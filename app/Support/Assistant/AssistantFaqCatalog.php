<?php

namespace App\Support\Assistant;

class AssistantFaqCatalog
{
    public static function entries(): array
    {
        return [
            [
                'id' => 'greeting',
                'keywords' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'salam', 'assalam'],
                'question' => 'Hello',
                'type' => 'greeting',
            ],
            [
                'id' => 'who_are_you',
                'keywords' => ['who are you', 'what are you', 'your name', 'introduce'],
                'question' => 'Who are you?',
                'type' => 'static',
                'answer' => "I'm Saf ERP Assistant — your live business companion inside this ERP. I read real data from sales, finance, production, and inventory to answer your questions instantly.",
            ],
            [
                'id' => 'what_can_you_do',
                'keywords' => ['what can you do', 'what you can do', 'what can be done', 'what could you do', 'what can i ask', 'what do you do', 'help me', 'how can you help', 'what do you know', 'capabilities', 'what are your capabilities', 'what can this do'],
                'question' => 'What can you help with?',
                'type' => 'static',
                'answer' => "I can tell you about revenue, profit, receivables, collections, orders, production, stock alerts, and pending tasks. I can also guide you to modules like Finance, Orders, Export center, and Reports. Just ask in plain language!",
            ],
            [
                'id' => 'revenue_mtd',
                'keywords' => ['revenue', 'sales this month', 'money we make', 'how much we make', 'invoiced', 'mtd revenue', 'monthly revenue', 'income this month'],
                'question' => 'How much revenue this month?',
                'type' => 'metric',
                'metric' => 'revenue_mtd',
                'permission' => 'finance',
            ],
            [
                'id' => 'revenue_today',
                'keywords' => ['revenue today', 'sales today', 'made today', 'today revenue', 'today sales'],
                'question' => 'How much did we make today?',
                'type' => 'metric',
                'metric' => 'revenue_today',
                'permission' => 'finance',
            ],
            [
                'id' => 'profit_estimate_mtd',
                'keywords' => ['profit', 'net profit', 'earning', 'how much profit', 'profit this month', 'margin'],
                'question' => 'What is our profit this month?',
                'type' => 'metric',
                'metric' => 'profit_estimate_mtd',
                'permission' => 'finance',
            ],
            [
                'id' => 'receivables',
                'keywords' => ['receivable', 'receivables', 'debt', 'outstanding', 'owe us', 'unpaid', 'ar balance', 'money owed'],
                'question' => 'How much is outstanding?',
                'type' => 'metric',
                'metric' => 'receivables',
                'permission' => 'finance',
            ],
            [
                'id' => 'collections_mtd',
                'keywords' => ['collection', 'collections', 'collected', 'cash received', 'payment received'],
                'question' => 'How much did we collect this month?',
                'type' => 'metric',
                'metric' => 'collections_mtd',
                'permission' => 'finance',
            ],
            [
                'id' => 'overdue_invoices',
                'keywords' => ['overdue', 'late invoice', 'past due', 'due invoice'],
                'question' => 'Any overdue invoices?',
                'type' => 'metric',
                'metric' => 'overdue_invoices',
                'permission' => 'finance',
            ],
            [
                'id' => 'expenses_mtd',
                'keywords' => ['expense', 'expenses', 'spending', 'cost this month', 'operating cost'],
                'question' => 'What are our expenses this month?',
                'type' => 'metric',
                'metric' => 'expenses_mtd',
                'permission' => 'finance',
            ],
            [
                'id' => 'payroll_mtd',
                'keywords' => ['payroll', 'salary', 'salaries', 'wages', 'staff cost'],
                'question' => 'Payroll cost this month?',
                'type' => 'metric',
                'metric' => 'payroll_mtd',
                'permission' => 'finance',
            ],
            [
                'id' => 'orders_mtd',
                'keywords' => ['orders this month', 'monthly orders', 'sales orders', 'order count', 'how many orders'],
                'question' => 'How many sales orders this month?',
                'type' => 'metric',
                'metric' => 'orders_mtd',
                'permission' => 'sales',
            ],
            [
                'id' => 'orders_today',
                'keywords' => ['orders today', 'today orders', 'deliveries today'],
                'question' => 'Orders scheduled for today?',
                'type' => 'metric',
                'metric' => 'orders_today',
                'permission' => 'sales',
            ],
            [
                'id' => 'returns_mtd',
                'keywords' => ['return', 'returns', 'return orders', 'customer return'],
                'question' => 'How many returns this month?',
                'type' => 'metric',
                'metric' => 'returns_mtd',
                'permission' => 'sales',
            ],
            [
                'id' => 'active_agents',
                'keywords' => ['agent', 'agents', 'selling partner', 'active agents', 'sales team'],
                'question' => 'How many active agents?',
                'type' => 'metric',
                'metric' => 'active_agents',
                'permission' => 'agents',
            ],
            [
                'id' => 'sales_target',
                'keywords' => ['target', 'sales target', 'quota', 'target progress', 'achieved target'],
                'question' => 'Sales target progress?',
                'type' => 'metric',
                'metric' => 'sales_target',
                'permission' => 'sales',
            ],
            [
                'id' => 'production_today',
                'keywords' => ['production', 'produced today', 'production today', 'manufacturing today', 'output today'],
                'question' => 'Production quantity today?',
                'type' => 'metric',
                'metric' => 'production_today',
                'permission' => 'production',
            ],
            [
                'id' => 'pending_qc',
                'keywords' => ['qc', 'quality check', 'pending qc', 'quality approval', 'production approval'],
                'question' => 'Pending QC approvals?',
                'type' => 'metric',
                'metric' => 'pending_qc',
                'permission' => 'production',
            ],
            [
                'id' => 'pending_deliveries',
                'keywords' => ['delivery', 'deliveries', 'pending delivery', 'dispatch', 'to deliver'],
                'question' => 'Pending deliveries?',
                'type' => 'metric',
                'metric' => 'pending_deliveries',
                'permission' => 'sales',
            ],
            [
                'id' => 'low_stock_count',
                'keywords' => ['low stock', 'stock alert', 'reorder', 'out of stock', 'inventory alert'],
                'question' => 'Low stock alerts?',
                'type' => 'metric',
                'metric' => 'low_stock_count',
                'permission' => 'inventory',
            ],
            [
                'id' => 'ops_tasks',
                'keywords' => ['task', 'tasks', 'attention', 'needs action', 'what needs', 'pending tasks', 'todo', 'to do'],
                'question' => 'What tasks need attention?',
                'type' => 'metric',
                'metric' => 'ops_tasks',
                'permission' => null,
            ],
            [
                'id' => 'business_summary',
                'keywords' => ['summary', 'overview', 'snapshot', 'business summary', 'quick update', 'status update'],
                'question' => 'Business summary',
                'type' => 'metric',
                'metric' => 'business_summary',
                'permission' => null,
            ],
            [
                'id' => 'business_health',
                'keywords' => ['doing well', 'how are we', 'business health', 'performance', 'on track'],
                'question' => 'Is business doing well?',
                'type' => 'metric',
                'metric' => 'business_health',
                'permission' => null,
            ],
            [
                'id' => 'nav_export',
                'keywords' => ['export', 'export center', 'download data', 'csv export', 'data export'],
                'question' => 'Where is the export center?',
                'type' => 'static',
                'answer' => 'Open Reports & analytics → Data export in the menu, or go directly to the Export center. You can pick a module, date range, and download CSV or PDF.',
                'link' => 'admin.export-center',
            ],
            [
                'id' => 'nav_finance',
                'keywords' => ['finance', 'invoices', 'invoice list', 'accounts receivable'],
                'question' => 'Where do I see finance?',
                'type' => 'static',
                'answer' => 'Go to Finance in the menu to view invoices, receipts, and outstanding balances.',
                'link' => 'admin.finance.index',
            ],
            [
                'id' => 'nav_orders',
                'keywords' => ['orders module', 'sales orders page', 'order list', 'where orders'],
                'question' => 'Where are sales orders?',
                'type' => 'static',
                'answer' => 'Open Sales → Orders to view, create, and track customer orders and returns.',
                'link' => 'admin.orders.index',
            ],
            [
                'id' => 'nav_production',
                'keywords' => ['production module', 'manufacturing page', 'where production'],
                'question' => 'Where is production?',
                'type' => 'static',
                'answer' => 'Open Manufacturing → Production to review runs, QC status, and daily output.',
                'link' => 'admin.production.index',
            ],
            [
                'id' => 'nav_accounting',
                'keywords' => ['accounting dashboard', 'accounting report', 'accounting page'],
                'question' => 'Where is the accounting dashboard?',
                'type' => 'static',
                'answer' => 'Open Accounting → Dashboard for period-based revenue, collections, expenses, and profit estimates.',
                'link' => 'admin.accounting.dashboard',
            ],
            [
                'id' => 'nav_reports',
                'keywords' => ['reports', 'analytics', 'reporting'],
                'question' => 'Where are reports?',
                'type' => 'static',
                'answer' => 'Check Reports & analytics in the menu for dashboards, VAT reports, and the export center.',
                'link' => 'admin.reports.dashboard',
            ],
            [
                'id' => 'def_receivables',
                'keywords' => ['what is receivable', 'what are receivables', 'meaning receivable', 'define receivable'],
                'question' => 'What does receivables mean?',
                'type' => 'static',
                'answer' => 'Receivables (AR) is money customers still owe you on issued invoices. Collections are payments already received. I can tell you both numbers from live data.',
            ],
            [
                'id' => 'def_mtd',
                'keywords' => ['what is mtd', 'meaning mtd', 'month to date'],
                'question' => 'What is MTD?',
                'type' => 'static',
                'answer' => 'MTD means Month to Date — from the 1st of this month through today. Most of my answers use the current calendar month unless you specify otherwise.',
            ],
            [
                'id' => 'def_profit',
                'keywords' => ['how profit calculated', 'profit formula', 'profit estimate'],
                'question' => 'How is profit calculated?',
                'type' => 'static',
                'answer' => 'Profit estimate = invoiced sales (after credits) minus operating expenses, gifts, campaigns, and payroll for the period. It is a management estimate, not an audited P&L statement.',
            ],
            [
                'id' => 'joke_arif',
                'keywords' => ['arif', 'do you know arif', 'who is arif', 'know arif', 'about arif', 'who is the owner', 'who owns saf', 'owner arif', 'saf owner', 'my boyfriend', 'your boyfriend'],
                'question' => 'Do you know Arif?',
                'type' => 'static',
                'answer' => "Of course I know Arif — he's the owner of Saf ERP, the one running this whole show. And if you ask nicely… yes, I may call him my boyfriend. Don't tell finance — they already think I spend too much time on revenue numbers.",
            ],
            [
                'id' => 'thanks',
                'keywords' => ['thank', 'thanks', 'goodbye', 'bye', 'see you'],
                'question' => 'Thank you',
                'type' => 'static',
                'answer' => "You're welcome! I'm here whenever you need a quick read on the business. Have a productive day!",
            ],
        ];
    }

    public static function quickAsks(): array
    {
        return [
            ['label' => 'Revenue this month', 'message' => 'How much revenue this month?'],
            ['label' => 'Profit estimate', 'message' => 'What is our profit this month?'],
            ['label' => 'Outstanding receivables', 'message' => 'How much is outstanding?'],
            ['label' => 'Pending tasks', 'message' => 'What tasks need attention?'],
            ['label' => 'Production today', 'message' => 'Production quantity today?'],
            ['label' => 'Business summary', 'message' => 'Give me a business summary'],
        ];
    }

    public static function findById(string $id): ?array
    {
        foreach (self::entries() as $entry) {
            if ($entry['id'] === $id) {
                return $entry;
            }
        }

        return null;
    }
}
