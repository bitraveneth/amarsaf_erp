<?php

namespace Tests\Feature;

use App\Support\Documents\DocumentDataFactory;
use App\Support\Documents\DocumentNumbers;
use App\Support\PdfDocumentBuilder;
use Carbon\Carbon;
use Tests\TestCase;

class DocumentPrintDesignTest extends TestCase
{
    public function test_delivery_challan_prints_customer_office_and_transport_copies(): void
    {
        $html = view('documents.layouts.print', [
            'type' => 'delivery-challan',
            'id' => 9,
            'payload' => $this->challanPayload(),
            'company' => $this->company(),
            'backUrl' => null,
            'autoPrint' => false,
        ])->render();

        $this->assertSame(3, substr_count($html, 'class="doc-copy-pill"'));
        $this->assertStringContainsString('Customer copy', $html);
        $this->assertStringContainsString('Office copy', $html);
        $this->assertStringContainsString('Transport copy', $html);
        $this->assertStringContainsString('Copy 1 of 3', $html);
        $this->assertStringContainsString('Copy 3 of 3', $html);
        $this->assertStringContainsString('Qty sent', $html);
        $this->assertStringContainsString('Qty received', $html);
        $this->assertStringContainsString('Driver and transport', $html);
        $this->assertStringContainsString('________', $html);
        $this->assertStringContainsString('doc-copy-next', $html);
    }

    public function test_invoice_purchase_order_and_report_use_the_same_sheet(): void
    {
        $invoice = view('documents.layouts.pdf', [
            'payload' => [
                'title' => 'Tax invoice',
                'subtitle' => 'Customer copy',
                'number' => 'INV-000001',
                'status' => 'Issued',
                'generated_at' => now(),
                'parties' => [[
                    'label' => 'Bill to',
                    'name' => 'Meghna',
                    'lines' => ['+8801700000000'],
                ]],
                'columns' => ['#', 'Particulars', 'Qty', 'Rate', 'Amount'],
                'aligns' => ['center', 'left', 'right', 'right', 'right'],
                'rows' => [['1', 'Drinking water', '2', '540.00', '1,080.00']],
                'totals' => [['Outstanding', '1,080.00 BDT']],
                'blocks' => [[
                    'label' => 'Payment details',
                    'lines' => ['Cash'],
                ]],
                'footer' => 'This is a computer-generated tax invoice.',
            ],
            'company' => $this->company(),
        ])->render();

        $purchaseOrder = view('documents.layouts.pdf', [
            'payload' => [
                'title' => 'Purchase order',
                'number' => 'PO-000001',
                'status' => 'Draft',
                'generated_at' => now(),
                'parties' => [[
                    'label' => 'Supplier',
                    'name' => 'North Vendor',
                    'lines' => ['Dhaka'],
                ]],
                'columns' => ['#', 'Particulars', 'Amount'],
                'rows' => [['1', 'Caps', '100.00']],
                'totals' => [['PO total', '100.00 BDT']],
                'footer' => 'Please quote this PO number on all correspondence and invoices.',
            ],
            'company' => $this->company(),
        ])->render();

        $report = view('exports.table-pdf', [
            'title' => 'Balance sheet',
            'columns' => ['Account', 'Amount'],
            'rows' => [['Cash', '1,200.00']],
            'generatedAt' => now(),
            'truncated' => false,
            'totalRows' => 1,
            'company' => $this->company(),
        ])->render();

        foreach ([$invoice, $purchaseOrder, $report] as $html) {
            $this->assertStringContainsString('doc-banner', $html);
            $this->assertStringContainsString('doc-lines', $html);
            $this->assertStringContainsString('Sohag Food', $html);
        }

        $this->assertStringContainsString('INV-000001', $invoice);
        $this->assertStringContainsString('Payment details', $invoice);
        $this->assertStringContainsString('PO-000001', $purchaseOrder);
        $this->assertStringContainsString('Balance sheet', $report);
        $this->assertStringContainsString('Statement', $report);
        $this->assertStringContainsString('1,200.00', $report);
    }

