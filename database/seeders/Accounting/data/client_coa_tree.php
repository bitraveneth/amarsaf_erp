<?php

/**
 * Client hierarchical chart of accounts (Tally-style groups + ledgers).
 * Groups: is_group true. Ledgers: postable leaf nodes with unique slugs where referenced by ERP automation.
 */
return [
    [
        'code' => '1000',
        'name' => 'ASSETS',
        'slug' => 'assets_root',
        'type' => 'asset',
        'is_group' => true,
        'report_root' => 'assets',
        'children' => [
            [
                'code' => '1100',
                'name' => 'Current Assets',
                'type' => 'asset',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '1110',
                        'name' => 'Cash & Bank',
                        'type' => 'asset',
                        'is_group' => true,
                        'children' => [
                            [
                                'code' => '1111',
                                'name' => 'Cash in Hand',
                                'type' => 'asset',
                                'is_group' => true,
                                'children' => [
                                    ['code' => '11111', 'name' => 'Cash in Hand', 'slug' => 'cash_in_hand', 'type' => 'asset'],
                                    ['code' => '11112', 'name' => 'Petty Cash', 'slug' => 'petty_cash', 'type' => 'asset'],
                                ],
                            ],
                            [
                                'code' => '1112',
                                'name' => 'Cash at Bank',
                                'type' => 'asset',
                                'is_group' => true,
                                'children' => [
                                    ['code' => '11121', 'name' => 'Bank Account – BRAC Bank', 'slug' => 'bank_brac', 'type' => 'asset'],
                                    ['code' => '11122', 'name' => 'Bank Account – Standard Chartered Bank', 'slug' => 'bank_scb', 'type' => 'asset'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => '1120',
                        'name' => 'Accounts Receivable',
                        'type' => 'asset',
                        'is_group' => true,
                        'children' => [
                            ['code' => '11201', 'name' => 'Trade Debtors', 'slug' => 'trade_debtors', 'type' => 'asset'],
                            ['code' => '11202', 'name' => 'Employee Receivable', 'slug' => 'employee_receivable', 'type' => 'asset'],
                            ['code' => '11203', 'name' => 'Director Receivable', 'slug' => 'director_receivable', 'type' => 'asset'],
                            ['code' => '11204', 'name' => 'Agent Advances', 'slug' => 'agent_advances', 'type' => 'asset'],
                        ],
                    ],
                    [
                        'code' => '1130',
                        'name' => 'Advances & Deposits',
                        'type' => 'asset',
                        'is_group' => true,
                        'children' => [
                            ['code' => '11301', 'name' => 'Advance to Suppliers', 'slug' => 'advance_suppliers', 'type' => 'asset'],
                            ['code' => '11302', 'name' => 'Security Deposit', 'slug' => 'security_deposit', 'type' => 'asset'],
                            ['code' => '11303', 'name' => 'Advance Rent', 'slug' => 'advance_rent', 'type' => 'asset'],
                            ['code' => '11304', 'name' => 'Utility Deposit', 'slug' => 'utility_deposit', 'type' => 'asset'],
                            ['code' => '11305', 'name' => 'Input VAT', 'slug' => 'input_vat', 'type' => 'asset'],
                            ['code' => '11306', 'name' => 'Withholding Tax Receivable', 'slug' => 'wht_receivable', 'type' => 'asset'],
                        ],
                    ],
                    [
                        'code' => '1140',
                        'name' => 'Inventory',
                        'type' => 'asset',
                        'is_group' => true,
                        'children' => [
                            ['code' => '11401', 'name' => 'Raw Materials', 'slug' => 'raw_materials', 'type' => 'asset'],
                            ['code' => '11402', 'name' => 'Packing Materials', 'slug' => 'packing_materials', 'type' => 'asset'],
                            ['code' => '11403', 'name' => 'Work-in-Progress (WIP)', 'slug' => 'wip', 'type' => 'asset'],
                            ['code' => '11404', 'name' => 'Finished Goods', 'slug' => 'finished_goods', 'type' => 'asset'],
                            ['code' => '11405', 'name' => 'Trading Stock', 'slug' => 'trading_stock', 'type' => 'asset'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '1200',
                'name' => 'Non-Current Assets',
                'type' => 'asset',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '1210',
                        'name' => 'Property, Plant & Equipment',
                        'type' => 'asset',
                        'is_group' => true,
                        'children' => [
                            ['code' => '12101', 'name' => 'Land', 'slug' => 'ppe_land', 'type' => 'asset'],
                            ['code' => '12102', 'name' => 'Building', 'slug' => 'ppe_building', 'type' => 'asset'],
                            ['code' => '12103', 'name' => 'Factory Building', 'slug' => 'ppe_factory_building', 'type' => 'asset'],
                            ['code' => '12104', 'name' => 'Factory Machinery', 'slug' => 'ppe_factory_machinery', 'type' => 'asset'],
                            ['code' => '12105', 'name' => 'Office Equipment', 'slug' => 'ppe_office_equipment', 'type' => 'asset'],
                            ['code' => '12106', 'name' => 'Computer & Laptop', 'slug' => 'ppe_computer', 'type' => 'asset'],
                            ['code' => '12107', 'name' => 'Furniture & Fixtures', 'slug' => 'ppe_furniture', 'type' => 'asset'],
                            ['code' => '12108', 'name' => 'Motor Vehicles', 'slug' => 'ppe_motor_vehicles', 'type' => 'asset'],
                        ],
                    ],
                    [
                        'code' => '1220',
                        'name' => 'Intangible Assets',
                        'type' => 'asset',
                        'is_group' => true,
                        'children' => [
                            ['code' => '12201', 'name' => 'Software', 'slug' => 'intangible_software', 'type' => 'asset'],
                            ['code' => '12202', 'name' => 'Trademark', 'slug' => 'intangible_trademark', 'type' => 'asset'],
                            ['code' => '12203', 'name' => 'Patent', 'slug' => 'intangible_patent', 'type' => 'asset'],
                            ['code' => '12204', 'name' => 'Goodwill', 'slug' => 'intangible_goodwill', 'type' => 'asset'],
                        ],
                    ],
                    [
                        'code' => '1230',
                        'name' => 'Investments',
                        'type' => 'asset',
                        'is_group' => true,
                        'children' => [
                            ['code' => '12301', 'name' => 'Fixed Deposit (FDR)', 'slug' => 'investment_fdr', 'type' => 'asset'],
                            ['code' => '12302', 'name' => 'Investment in Subsidiary', 'slug' => 'investment_subsidiary', 'type' => 'asset'],
                            ['code' => '12303', 'name' => 'Long-Term Investment', 'slug' => 'investment_long_term', 'type' => 'asset'],
                        ],
                    ],
                ],
            ],
        ],
    ],
    [
        'code' => '2000',
        'name' => 'LIABILITIES',
        'slug' => 'liabilities_root',
        'type' => 'liability',
        'is_group' => true,
        'report_root' => 'liabilities',
        'children' => [
            [
                'code' => '2100',
                'name' => 'Current Liabilities',
                'type' => 'liability',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '2110',
                        'name' => 'Accounts Payable',
                        'type' => 'liability',
                        'is_group' => true,
                        'children' => [
                            ['code' => '21101', 'name' => 'Trade Creditors', 'slug' => 'trade_creditors', 'type' => 'liability'],
                            ['code' => '21102', 'name' => 'Supplier Payable', 'slug' => 'supplier_payable', 'type' => 'liability'],
                            ['code' => '21103', 'name' => 'Contractor Payable', 'slug' => 'contractor_payable', 'type' => 'liability'],
                        ],
                    ],
                    [
                        'code' => '2120',
                        'name' => 'Accrued Expenses',
                        'type' => 'liability',
                        'is_group' => true,
                        'children' => [
                            ['code' => '21201', 'name' => 'Salary Payable', 'slug' => 'salary_payable', 'type' => 'liability'],
                            ['code' => '21202', 'name' => 'Rent Payable', 'slug' => 'rent_payable', 'type' => 'liability'],
                            ['code' => '21203', 'name' => 'Utility Bill Payable', 'slug' => 'utility_payable', 'type' => 'liability'],
                            ['code' => '21204', 'name' => 'Audit Fee Payable', 'slug' => 'audit_fee_payable', 'type' => 'liability'],
                            ['code' => '21205', 'name' => 'GRNI Accrual', 'slug' => 'grni_accrual', 'type' => 'liability'],
                            ['code' => '21206', 'name' => 'Commission Payable', 'slug' => 'commission_payable', 'type' => 'liability'],
                        ],
                    ],
                    [
                        'code' => '2130',
                        'name' => 'Tax Liabilities',
                        'type' => 'liability',
                        'is_group' => true,
                        'children' => [
                            ['code' => '21301', 'name' => 'VAT Payable', 'slug' => 'vat_payable', 'type' => 'liability'],
                            ['code' => '21302', 'name' => 'Income Tax Payable', 'slug' => 'income_tax_payable', 'type' => 'liability'],
                            ['code' => '21303', 'name' => 'Tax Deducted at Source (TDS) Payable', 'slug' => 'tds_payable', 'type' => 'liability'],
                            ['code' => '21304', 'name' => 'Advance Tax Payable', 'slug' => 'advance_tax_payable', 'type' => 'liability'],
                        ],
                    ],
                    [
                        'code' => '2140',
                        'name' => 'Short-Term Borrowings',
                        'type' => 'liability',
                        'is_group' => true,
                        'children' => [
                            ['code' => '21401', 'name' => 'Bank Overdraft', 'slug' => 'bank_overdraft', 'type' => 'liability'],
                            ['code' => '21402', 'name' => 'Short-Term Loan', 'slug' => 'short_term_loan', 'type' => 'liability'],
                            ['code' => '21403', 'name' => 'Loan from Director', 'slug' => 'director_loan_st', 'type' => 'liability'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '2200',
                'name' => 'Non-Current Liabilities',
                'type' => 'liability',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '2210',
                        'name' => 'Long-Term Loans',
                        'type' => 'liability',
                        'is_group' => true,
                        'children' => [
                            ['code' => '22101', 'name' => 'Bank Term Loan', 'slug' => 'bank_term_loan', 'type' => 'liability'],
                            ['code' => '22102', 'name' => 'Lease Liability', 'slug' => 'lease_liability', 'type' => 'liability'],
                            ['code' => '22103', 'name' => "Director's Long-Term Loan", 'slug' => 'director_loan_lt', 'type' => 'liability'],
                        ],
                    ],
                ],
            ],
        ],
    ],
    [
        'code' => '3000',
        'name' => 'EQUITY',
        'slug' => 'equity_root',
        'type' => 'equity',
        'is_group' => true,
        'report_root' => 'equity',
        'children' => [
            [
                'code' => '3100',
                'name' => 'Share Capital',
                'type' => 'equity',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '3110',
                        'name' => 'Ordinary Share Capital',
                        'type' => 'equity',
                        'is_group' => true,
                        'children' => [
                            ['code' => '31101', 'name' => 'Paid-Up Share Capital', 'slug' => 'paid_up_share_capital', 'type' => 'equity'],
                        ],
                    ],
                    [
                        'code' => '3120',
                        'name' => 'Preference Share Capital',
                        'type' => 'equity',
                        'is_group' => true,
                        'children' => [
                            ['code' => '31201', 'name' => 'Preference Shares', 'slug' => 'preference_shares', 'type' => 'equity'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '3200',
                'name' => 'Reserves',
                'type' => 'equity',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '3210',
                        'name' => 'Statutory Reserve',
                        'type' => 'equity',
                        'is_group' => true,
                        'children' => [
                            ['code' => '32101', 'name' => 'Statutory Reserve Fund', 'slug' => 'statutory_reserve', 'type' => 'equity'],
                        ],
                    ],
                    [
                        'code' => '3220',
                        'name' => 'General Reserve',
                        'type' => 'equity',
                        'is_group' => true,
                        'children' => [
                            ['code' => '32201', 'name' => 'General Reserve Fund', 'slug' => 'general_reserve', 'type' => 'equity'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '3300',
                'name' => 'Retained Earnings',
                'type' => 'equity',
                'is_group' => true,
                'children' => [
                    ['code' => '33001', 'name' => 'Retained Earnings', 'slug' => 'retained_earnings', 'type' => 'equity'],
                    ['code' => '33002', 'name' => 'Current Year Profit/Loss', 'slug' => 'current_year_pl', 'type' => 'equity'],
                ],
            ],
        ],
    ],
    [
        'code' => '4000',
        'name' => 'REVENUE / INCORE',
        'slug' => 'revenue_root',
        'type' => 'income',
        'is_group' => true,
        'report_root' => 'revenue',
        'children' => [
            [
                'code' => '4100',
                'name' => 'Operating Revenue',
                'type' => 'income',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '4110',
                        'name' => 'Sales Revenue',
                        'type' => 'income',
                        'is_group' => true,
                        'children' => [
                            ['code' => '41101', 'name' => 'Product Sales', 'slug' => 'product_sales', 'type' => 'income'],
                            ['code' => '41102', 'name' => 'Service Revenue', 'slug' => 'service_revenue', 'type' => 'income'],
                            ['code' => '41103', 'name' => 'Export Sales', 'slug' => 'export_sales', 'type' => 'income'],
                            ['code' => '41104', 'name' => 'Sales Returns', 'slug' => 'sales_returns', 'type' => 'income'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '4200',
                'name' => 'Other Income',
                'type' => 'income',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '4210',
                        'name' => 'Financial Income',
                        'type' => 'income',
                        'is_group' => true,
                        'children' => [
                            ['code' => '42101', 'name' => 'Interest Income', 'slug' => 'interest_income', 'type' => 'income'],
                            ['code' => '42102', 'name' => 'FDR Interest Income', 'slug' => 'fdr_interest_income', 'type' => 'income'],
                        ],
                    ],
                    [
                        'code' => '4220',
                        'name' => 'Other Income',
                        'type' => 'income',
                        'is_group' => true,
                        'children' => [
                            ['code' => '42201', 'name' => 'Commission Income', 'slug' => 'commission_income', 'type' => 'income'],
                            ['code' => '42202', 'name' => 'Rental Income', 'slug' => 'rental_income', 'type' => 'income'],
                            ['code' => '42203', 'name' => 'Foreign Exchange Gain', 'slug' => 'fx_gain', 'type' => 'income'],
                            ['code' => '42204', 'name' => 'Other Income', 'slug' => 'other_income', 'type' => 'income'],
                        ],
                    ],
                ],
            ],
        ],
    ],
    [
        'code' => '5000',
        'name' => 'Manufacturing Account',
        'slug' => 'manufacturing_root',
        'type' => 'expense',
        'is_group' => true,
        'report_root' => 'manufacturing',
        'children' => [
            [
                'code' => '5100',
                'name' => 'Direct Material Cost',
                'type' => 'expense',
                'is_group' => true,
                'children' => [
                    ['code' => '51001', 'name' => 'Raw Material Consumption', 'slug' => 'raw_material_consumption', 'type' => 'expense'],
                    ['code' => '51002', 'name' => 'Packing Material Consumption', 'slug' => 'packing_material_consumption', 'type' => 'expense'],
                ],
            ],
            [
                'code' => '5200',
                'name' => 'Direct Labor Cost',
                'type' => 'expense',
                'is_group' => true,
                'children' => [
                    ['code' => '52001', 'name' => 'Factory Labor Wages', 'slug' => 'factory_labor_wages', 'type' => 'expense'],
                    ['code' => '52002', 'name' => 'Production Staff Salary', 'slug' => 'production_staff_salary', 'type' => 'expense'],
                ],
            ],
            [
                'code' => '5300',
                'name' => 'Manufacturing Overhead',
                'type' => 'expense',
                'is_group' => true,
                'children' => [
                    ['code' => '53001', 'name' => 'Factory Electricity', 'slug' => 'factory_electricity', 'type' => 'expense'],
                    ['code' => '53002', 'name' => 'Machinery Rent', 'slug' => 'machinery_rent', 'type' => 'expense'],
                    ['code' => '53003', 'name' => 'Factory Maintenance', 'slug' => 'factory_maintenance', 'type' => 'expense'],
                    ['code' => '53004', 'name' => 'Production Consumables', 'slug' => 'production_consumables', 'type' => 'expense'],
                ],
            ],
            [
                'code' => '5400',
                'name' => 'Inventory Adjustment',
                'type' => 'expense',
                'is_group' => true,
                'children' => [
                    ['code' => '54001', 'name' => 'Opening Stock', 'slug' => 'opening_stock', 'type' => 'expense'],
                    ['code' => '54002', 'name' => 'Closing Stock', 'slug' => 'closing_stock', 'type' => 'expense'],
                ],
            ],
        ],
    ],
    [
        'code' => '6000',
        'name' => 'OPERATING EXPENSES',
        'slug' => 'operatingexpense_root',
        'type' => 'expense',
        'is_group' => true,
        'report_root' => 'operatingexpense',
        'children' => [
            [
                'code' => '6100',
                'name' => 'Administrative Expenses',
                'type' => 'expense',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '6110',
                        'name' => 'Employee Costs',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '61101', 'name' => 'Salaries & Wages', 'slug' => 'salaries_wages', 'type' => 'expense'],
                            ['code' => '61102', 'name' => 'Bonus Expense', 'slug' => 'bonus_expense', 'type' => 'expense'],
                            ['code' => '61103', 'name' => 'Provident Fund Expense', 'slug' => 'provident_fund_expense', 'type' => 'expense'],
                        ],
                    ],
                    [
                        'code' => '6120',
                        'name' => 'Office Expenses',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '61201', 'name' => 'Office Rent', 'slug' => 'office_rent', 'type' => 'expense'],
                            ['code' => '61202', 'name' => 'Electricity Expense', 'slug' => 'electricity_expense', 'type' => 'expense'],
                            ['code' => '61203', 'name' => 'Internet Bill', 'slug' => 'internet_bill', 'type' => 'expense'],
                            ['code' => '61204', 'name' => 'Printing & Stationery', 'slug' => 'printing_stationery', 'type' => 'expense'],
                            ['code' => '61205', 'name' => 'Office Supplies', 'slug' => 'office_supplies', 'type' => 'expense'],
                            ['code' => '61206', 'name' => 'Utilities Expense', 'slug' => 'utilities_expense', 'type' => 'expense'],
                        ],
                    ],
                    [
                        'code' => '6130',
                        'name' => 'Professional Fees',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '61301', 'name' => 'Audit Fees', 'slug' => 'audit_fees', 'type' => 'expense'],
                            ['code' => '61302', 'name' => 'Legal Fees', 'slug' => 'legal_fees', 'type' => 'expense'],
                            ['code' => '61303', 'name' => 'Consultancy Fees', 'slug' => 'consultancy_fees', 'type' => 'expense'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '6200',
                'name' => 'Selling & Distribution Expenses',
                'type' => 'expense',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '6210',
                        'name' => 'Marketing Expenses',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '62101', 'name' => 'Advertisement Expense', 'slug' => 'advertisement_expense', 'type' => 'expense'],
                            ['code' => '62102', 'name' => 'Promotional Expense', 'slug' => 'promotional_expense', 'type' => 'expense'],
                            ['code' => '62103', 'name' => 'Branding Expense', 'slug' => 'branding_expense', 'type' => 'expense'],
                            ['code' => '62104', 'name' => 'Marketing Expense', 'slug' => 'marketing_expense', 'type' => 'expense'],
                        ],
                    ],
                    [
                        'code' => '6220',
                        'name' => 'Transportation Expenses',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '62201', 'name' => 'Delivery Expense', 'slug' => 'delivery_expense', 'type' => 'expense'],
                            ['code' => '62202', 'name' => 'Vehicle Fuel Expense', 'slug' => 'vehicle_fuel', 'type' => 'expense'],
                            ['code' => '62203', 'name' => 'Courier Expense', 'slug' => 'courier_expense', 'type' => 'expense'],
                            ['code' => '62204', 'name' => 'Selling & Distribution Expense', 'slug' => 'selling_distribution', 'type' => 'expense'],
                        ],
                    ],
                    [
                        'code' => '6230',
                        'name' => 'Commission & Write-offs',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '62301', 'name' => 'Commission Expense', 'slug' => 'commission_expense', 'type' => 'expense'],
                            ['code' => '62302', 'name' => 'Inventory Write-Off Expense', 'slug' => 'inventory_write_off', 'type' => 'expense'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '6300',
                'name' => 'Financial Expenses',
                'type' => 'expense',
                'is_group' => true,
                'children' => [
                    [
                        'code' => '6310',
                        'name' => 'Interest & Charges',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '63101', 'name' => 'Loan Interest Expense', 'slug' => 'loan_interest', 'type' => 'expense'],
                            ['code' => '63102', 'name' => 'Bank Charges', 'slug' => 'bank_charges', 'type' => 'expense'],
                            ['code' => '63103', 'name' => 'Foreign Exchange Loss', 'slug' => 'fx_loss', 'type' => 'expense'],
                        ],
                    ],
                    [
                        'code' => '6320',
                        'name' => 'Purchases',
                        'type' => 'expense',
                        'is_group' => true,
                        'children' => [
                            ['code' => '63201', 'name' => 'Purchases', 'slug' => 'purchases', 'type' => 'expense'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
