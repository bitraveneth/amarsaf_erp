<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission;
use App\Http\Controllers\Controller;
use App\Support\Documents\CompanyDocumentContext;
use App\Support\Documents\DocumentDataFactory;
use App\Support\Documents\DocumentRegistry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DocumentController extends Controller
{
    public function preview(Request $request, string $type, int $id)
    {
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
        [$definition, $model, $payload, $company] = $this->resolve($type, $id);

        $pdf = Pdf::loadView('documents.layouts.pdf', [
            'type' => $type,
            'definition' => $definition,
            'payload' => $payload,
            'company' => $company,
        ])->setPaper('a4');

        return $pdf->download(DocumentRegistry::filename($type, $model) . '.pdf');
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