    public function test_customer_invoice_uses_the_designed_sheet(): void
    {
        $payload = [
            'title' => 'Tax invoice',
            'number' => 'INV-000001',
            'status' => 'Issued',
            'generated_at' => now(),
            'sheet' => 'invoice',
            'invoice' => [
                'number' => 'INV-000001',
                'issued' => '29 October, 2020',
                'due' => '5 January, 2020',
                'bill_to' => [
                    'name' => 'Megumin Ryu',
                    'phone' => '01712345678',
                    'email' => 'megumin_ryu@mail.com',
                    'address' => 'Main street',
                ],
                'contact' => ['+8801750720000', 'contact@amarsaf.com', 'Gokulkhali'],
                'lines' => [[
                    'sl' => '1',
                    'description' => 'Drinking water',
                    'qty' => 'x 2',
                    'rate' => '540',
                    'amount' => '1,080',
                ]],
                'totals' => [
                    ['label' => 'Sub Total', 'amount' => '1,080'],
                    ['label' => 'Discount (-)', 'amount' => '0'],
                    ['label' => 'VAT (+)', 'amount' => '0'],
                    ['label' => 'Invoice Total', 'amount' => '1,080'],
                    ['label' => 'Paid Advance (-)', 'amount' => '0'],
                ],
                'grand' => ['label' => 'Total', 'amount' => 'BDT 1,080'],
                'payments' => [
                    ['label' => 'Account Name', 'value' => 'Sohag Food'],
                    ['label' => 'Bank Name', 'value' => 'Eastern Bank PLC'],
                ],
                'notes' => ['Payment is due by the due date shown on this invoice.'],
                'signatory' => [
                    'name' => 'Authorized signatory',
                    'title' => 'Managing Director',
                    'company' => 'Sohag Food & Beverage Ltd.',
                ],
                'footer' => 'If you have any inquiries regarding this invoice, please call us at +8801750720000',
            ],
        ];

        $html = view('documents.layouts.pdf', [
            'payload' => $payload,
            'company' => $this->company(),
        ])->render();

        $this->assertStringContainsString('class="inv inv-for-pdf"', $html);
        $this->assertStringContainsString('INVOICE', $html);
        $this->assertStringContainsString('Billed to', $html);
        $this->assertStringContainsString('Megumin Ryu', $html);
        $this->assertStringContainsString('Sub Total', $html);
        $this->assertStringContainsString('inv-grand', $html);
        $this->assertStringContainsString('Payment details', $html);
        $this->assertStringContainsString('BDT 1,080', $html);
        $this->assertStringNotContainsString('class="doc-banner"', $html);
        $this->assertStringNotContainsString('class="doc-lines"', $html);

        $pdf = PdfDocumentBuilder::loadView('documents.layouts.pdf', [
            'payload' => $payload,
            'company' => $this->company(),
        ])->setPaper('a4');
        $pdf->setOption('isFontSubsettingEnabled', false);

        $text = $this->pdfText($pdf->output());
        $this->assertStringContainsString('INVOICE', $text);
        $this->assertStringContainsString('BILLED TO', $text);
        $this->assertStringContainsString('Drinking water', $text);
        $this->assertStringContainsString('Sub Total', $text);
        $this->assertStringContainsString('PAYMENT DETAILS', $text);
    }

