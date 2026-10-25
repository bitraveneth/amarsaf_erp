<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\Documents\CompanyDocumentContext;
use App\Support\Documents\DocumentDataFactory;
use App\Support\Documents\DocumentRegistry;
use App\Support\PdfDocumentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class DocumentController extends Controller
{
    public function preview(Request $request, string $type, int $id)
    {
        if ($type === 'invoice') {
            return view('admin.finance.invoice_print', [
                'invoice' => $this->invoiceForDocument($id),
            ]);
        }

        [$definition, $model, $payload, $company] = $this->resolve($type, $id);

        return view('documents.layouts.print', [
            'type' => $type,
            'id' => $id,
            'definition' => $definition,
            'payload' => $payload,
            'company' => $company,
            'backUrl' => DocumentRegistry::backUrl($type, $model),
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function pdf(string $type, int $id)
    {
        if ($type === 'invoice') {
            $invoice = $this->invoiceForDocument($id);

            $pdf = PdfDocumentBuilder::loadView('admin.finance.invoice_pdf', [
                'invoice' => $invoice,
            ])->setPaper('a4');

            return $pdf->download('invoice-'.$invoice->number.'.pdf');
        }

        [$definition, $model, $payload, $company] = $this->resolve($type, $id);

        $pdf = PdfDocumentBuilder::loadView('documents.layouts.pdf', [
            'type' => $type,
            'definition' => $definition,
            'payload' => $payload,
            'company' => $company,
        ])->setPaper('a4');

        return $pdf->download(DocumentRegistry::filename($type, $model) . '.pdf');
    }

    protected function invoiceForDocument(int $id): Invoice
    {
        $definition = DocumentRegistry::get('invoice');
        $invoice = DocumentRegistry::resolveModel('invoice', $id);

        if (! $invoice instanceof Invoice) {
            abort(404);
        }

        if (! Permission::can(request()->user(), $definition['permission'])) {
            abort(403, 'You do not have permission to access this document.');
        }

        $relations = ['order.agent', 'items.product', 'receipts', 'creditNotes'];

        if (Schema::hasTable('agent_advance_applications')) {
            $relations[] = 'advanceApplications';
        }

        $invoice->load($relations);

        return $invoice;
    }

    protected function resolve(string $type, int $id): array
    {
        try {
            $definition = DocumentRegistry::get($type);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        $model = DocumentRegistry::resolveModel($type, $id);

        if (! $model) {
            abort(404);
        }

        if (! Permission::can(request()->user(), $definition['permission'])) {
            abort(403, 'You do not have permission to access this document.');
        }

        $company = CompanyDocumentContext::make();
        $payload = DocumentDataFactory::build($type, $model);

        return [$definition, $model, $payload, $company];
    }
}
