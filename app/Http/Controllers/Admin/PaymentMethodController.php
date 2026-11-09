<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\PaymentInstrument;
use App\Models\PaymentMethodCategory;
use App\Services\Accounting\PaymentInstrumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PaymentMethodController extends Controller
{
    public function __construct(protected PaymentInstrumentService $instruments)
    {
    }

    public function index(Request $request)
    {
        $categories = PaymentMethodCategory::with(['instruments.account', 'parentAccount'])
            ->ordered()
            ->get();

        $assetGroups = Account::query()
            ->groups()
            ->where('type', 'asset')
            ->orderBy('code')
            ->get();

        $activeCode = $request->query('category');
        if (! $categories->contains(fn ($c) => $c->code === $activeCode)) {
            $activeCode = $categories->first()?->code;
        }

        $stats = [
            'categories' => $categories->count(),
            'accounts' => $categories->sum(fn ($c) => $c->instruments->count()),
            'active' => $categories->sum(fn ($c) => $c->instruments->where('is_active', true)->count()),
            'linked' => $categories->sum(fn ($c) => $c->instruments->whereNotNull('account_id')->count()),
        ];

        return view('admin.finance.payment_methods.index', compact(
            'categories',
            'assetGroups',
            'activeCode',
            'stats'
        ));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:40|alpha_dash|unique:payment_method_categories,code',
            'kind' => 'required|in:bank,mobile,cash',
            'parent_account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('is_group', true)->where('type', 'asset')),
            ],
        ]);

        $category = PaymentMethodCategory::create([
            ...$data,
            'sort_order' => (int) (PaymentMethodCategory::max('sort_order') ?? 0) + 10,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.payment-methods.index', ['category' => $category->code])
            ->with('status', 'Payment category added.');
    }

    public function updateCategory(Request $request, PaymentMethodCategory $paymentMethodCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'parent_account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('is_group', true)->where('type', 'asset')),
            ],
            'is_active' => 'sometimes|boolean',
        ]);

        $paymentMethodCategory->update([
            'name' => $data['name'],
            'parent_account_id' => $data['parent_account_id'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.payment-methods.index', ['category' => $paymentMethodCategory->code])
            ->with('status', 'Payment category updated.');
    }

    public function storeInstrument(Request $request, PaymentMethodCategory $paymentMethodCategory)
    {
        $data = $this->validatedInstrument($request);

        try {
            $this->instruments->create($paymentMethodCategory, [
                ...$data,
                'is_default' => $request->boolean('is_default'),
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['name' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.payment-methods.index', ['category' => $paymentMethodCategory->code])
            ->with('status', 'Account added and ledger created in the chart of accounts.');
    }

    public function updateInstrument(Request $request, PaymentInstrument $paymentInstrument)
    {
        $data = $this->validatedInstrument($request);

        $this->instruments->update($paymentInstrument, [
            ...$data,
            'is_default' => $request->boolean('is_default'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $paymentInstrument->loadMissing('category');

        return redirect()
            ->route('admin.payment-methods.index', ['category' => $paymentInstrument->category?->code])
            ->with('status', 'Payment account updated.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedInstrument(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'account_holder_name' => 'nullable|string|max:150',
            'bank_name' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:80',
            'branch' => 'nullable|string|max:120',
            'routing' => 'nullable|string|max:80',
            'swift_code' => 'nullable|string|max:40',
            'iban' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:40',
            'details' => 'nullable|string|max:255',
            'is_default' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
    }
}
