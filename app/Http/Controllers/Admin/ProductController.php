<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentPriceList;
use App\Models\InvoiceItem;
use App\Models\OrderItem;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['packagingType', 'taxClass'])->latest()->paginate(12);
        $packagingCount = PackagingType::count();
        $taxClassCount = TaxClass::count();

        return view('admin.products.index', compact('products', 'packagingCount', 'taxClassCount'));
    }

    public function create()
    {
        $packagingTypes = PackagingType::orderBy('name')->get();
        $taxClasses = TaxClass::orderBy('name')->get();

        return view('admin.products.create', compact('packagingTypes', 'taxClasses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'size' => 'nullable|string',
            'volume_ml' => 'nullable|numeric|min:0',
            'sku_code' => 'nullable|string',
            'packaging_type_id' => 'nullable|exists:packaging_types,id',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'mineral_source' => 'nullable|string',
            'ph' => 'nullable|numeric|min:0|max:14',
            'tds' => 'nullable|integer|min:0',
            'certifications' => 'nullable|string',
            'barcode' => 'nullable|string',
            'qr_code' => 'nullable|string',
            'image_path' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::create($data);

        if ($request->hasFile('product_image')) {
            $product->update(['image_path' => $request->file('product_image')->store('products', 'public')]);
        }

        if ($request->hasFile('qr_code_file')) {
            $product->update(['qr_code' => $request->file('qr_code_file')->store('qrcodes', 'public')]);
        }

        return redirect()->route('admin.products.show', $product)->with('status', 'Product added to catalog.');
    }

    public function edit(Product $product)
    {
        $packagingTypes = PackagingType::orderBy('name')->get();
        $taxClasses = TaxClass::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'packagingTypes', 'taxClasses'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'name' => 'required|string',
            'description' => 'nullable|string',
            'size' => 'nullable|string',
            'volume_ml' => 'nullable|numeric|min:0',
            'sku_code' => 'nullable|string',
            'packaging_type_id' => 'nullable|exists:packaging_types,id',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'mineral_source' => 'nullable|string',
            'ph' => 'nullable|numeric|min:0|max:14',
            'tds' => 'nullable|integer|min:0',
            'certifications' => 'nullable|string',
            'barcode' => 'nullable|string',
            'qr_code' => 'nullable|string',
            'image_path' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        $product->update($data);

        if ($request->hasFile('product_image')) {
            $product->update(['image_path' => $request->file('product_image')->store('products', 'public')]);
        }

        if ($request->hasFile('qr_code_file')) {
            $product->update(['qr_code' => $request->file('qr_code_file')->store('qrcodes', 'public')]);
        }

        return redirect()->route('admin.products.show', $product)->with('status', 'Product updated.');
    }

    public function show(Product $product)
    {
        $product->load(['packagingType', 'taxClass', 'batches']);
        return view('admin.products.show', compact('product'));
    }

    public function export()
    {
        $products = Product::with(['packagingType', 'taxClass'])->orderBy('sku')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_catalog.csv"',
        ];

        $callback = static function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'SKU',
                'Name',
                'Size',
                'Volume (ml)',
                'Packaging',
                'Tax class',
                'Tax rate',
                'Base price',
                'Barcode',
                'QR path',
            ]);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->size,
                    $product->volume_ml,
                    optional($product->packagingType)->name,
                    optional($product->taxClass)->name,
                    optional($product->taxClass)->rate,
                    $product->base_price,
                    $product->barcode,
                    $product->qr_code,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Product $product)
    {
        if (
            OrderItem::where('product_id', $product->id)->exists()
            || InvoiceItem::where('product_id', $product->id)->exists()
            || AgentPriceList::where('product_id', $product->id)->exists()
        ) {
            return redirect()->route('admin.products.index')
                ->with('status', 'Product is used in orders, invoices, or price lists and cannot be deleted.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        if ($product->qr_code) {
            Storage::disk('public')->delete($product->qr_code);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }
}