    public function test_invoice_sheet_hides_the_app_name_when_no_company_is_saved(): void
    {
        $payload = [
            'title' => 'Tax invoice',
            'number' => 'SFIN2026101801',
            'sheet' => 'invoice',
            'invoice' => [
                'number' => 'SFIN2026101801',
                'issued' => '18 October, 2026',
                'due' => '25 October, 2026',
                'bill_to' => ['name' => 'Gulshan Trading'],
                'lines' => [[
                    'sl' => '1',
                    'description' => 'Drinking water',
                    'qty' => 'x 2',
                    'rate' => '540',
                    'amount' => '1,080',
                ]],
                'totals' => [
                    ['label' => 'Sub Total', 'amount' => '1,080'],
                ],
                'grand' => ['label' => 'Total', 'amount' => 'BDT 1,080'],
                'payments' => [],
                'notes' => [],
                'signatory' => ['name' => 'Authorized signatory'],
                'footer' => 'If you have any inquiries regarding this invoice, please contact us.',
            ],
        ];
        $company = $this->company();
        unset($company['legal_name']);

        $html = view('documents.layouts.pdf', [
            'payload' => $payload,
            'company' => $company,
        ])->render();

        $this->assertStringNotContainsString('Saf ERP', $html);
        $this->assertStringNotContainsString('class="inv-company"', $html);
        $this->assertStringContainsString('SFIN2026101801', $html);
    }

    public function test_sales_order_and_challan_use_the_purple_sheet(): void
    {
        $salesOrder = DocumentDataFactory::designedSheet([
            'title' => 'Sales order',
            'number' => '#6',
            'generated_at' => Carbon::parse('2026-10-29 17:44:00'),
            'parties' => [
                [
                    'label' => 'Customer',
                    'name' => 'Gulshan Trading',
                    'lines' => ['01700000000'],
                ],
                [
                    'label' => 'Ship to',
                    'name' => 'Gulshan Trading',
                    'lines' => ['Gokulkhali'],
                ],
            ],
            'meta' => [['Order no', '#6']],
            'columns' => ['#', 'Product', 'Amount'],
            'aligns' => ['center', 'left', 'right'],
            'rows' => [['1', 'Drinking water', '1,080.00']],
            'totals' => [
                ['Subtotal', '1,080.00 BDT'],
                ['Order total', '1,080.00 BDT'],
                ['Commission', '50.00 BDT'],
            ],
            'signatures' => [['Prepared by', ''], ['Customer signature', '']],
            'footer' => 'Sales order footer',
        ]);

        $this->assertSame('invoice', $salesOrder['sheet']);
        $this->assertSame('document', $salesOrder['invoice']['layout']);
        $this->assertSame('Order total', $salesOrder['invoice']['grand']['label']);
        $this->assertSame('Order details', $salesOrder['invoice']['details_label']);
        $this->assertSame('Commission', $salesOrder['invoice']['totals'][1]['label']);

        $html = view('documents.layouts.pdf', [
            'payload' => $salesOrder,
            'company' => $this->company(),
        ])->render();

        $this->assertStringContainsString('inv-title-stack', $html);
        $this->assertStringContainsString('inv-title-line', $html);
        $this->assertStringContainsString('Sales order', $html);
        $this->assertStringContainsString('Gulshan Trading', $html);
        $this->assertStringContainsString('Prepared by', $html);
        $this->assertStringContainsString('>Total<', $html);
        $this->assertStringContainsString('SL', $html);
        $this->assertStringContainsString('Product description', $html);
        $this->assertStringContainsString('Generated:', $html);
        $this->assertStringContainsString('Payment is due within 7 days of invoice date.', $html);
        $this->assertStringContainsString('If you have any inquiries regarding this sales order', $html);
        $this->assertStringNotContainsString('class="doc-banner"', $html);
        $this->assertStringNotContainsString('class="doc-lines"', $html);

        $challan = DocumentDataFactory::designedSheet($this->challanPayload());
        $challanHtml = view('documents.layouts.print', [
            'type' => 'delivery-challan',
            'id' => 9,
            'payload' => $challan,
            'company' => $this->company(),
            'backUrl' => null,
            'autoPrint' => false,
        ])->render();

        $this->assertSame(3, substr_count($challanHtml, 'class="inv-copy"'));
        $this->assertStringContainsString('Customer copy', $challanHtml);
        $this->assertStringContainsString('Transport copy', $challanHtml);
        $this->assertStringContainsString('Driver', $challanHtml);
        $this->assertStringNotContainsString('class="doc-banner"', $challanHtml);
        $this->assertNull($challan['invoice']['grand']);
    }

