<?php

namespace App\Support\Assistant;

/**
 * Single source of truth for Saf AI Assistant data questions.
 *
 * Architecture (no LLM required):
 * 1. isBusinessQuestion() — live-data gate (never route to Learning Hub)
 * 2. AssistantIntentRouter — keyword + synonym scoring → intent id
 * 3. ErpAssistantInsightsService — real-time snapshot from ERP tables
 * 4. AssistantResponseComposer — human-readable answer from snapshot
 */
class AssistantDataCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function intents(): array
    {
        return [
            // ── Finance (live) ──────────────────────────────────────────────
            self::live('revenue_mtd', 'Finance', 'How much revenue this month?', [
                'revenue', 'sales this month', 'money we make', 'how much we make', 'invoiced',
                'mtd revenue', 'monthly revenue', 'income this month', 'revenue this month',
                'revenue for this month', 'what the revenue', 'total sales', 'sales revenue',
                'how much revenue', 'turnover this month', 'what did we sell',
            ], [
                'How much revenue this month?',
                'What the revenue for this month?',
                'What is our sales this month?',
                'How much money did we make this month?',
                'Total invoiced sales MTD?',
            ], 'finance', 'Invoiced net sales after credit notes for {period}.'),

            self::live('revenue_today', 'Finance', 'How much did we make today?', [
                'revenue today', 'sales today', 'made today', 'today revenue', 'today sales',
                'how much today', 'sales so far today', 'invoiced today',
            ], [
                'How much revenue today?',
                'What did we sell today?',
                'Today\'s sales?',
            ], 'finance', 'Invoiced net sales after credits for today only.'),

            self::live('profit_estimate_mtd', 'Finance', 'What is our profit this month?', [
                'profit', 'net profit', 'earning', 'how much profit', 'profit this month',
                'margin', 'our profit', 'what is our profit', 'are we profitable',
                'net income', 'bottom line', 'profit estimate',
            ], [
                'What is our profit this month?',
                'Are we profitable this month?',
                'How much profit did we make?',
                'What is the profit estimate?',
            ], 'finance', 'Revenue minus expenses, gifts, campaigns, and payroll for {period} (management estimate).'),

            self::live('receivables', 'Finance', 'How much is outstanding?', [
                'receivable', 'receivables', 'debt', 'outstanding', 'owe us', 'unpaid',
                'ar balance', 'money owed', 'how much is outstanding', 'how much outstanding',
                'customer debt', 'accounts receivable', 'open balance', 'money customers owe',
            ], [
                'How much is outstanding?',
                'What are our receivables?',
                'How much do customers owe us?',
                'Outstanding balance?',
            ], 'finance', 'Sum of open invoice balances as of today.'),

            self::live('collections_mtd', 'Finance', 'How much did we collect this month?', [
                'collection', 'collections', 'collected', 'cash received', 'payment received',
                'money collected', 'receipts this month', 'payments this month',
            ], [
                'How much did we collect this month?',
                'Collections MTD?',
                'How much cash did we receive?',
            ], 'finance', 'Receipts recorded in {period}.'),

            self::live('collection_rate', 'Finance', 'What is our collection rate?', [
                'collection rate', 'collection percentage', 'cash collection rate',
                'percent collected', 'how much of sales collected',
            ], [
                'What is our collection rate?',
                'Collection rate this month?',
                'What percentage of sales have we collected?',
            ], 'finance', 'Collections ÷ invoiced sales for {period}, as a percentage.'),

            self::live('overdue_invoices', 'Finance', 'Any overdue invoices?', [
                'overdue', 'late invoice', 'past due', 'due invoice', 'overdue invoice',
                'late payment', 'unpaid overdue',
            ], [
                'Any overdue invoices?',
                'How many invoices are overdue?',
                'Past due invoices?',
            ], 'finance', 'Count of issued invoices past due date with open balance.'),

            self::live('expenses_mtd', 'Finance', 'What are our expenses this month?', [
                'expense', 'expenses', 'spending', 'cost this month', 'operating cost',
                'operating expenses', 'how much did we spend', 'costs this month',
            ], [
                'What are our expenses this month?',
                'Operating expenses MTD?',
                'How much have we spent?',
            ], 'finance', 'Recorded expenses plus gifts and campaign costs in {period} (excl. payroll).'),

            self::live('payroll_mtd', 'Finance', 'Payroll cost this month?', [
                'payroll', 'salary', 'salaries', 'wages', 'staff cost', 'employee cost',
                'payroll this month', 'salary cost',
            ], [
                'Payroll cost this month?',
                'How much payroll this month?',
                'Staff salary cost?',
            ], 'finance', 'Salary distributions for {period} (base + bonus + allowances + commission).'),

            // ── Sales (live) ────────────────────────────────────────────────
            self::live('orders_mtd', 'Sales', 'How many sales orders this month?', [
                'orders this month', 'monthly orders', 'sales orders', 'order count',
                'how many orders', 'orders mtd', 'sales order count',
            ], [
                'How many sales orders this month?',
                'Order count this month?',
                'How many orders MTD?',
            ], 'sales', 'Non-return orders with delivery date in {period}.'),

            self::live('orders_today', 'Sales', 'Orders scheduled for today?', [
                'orders today', 'today orders', 'deliveries today', 'orders due today',
                'delivery schedule today', 'how many orders today',
            ], [
                'Orders scheduled for today?',
                'How many orders today?',
                'Deliveries due today?',
            ], 'sales', 'Non-return orders with delivery date = today.'),

            self::live('returns_mtd', 'Sales', 'How many returns this month?', [
                'return', 'returns', 'return orders', 'customer return', 'sales returns',
                'how many returns',
            ], [
                'How many returns this month?',
                'Return orders MTD?',
                'Customer returns this month?',
            ], 'sales', 'Return-type orders created in {period}.'),

            self::live('pending_deliveries', 'Sales', 'Pending deliveries?', [
                'delivery', 'deliveries', 'pending delivery', 'dispatch', 'to deliver',
                'awaiting dispatch', 'orders to deliver', 'not delivered yet',
            ], [
                'Pending deliveries?',
                'How many orders awaiting delivery?',
                'Orders to dispatch?',
            ], 'sales', 'Orders in confirmed → dispatched stages.'),

            self::live('sales_target', 'Sales', 'Sales target progress?', [
                'target', 'sales target', 'quota', 'target progress', 'achieved target',
                'sales quota', 'target vs actual', 'how are we vs target',
            ], [
                'Sales target progress?',
                'Are we hitting target?',
                'Target vs achieved this month?',
            ], 'sales', 'Active monthly target vs achieved revenue for {period}.'),

            // ── Agents (live) ───────────────────────────────────────────────
            self::live('active_agents', 'Agents', 'How many active agents?', [
                'agent', 'agents', 'selling partner', 'active agents', 'sales team',
                'distributors', 'how many agents',
            ], [
                'How many active agents?',
                'Active selling agents?',
                'Agent count?',
            ], 'agents', 'Count of agents with is_active = true.'),

            // ── Production (live) ─────────────────────────────────────────────
            self::live('production_today', 'Production', 'Production quantity today?', [
                'production', 'produced today', 'production today', 'manufacturing today',
                'output today', 'units produced', 'how much produced today',
            ], [
                'Production quantity today?',
                'How much did we produce today?',
                'Manufacturing output today?',
            ], 'production', 'Sum of QC-approved production run quantities created today.'),

            self::live('pending_qc', 'Production', 'Pending QC approvals?', [
                'qc', 'quality check', 'pending qc', 'quality approval', 'production approval',
                'awaiting qc', 'runs pending qc',
            ], [
                'Pending QC approvals?',
                'How many runs need QC?',
                'Production waiting for QC?',
            ], 'production', 'Production runs where qc_status ≠ approved.'),

            // ── Inventory (live) ────────────────────────────────────────────
            self::live('low_stock_count', 'Inventory', 'Low stock alerts?', [
                'low stock', 'stock alert', 'reorder', 'out of stock', 'inventory alert',
                'stock shortage', 'reorder level', 'skus low',
            ], [
                'Low stock alerts?',
                'Any low stock items?',
                'How many SKUs need reorder?',
            ], 'inventory', 'Sellable SKUs at or below reorder level.'),

            // ── Operations (live, mixed permissions) ────────────────────────
            self::live('ops_tasks', 'Operations', 'What tasks need attention?', [
                'task', 'tasks', 'my task', 'my tasks', 'tasks today', 'task today',
                'what to do', 'attention', 'needs action', 'what needs', 'pending tasks',
                'todo', 'to do', 'work today', 'tasks need attention', 'need attention',
                'what should i do', 'anything pending', 'issues today',
            ], [
                'What tasks need attention?',
                'What should I focus on today?',
                'Anything pending?',
                'What needs action?',
            ], null, 'Aggregated alerts: pending QC, low stock, overdue invoices, pending deliveries.'),

            self::live('business_summary', 'Operations', 'Business summary', [
                'summary', 'overview', 'snapshot', 'business summary', 'quick update',
                'status update', 'give me an update', 'full picture', 'tell me everything',
            ], [
                'Give me a business summary',
                'Quick business snapshot',
                'Full update on the business',
            ], null, 'Multi-line snapshot of finance, sales, and production (permission-filtered).'),

            self::live('business_health', 'Operations', 'Is business doing well?', [
                'doing well', 'how are we', 'business health', 'performance', 'on track',
                'how is business', 'are we ok', 'doing good', 'health check',
            ], [
                'Is business doing well?',
                'How is the business performing?',
                'Are we on track?',
            ], null, 'Narrative health check from collections, profit, target, and task count.'),
        ];
    }

    /**
     * Navigation and definition answers (static text, not live numbers).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function staticEntries(): array
    {
        return [
            self::static('greeting', 'General', 'Hello', ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'salam', 'assalam'], null, 'greeting'),
            self::static('who_are_you', 'General', 'Who are you?', ['who are you', 'what are you', 'your name', 'introduce'], "I'm Saf AI Assistant — your live business companion inside this ERP. I read real data from sales, finance, production, and inventory to answer your questions instantly."),
            self::static('what_can_you_do', 'General', 'What can you help with?', ['what can you do', 'what you can do', 'what can be done', 'what could you do', 'what can i ask', 'what do you do', 'help me', 'how can you help', 'what do you know', 'capabilities', 'what are your capabilities', 'what can this do'], self::capabilitiesAnswer()),
            self::static('nav_finance', 'Navigation', 'Where do I see finance?', ['finance', 'invoices', 'invoice list', 'accounts receivable', 'where finance', 'open finance'], 'Go to Finance in the menu to view invoices, receipts, and outstanding balances.', 'admin.finance.index'),
            self::static('nav_orders', 'Navigation', 'Where are sales orders?', ['orders module', 'sales orders page', 'order list', 'where orders', 'open orders'], 'Open Sales → Orders to view, create, and track customer orders and returns.', 'admin.orders.index'),
            self::static('nav_production', 'Navigation', 'Where is production?', ['production module', 'manufacturing page', 'where production', 'open production'], 'Open Manufacturing → Production to review runs, QC status, and daily output.', 'admin.production.index'),
            self::static('nav_accounting', 'Navigation', 'Where is the accounting dashboard?', ['accounting dashboard', 'accounting report', 'accounting page', 'open accounting'], 'Open Accounting → Dashboard for period-based revenue, collections, expenses, and profit estimates.', 'admin.accounting.dashboard'),
            self::static('nav_reports', 'Navigation', 'Where are reports?', ['reports', 'analytics', 'reporting', 'open reports'], 'Check Reports & analytics in the menu for dashboards, VAT reports, and the export center.', 'admin.reports.dashboard'),
            self::static('nav_export', 'Navigation', 'Where is the export center?', ['export', 'export center', 'download data', 'csv export', 'data export'], 'Open Reports & analytics → Data export in the menu, or go directly to the Export center. You can pick a module, date range, and download CSV or PDF.', 'admin.export-center'),
            self::static('nav_sales_dashboard', 'Navigation', 'Where is the sales dashboard?', ['sales dashboard', 'sales overview page'], 'Open Sales → Dashboard for order pipeline, fulfillment, and sales KPIs.', 'admin.sales.dashboard'),
            self::static('nav_manufacturing_dashboard', 'Navigation', 'Where is manufacturing dashboard?', ['manufacturing dashboard', 'production dashboard'], 'Open Manufacturing → Dashboard for production KPIs and run status.', 'admin.manufacturing.dashboard'),
            self::static('nav_warehouses', 'Navigation', 'Where is warehouse dashboard?', ['warehouse dashboard', 'warehouses page', 'where warehouses'], 'Open Control → Warehouses dashboard for stock by location, vehicles, and deliveries.', 'admin.warehouses.dashboard'),
            self::static('nav_inventory', 'Navigation', 'Where is inventory?', ['inventory module', 'stock page', 'where inventory', 'stock list'], 'Open Inventory in the menu for stock levels, transfers, and low-stock alerts.', 'admin.inventory.index'),
            self::static('nav_agents', 'Navigation', 'Where are agents?', ['agents page', 'where agents', 'agent list'], 'Open Control → Agents to manage selling partners, zones, and credit limits.', 'admin.agents.index'),
            self::static('nav_learning', 'Navigation', 'Where is ERP Academy?', ['learning hub', 'erp academy', 'training', 'courses', 'where training'], 'Open ERP Academy (Learning Hub) from the sidebar or Control menu for step-by-step courses and quizzes.', 'admin.learning-hub'),
            self::static('def_receivables', 'Definitions', 'What does receivables mean?', ['what is receivable', 'what are receivables', 'meaning receivable', 'define receivable'], 'Receivables (AR) is money customers still owe you on issued invoices. Collections are payments already received. Ask me "how much is outstanding?" for the live balance.'),
            self::static('def_mtd', 'Definitions', 'What is MTD?', ['what is mtd', 'meaning mtd', 'month to date'], 'MTD means Month to Date — from the 1st of this month through today. Most live answers use the current calendar month.'),
            self::static('def_profit', 'Definitions', 'How is profit calculated?', ['how profit calculated', 'profit formula', 'profit estimate meaning'], 'Profit estimate = invoiced sales (after credits) minus operating expenses, gifts, campaigns, and payroll for the period. It is a management estimate, not an audited P&L.'),
            self::static('def_revenue', 'Definitions', 'What counts as revenue?', ['what is revenue', 'define revenue', 'revenue meaning', 'what counts as sales'], 'Revenue here means net invoiced sales after credit notes in the period — the same figure used on the Accounting dashboard.'),
            self::static('thanks', 'General', 'Thank you', ['thank', 'thanks', 'goodbye', 'bye', 'see you'], "You're welcome! I'm here whenever you need a quick read on the business. Have a productive day!"),
            self::static('joke_arif', 'General', 'Do you know Arif?', ['arif', 'do you know arif', 'who is arif', 'know arif', 'about arif', 'who is the owner', 'who owns saf', 'owner arif', 'saf owner', 'my boyfriend', 'your boyfriend'], "Of course I know Arif — he's the owner of Saf ERP, the one running this whole show. And if you ask nicely… yes, I may call him my boyfriend. Don't tell finance — they already think I spend too much time on revenue numbers."),
        ];
    }

    /**
     * Synonym phrases keyed by intent id (longest match wins in router).
     *
     * @return array<string, array<int, string>>
     */
    public static function synonymMap(): array
    {
        $map = [];
        foreach (self::intents() as $intent) {
            $examples = $intent['example_questions'] ?? [];
            $keywords = $intent['keywords'] ?? [];
            $phrases = array_values(array_unique(array_merge(
                array_slice($examples, 0, 6),
                array_filter($keywords, fn ($k) => str_contains($k, ' ') || strlen($k) > 8)
            )));
            if ($phrases !== []) {
                $map[$intent['id']] = $phrases;
            }
        }

        return $map;
    }

    /**
     * Business signals for isBusinessQuestion() — derived from live intents.
     *
     * @return array<int, string>
     */
    public static function businessSignals(): array
    {
        $signals = [];
        foreach (self::intents() as $intent) {
            foreach ($intent['keywords'] as $keyword) {
                if (strlen($keyword) >= 4) {
                    $signals[] = $keyword;
                }
            }
        }

        return array_values(array_unique($signals));
    }

    /**
     * Quick-ask chips grouped for bootstrap UI.
     *
     * @return array<int, array<string, string>>
     */
    public static function quickAsks(): array
    {
        return [
            ['label' => 'Revenue this month', 'message' => 'How much revenue this month?'],
            ['label' => 'Profit estimate', 'message' => 'What is our profit this month?'],
            ['label' => 'Outstanding', 'message' => 'How much is outstanding?'],
            ['label' => 'Collections', 'message' => 'How much did we collect this month?'],
            ['label' => 'Pending tasks', 'message' => 'What tasks need attention?'],
            ['label' => 'Business summary', 'message' => 'Give me a business summary'],
            ['label' => 'Orders today', 'message' => 'Orders scheduled for today?'],
            ['label' => 'Low stock', 'message' => 'Low stock alerts?'],
        ];
    }

    /**
     * Full Q&A reference for docs / debugging.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function coverageReport(): array
    {
        $rows = [];

        foreach (self::intents() as $intent) {
            $rows[] = [
                'category' => $intent['category'],
                'id' => $intent['id'],
                'type' => 'live',
                'canonical_question' => $intent['canonical_question'],
                'example_questions' => $intent['example_questions'],
                'answer' => $intent['answer_template'],
                'permission' => $intent['permission'],
            ];
        }

        foreach (self::staticEntries() as $entry) {
            if (($entry['type'] ?? '') === 'greeting') {
                continue;
            }
            $rows[] = [
                'category' => $entry['category'],
                'id' => $entry['id'],
                'type' => $entry['type'] ?? 'static',
                'canonical_question' => $entry['question'],
                'example_questions' => [$entry['question']],
                'answer' => $entry['answer'] ?? '',
                'permission' => null,
            ];
        }

        return $rows;
    }

    /**
     * Build FAQ catalog metric entries from this catalog.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function faqMetricEntries(): array
    {
        $entries = [];
        foreach (self::intents() as $intent) {
            $entries[] = [
                'id' => $intent['id'],
                'keywords' => $intent['keywords'],
                'question' => $intent['canonical_question'],
                'type' => 'metric',
                'metric' => $intent['id'],
                'permission' => $intent['permission'],
            ];
        }

        return $entries;
    }

    /**
     * Build FAQ catalog static entries from this catalog.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function faqStaticEntries(): array
    {
        $entries = [];
        foreach (self::staticEntries() as $entry) {
            $faq = [
                'id' => $entry['id'],
                'keywords' => $entry['keywords'],
                'question' => $entry['question'],
                'type' => $entry['type'] ?? 'static',
            ];
            if (isset($entry['answer'])) {
                $faq['answer'] = $entry['answer'];
            }
            if (isset($entry['link'])) {
                $faq['link'] = $entry['link'];
            }
            $entries[] = $faq;
        }

        return $entries;
    }

    protected static function capabilitiesAnswer(): string
    {
        $categories = [];
        foreach (self::intents() as $intent) {
            $categories[$intent['category']] = true;
        }

        $list = implode(', ', array_keys($categories));

        return "I answer live ERP data in plain language — {$list}. I also explain ERP processes (GRN, POD, BOM), where to click in the menu, and definitions like MTD and receivables. Just ask naturally; I pull real numbers from your database.";
    }

    /**
     * @param  array<int, string>  $keywords
     * @param  array<int, string>  $examples
     * @return array<string, mixed>
     */
    protected static function live(
        string $id,
        string $category,
        string $canonicalQuestion,
        array $keywords,
        array $examples,
        ?string $permission,
        string $answerTemplate
    ): array {
        return [
            'id' => $id,
            'category' => $category,
            'canonical_question' => $canonicalQuestion,
            'keywords' => $keywords,
            'example_questions' => $examples,
            'permission' => $permission,
            'answer_template' => $answerTemplate,
        ];
    }

    /**
     * @param  array<int, string>  $keywords
     * @return array<string, mixed>
     */
    protected static function static(
        string $id,
        string $category,
        string $question,
        array $keywords,
        ?string $answer,
        ?string $link = null,
        ?string $type = null
    ): array {
        $entry = [
            'id' => $id,
            'category' => $category,
            'question' => $question,
            'keywords' => $keywords,
            'type' => $type ?? 'static',
        ];
        if ($answer !== null) {
            $entry['answer'] = $answer;
        }
        if ($link !== null) {
            $entry['link'] = $link;
        }

        return $entry;
    }
}
