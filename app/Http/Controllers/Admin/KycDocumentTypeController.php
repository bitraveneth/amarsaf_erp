<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocumentType;
use Illuminate\Http\Request;

class KycDocumentTypeController extends Controller
{
    public function index()
    {
        $types = KycDocumentType::ordered()->paginate(20);

        return view('admin.kyc-document-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:kyc_document_types,name',
        ]);

        KycDocumentType::create([
            'name' => trim($data['name']),
            'sort_order' => (int) (KycDocumentType::max('sort_order') + 10),
        ]);

        return back()->with('status', 'KYC document type added.');
    }

    public function destroy(KycDocumentType $kycDocumentType)
    {
        $kycDocumentType->delete();

        return back()->with('status', 'KYC document type deleted.');
    }
}
