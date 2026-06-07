<?php

return [
    'inventory_gl_enabled' => env('ACCOUNTING_INVENTORY_GL', true),

    // invoice = Anglo-Saxon (COGS when invoiced), delivery = when stock ships
    'cogs_recognition' => env('ACCOUNTING_COGS_RECOGNITION', 'invoice'),

    'accounts' => [
        'raw_materials_inventory' => 'Raw Materials Inventory',
        'finished_goods_inventory' => 'Finished Goods Inventory',
        'work_in_progress' => 'Work in Progress',
        'grni_accrual' => 'GRNI Accrual',
        'cogs' => 'Cost of Goods Sold',
        'inventory_write_off' => 'Inventory Write-Off Expense',
        'purchases' => 'Purchases',
    ],
];