    public function test_document_numbers_follow_the_date_standard(): void
    {
        $day = Carbon::parse('2026-10-18');

        $this->assertSame('SFIN2026101801', DocumentNumbers::invoiceCode($day, 1));
        $this->assertSame('SFIN2026101802', DocumentNumbers::invoiceCode($day, 2));
        $this->assertSame('SGC20261018', DocumentNumbers::challanCode($day, 1, 1));
        $this->assertSame('SGC20261018', DocumentNumbers::challanCode($day, 1, 3));
        $this->assertSame('SGC2026101802', DocumentNumbers::challanCode($day, 2, 3));
    }

    public function test_challan_pdf_contains_each_copy_label(): void
    {
        $pdf = PdfDocumentBuilder::loadView('documents.layouts.pdf', [
            'payload' => $this->challanPayload(),
            'company' => $this->company(),
        ])->setPaper('a4');
        $pdf->setOption('isFontSubsettingEnabled', false);

        $binary = $pdf->output();
        $this->assertStringStartsWith('%PDF', $binary);

        $text = $this->pdfText($binary);
        $this->assertStringContainsString('CUSTOMER COPY', $text);
        $this->assertStringContainsString('OFFICE COPY', $text);
        $this->assertStringContainsString('TRANSPORT COPY', $text);
        $this->assertStringContainsString('DC-00009', $text);
        $this->assertStringContainsString('Drinking water', $text);
    }

    protected function challanPayload(): array
    {
        return [
            'title' => 'Delivery challan',
            'number' => 'DC-00009',
            'status' => 'Scheduled',
            'generated_at' => now(),
            'copies' => ['Customer copy', 'Office copy', 'Transport copy'],
            'parties' => [[
                'label' => 'Deliver to',
                'name' => 'Meghna',
                'lines' => ['Gokulkhali'],
            ]],
            'meta' => [
                ['Challan no.', 'DC-00009'],
                ['Delivery date', '02 Nov 2026'],
            ],
            'intro' => [[
                'label' => 'Driver and transport',
                'lines' => ['Driver: Karim', 'Vehicle: DHAKA-11-1234'],
            ]],
            'columns' => ['#', 'Particulars', 'Size', 'Unit', 'Qty sent', 'Qty received', 'Remarks'],
            'aligns' => ['center', 'left', 'left', 'left', 'right', 'right', 'left'],
            'rows' => [['1', 'Drinking water', '500 ml', 'Bottle', '24', '________', '—']],
            'notes' => 'Received the goods above in good condition.',
            'signatures' => [
                ['Prepared by', ''],
                ['Authorized signature', ''],
                ['Received by', ''],
            ],
            'footer' => 'Office copy stays with accounts.',
        ];
    }

    protected function company(): array
    {
        return [
            'legal_name' => 'Sohag Food & Beverage Ltd.',
            'initials' => 'SF',
            'phone' => '+8801750720000',
            'email' => 'contact@amarsaf.com',
            'address' => 'Gokulkhali',
            'primary' => '#5F4BFF',
        ];
    }

    protected function pdfText(string $binary): string
    {
        $text = str_replace("\x00", '', $binary);
        $offset = 0;

        while (($start = strpos($binary, 'stream', $offset)) !== false) {
            $start += strlen('stream');
            if (isset($binary[$start]) && $binary[$start] === "\r") {
                $start++;
            }
            if (isset($binary[$start]) && $binary[$start] === "\n") {
                $start++;
            }
            $end = strpos($binary, 'endstream', $start);
            if ($end === false) {
                break;
            }

            $stream = rtrim(substr($binary, $start, $end - $start), "\r\n");
            $decoded = @gzuncompress($stream);
            if ($decoded === false) {
                $decoded = @gzinflate($stream);
            }
            if (is_string($decoded)) {
                $text .= str_replace("\x00", '', $decoded);
            }

            $offset = $end + strlen('endstream');
        }

        return $text;
    }
}
