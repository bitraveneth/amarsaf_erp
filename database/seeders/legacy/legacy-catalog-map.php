<?php

/**
 * Legacy catalog SQL import mapping.
 *
 * Place your dump at: storage/app/imports/legacy-catalog.sql
 * Then run: php artisan migrate:fresh --seed
 * Or analyse first: php artisan saf:import-legacy-catalog --analyze
 *
 * Supports v2-compatible dumps (same table/column names) and mapped legacy schemas.
 */
return [
    'connection' => 'legacy_import',
    'import_database' => env('LEGACY_IMPORT_DATABASE', 'saferp_legacy_import'),

    // v2_direct: tables match current ERP schema. mapped: use legacy_* keys below.
    'mode' => env('LEGACY_CATALOG_MODE', 'v2_direct'),

    'categories_table' => 'material_categories',
    'categories_columns' => [
        'code' => 'code',
        'name' => 'name',
        'group' => 'group',
        'description' => 'description',
        'sort_order' => 'sort_order',
        'is_system' => 'is_system',
    ],

    'products_table' => 'products',
    'products_columns' => [
        'sku' => 'sku',
        'name' => 'name',
        'product_type' => 'product_type',
        'material_category_id' => 'material_category_id',
        'description' => 'description',
        'size' => 'size',
        'uom' => 'uom',
        'base_price' => 'base_price',
        'mrp' => 'mrp',
        'standard_cost' => 'standard_cost',
        'supplier_name' => 'supplier_name',
        'chemical_name' => 'chemical_name',
        'sourcing' => 'sourcing',
        'brand' => 'brand',
        'is_active' => 'is_active',
    ],

    // Optional legacy schema overrides (mode = mapped)
    'legacy_categories_table' => 'material_subgroups',
    'legacy_categories_columns' => [
        'code' => 'subgroup_code',
        'name' => 'subgroup_name',
        'group' => 'group_name',
        'description' => 'notes',
        'sort_order' => 'sort_order',
        'is_system' => 'is_system',
    ],

    'legacy_products_table' => 'catalog_products',
    'legacy_products_columns' => [
        'sku' => 'item_code',
        'name' => 'item_name',
        'product_type' => 'item_type',
        'material_category_id' => 'subgroup_id',
        'description' => 'description',
        'size' => 'size_label',
        'uom' => 'unit',
        'base_price' => 'sale_price',
        'mrp' => 'mrp',
        'standard_cost' => 'cost_price',
        'supplier_name' => 'supplier',
        'chemical_name' => 'chemical_name',
        'sourcing' => 'sourcing',
        'brand' => 'brand',
        'is_active' => 'active',
    ],

    'legacy_category_lookup' => [
        'table' => 'material_subgroups',
        'id' => 'id',
        'code' => 'subgroup_code',
    ],

    'product_type_map' => [
        'raw' => 'raw',
        'material' => 'raw',
        'rm' => 'raw',
        'finished' => 'finished',
        'fg' => 'finished',
        'product' => 'finished',
    ],
];
