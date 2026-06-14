<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AccountingPeriodClosedException;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class JournalEntryController extends Controller
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $entries = JournalEntry::with(['lines.account', 'postedByUser'])
            ->whereBetween('entry_date', [$from->toDateString(), $to->toDateString()])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('type'), fn ($q, $type) => $q->where('journal_type', $type))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.finance.journals.index', compact('entries', 'from', 'to'));
    }

    public function create()
    {
        $accounts = Account::postable()->orderBy('code')->get();
        $journal = new JournalEntry([
            'entry_date' => Carbon::today(),
            'status' => 'draft',
        ]);

        return view('admin.finance.journals.form', compact('journal', 'accounts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $lines = $this->normalizedLines($data['lines']);

        try {
            $journal = $this->accounting->createDraft(
                Carbon::parse($data['entry_date']),
                $lines,
                ['description' => $data['description'] ?? null]
            );

            if ($request->has('post_now')) {
                $journal = $this->accounting->postDraft($journal);
            }
        } catch (AccountingPeriodClosedException|InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['journal' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.journals.show', $journal)
            ->with('status', $journal->status === 'posted' ? 'Journal posted.' : 'Draft journal saved.');
    }

    public function show(JournalEntry $journal)
    {
        $journal->load(['lines.account', 'postedByUser', 'accountingPeriod', 'reversalOf', 'reversedBy']);

        return view('admin.finance.journals.show', compact('journal'));
    }

    public function edit(JournalEntry $journal)
    {
        if (! $journal->isEditable()) {
            return redirect()
                ->route('admin.journals.show', $journal)
                ->withErrors(['journal' => 'Posted journals cannot be edited. Reverse and create a new entry instead.']);
        }

        $journal->load('lines.account');
        $accounts = Account::postable()->orderBy('code')->get();

        return view('admin.finance.journals.form', compact('journal', 'accounts'));
    }

    public function update(Request $request, JournalEntry $journal)
    {
        $data = $this->validated($request);
        $lines = $this->normalizedLines($data['lines']);

        try {
            $journal = $this->accounting->saveDraft(
                $journal,
                Carbon::parse($data['entry_date']),
                $lines,
                ['description' => $data['description'] ?? null]
            );

            if ($request->has('post_now')) {
                $journal = $this->accounting->postDraft($journal);
            }
        } catch (AccountingPeriodClosedException|InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['journal' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.journals.show', $journal)
            ->with('status', $journal->status === 'posted' ? 'Journal posted.' : 'Draft journal updated.');
    }

    public function post(JournalEntry $journal)
    {
        try {
            $this->accounting->postDraft($journal);
        } catch (AccountingPeriodClosedException|InvalidArgumentException $e) {
            return back()->withErrors(['journal' => $e->getMessage()]);
        }

        return redirect()->route('admin.journals.show', $journal)->with('status', 'Journal posted.');
    }

    public function reverse(JournalEntry $journal)
    {
        try {
            $reversal = $this->accounting->reverse($journal);
        } catch (AccountingPeriodClosedException|InvalidArgumentException $e) {
            return back()->withErrors(['journal' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.journals.show', $reversal)
            ->with('status', 'Reversal journal created.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'entry_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'post_now' => 'sometimes|boolean',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);
    }

    protected function normalizedLines(array $lines): array
    {
        return collect($lines)->map(function (array $line) {
            return [
                'account_id' => (int) $line['account_id'],
                'debit' => (float) ($line['debit'] ?? 0),
                'credit' => (float) ($line['credit'] ?? 0),
                'description' => $line['description'] ?? null,
            ];
        })->all();
    }
}
