<?php

namespace App\Services\Assistant;

use App\Models\User;
use App\Support\Assistant\AssistantFaqCatalog;

class AssistantResponseComposer
{
    protected array $quotes = [
        'Small steps every day build great businesses.',
        'What gets measured gets managed — you are on the right track.',
        'Clarity today saves firefighting tomorrow.',
        'Focus on cash, customers, and consistency.',
        'Progress beats perfection. Keep moving forward.',
        'A clear dashboard is a calm mind.',
        'Today is a good day to close one more loop.',
        'Data is only useful when it leads to action.',
    ];

    public function __construct(
        protected ErpAssistantInsightsService $insights
    ) {}

    public function bootstrap(User $user, array $context = []): array
    {
        $firstName = trim(explode(' ', $user->name ?? 'there')[0]) ?: 'there';
        $locale = ($context['locale'] ?? 'en') === 'bn' ? 'bn' : 'en';
        $onLearningHub = ($context['source'] ?? '') === 'learning-hub';

        $greeting = $this->timeGreeting($firstName, $locale);
        $quote = $this->quoteForToday($locale);

        if ($onLearningHub) {
            $prompt = $locale === 'bn'
                ? 'ERP শেখা, ধাপ ব্যাখ্যা, GRN/POD/BOM — অথবা আজকের বিক্রয়, স্টক, বকেয়া — যেকোনো কিছু জিজ্ঞেস করুন।'
                : 'Ask about ERP training, steps and terms (GRN, POD, BOM), or live numbers like sales, stock, and receivables.';
        } else {
            $prompt = $locale === 'bn'
                ? 'ব্যবসার সংখ্যা, ERP-তে কোথায় যাবেন, বা GRN/POD মানে কী — যেকোনো কিছু জিজ্ঞেস করুন।'
                : 'Ask about live business numbers, where to go in the ERP, or what terms like GRN and POD mean.';
        }

        return [
            'greeting' => $greeting,
            'quote' => $quote,
            'prompt' => $prompt,
            'quick_asks' => $this->mergedQuickAsks($context),
            'agent_name' => 'Saf AI Assistant',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, string>>
     */
    protected function mergedQuickAsks(array $context): array
    {
        $learning = app(LearningAssistantService::class);
        $learningAsks = array_slice($learning->quickAsks($context), 0, 2);
        $businessAsks = AssistantFaqCatalog::quickAsks();

        $seen = [];
        $merged = [];

        foreach (array_merge($learningAsks, $businessAsks) as $ask) {
            $key = strtolower(trim($ask['message'] ?? $ask['label'] ?? ''));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $merged[] = $ask;
        }

        return array_slice($merged, 0, 6);
    }

    public function compose(User $user, array $route, string $originalMessage): array
    {
        $snapshot = $this->insights->snapshot($user);
        $entry = $route['entry'] ?? null;

        if ($route['type'] === 'greeting') {
            $boot = $this->bootstrap($user);

            return $this->wrap(
                $boot['greeting'] . ' ' . $boot['quote'] . ' ' . $boot['prompt'],
                []
            );
        }

        if ($route['type'] === 'unknown') {
            return $this->wrap(
                "I'm not sure I understood that yet. Try one of these, or rephrase your question:",
                [],
                AssistantFaqCatalog::quickAsks()
            );
        }

        if ($route['type'] === 'faq' && $entry) {
            $links = [];
            if (! empty($entry['link'])) {
                $links[] = $this->linkFromRoute($entry['link']);
            }

            return $this->wrap($entry['answer'] ?? 'Let me know if you need anything else.', $links);
        }

        if ($route['type'] === 'metric' && $entry) {
            $permission = $entry['permission'] ?? null;
            if ($permission && ! ($snapshot['permissions'][$permission] ?? false)) {
                return $this->wrap($this->insights->permissionDeniedMessage($permission), []);
            }

            $metric = $entry['metric'] ?? $route['metric'] ?? null;
            $reply = $this->metricReply($metric, $snapshot);
            $links = $this->metricLinks($metric);

            return $this->wrap($reply, $links);
        }

        return $this->wrap(
            "I couldn't find an answer for that. Try asking about revenue, receivables, profit, or pending tasks.",
            [],
            AssistantFaqCatalog::quickAsks()
        );
    }

    protected function metricReply(?string $metric, array $snapshot): string
    {
        $currency = $snapshot['currency'] ?? 'BDT';
        $period = $snapshot['period_label'] ?? 'this month';
        $finance = $snapshot['finance'] ?? [];
        $sales = $snapshot['sales'] ?? [];
        $production = $snapshot['production'] ?? [];
        $inventory = $snapshot['inventory'] ?? [];
        $agents = $snapshot['agents'] ?? [];
        $target = $snapshot['sales_target'] ?? [];
        $tasks = $snapshot['tasks'] ?? [];

        return match ($metric) {
            'revenue_mtd' => "For {$period}, invoiced sales after credits are {$this->money($finance['revenue_mtd'] ?? 0, $currency)}. That's your MTD revenue snapshot.",
            'revenue_today' => "Today's invoiced sales after credits are {$this->money($finance['revenue_today'] ?? 0, $currency)}.",
            'profit_estimate_mtd' => "My management profit estimate for {$period} is {$this->money($finance['profit_estimate_mtd'] ?? 0, $currency)} — revenue minus expenses and payroll. This is not an audited P&L; use Accounting reports for formal statements.",
            'receivables' => "Outstanding receivables right now are {$this->money($finance['receivables'] ?? 0, $currency)} — that's what customers still owe on open invoices.",
            'collections_mtd' => "Collections received in {$period} total {$this->money($finance['collections_mtd'] ?? 0, $currency)} (" . ($finance['collection_rate'] ?? 0) . '% of invoiced sales).',
            'collection_rate' => "Collection rate for {$period} is " . ($finance['collection_rate'] ?? 0) . "% — {$this->money($finance['collections_mtd'] ?? 0, $currency)} collected against {$this->money($finance['revenue_mtd'] ?? 0, $currency)} invoiced sales.",
            'overdue_invoices' => ($finance['overdue_invoices'] ?? 0) > 0
                ? "There are {$finance['overdue_invoices']} overdue invoice(s) with an open balance. Worth a follow-up in Finance."
                : 'Good news — no overdue invoices with an open balance right now.',
            'expenses_mtd' => "Operating expenses recorded in {$period} are {$this->money($finance['expenses_mtd'] ?? 0, $currency)} (excluding payroll).",
            'payroll_mtd' => "Payroll cost for {$period} is {$this->money($finance['payroll_mtd'] ?? 0, $currency)}.",
            'orders_mtd' => "You have {$sales['orders_mtd']} sales order(s) scheduled this month ({$period}).",
            'orders_today' => "There are {$sales['orders_today']} sales order(s) scheduled for delivery today.",
            'returns_mtd' => "Customer return orders this month: {$sales['returns_mtd']}.",
            'pending_deliveries' => "{$sales['pending_deliveries']} order(s) are in confirmed-to-dispatched delivery stages.",
            'active_agents' => "You currently have {$agents['active_count']} active selling agent(s).",
            'sales_target' => empty($target['target_mtd']) || ($target['target_mtd'] ?? 0) <= 0
                ? 'No monthly sales target is configured for the current period.'
                : "Sales target for {$period} is {$this->money($target['target_mtd'], $currency)}. Achieved {$this->money($target['achieved_mtd'] ?? 0, $currency)} ({$target['progress_percent']}%). Remaining gap: {$this->money($target['gap'] ?? 0, $currency)}.",
            'production_today' => "Approved production quantity today is {$this->number($production['quantity_today'] ?? 0)} units.",
            'pending_qc' => ($production['pending_qc'] ?? 0) > 0
                ? "{$production['pending_qc']} production run(s) are waiting for QC approval."
                : 'All production runs are QC-approved — nothing pending there.',
            'low_stock_count' => ($inventory['low_stock_count'] ?? 0) > 0
                ? "{$inventory['low_stock_count']} sellable SKU(s) are at or below reorder level."
                : 'No low-stock alerts on sellable items right now.',
            'ops_tasks' => $this->tasksReply($tasks, $snapshot),
            'business_summary' => $this->summaryReply($snapshot),
            'business_health' => $this->healthReply($snapshot),
            default => "I don't have data for that metric yet.",
        };
    }

    protected function tasksReply(array $tasks, array $snapshot): string
    {
        if (empty($tasks)) {
            return 'Nothing urgent flagged right now across QC, stock, deliveries, and overdue invoices.';
        }

        $lines = ['Here is what may need attention today:'];
        if (isset($tasks['pending_qc'])) {
            $lines[] = "• {$tasks['pending_qc']} production run(s) pending QC";
        }
        if (isset($tasks['low_stock'])) {
            $lines[] = "• {$tasks['low_stock']} low-stock SKU alert(s)";
        }
        if (isset($tasks['overdue_invoices'])) {
            $lines[] = "• {$tasks['overdue_invoices']} overdue invoice(s)";
        }
        if (isset($tasks['pending_deliveries'])) {
            $lines[] = "• {$tasks['pending_deliveries']} order(s) awaiting delivery dispatch";
        }

        return implode("\n", $lines);
    }

    protected function summaryReply(array $snapshot): string
    {
        $parts = [];
        $period = $snapshot['period_label'] ?? 'this month';
        $currency = $snapshot['currency'] ?? 'BDT';

        if (isset($snapshot['finance'])) {
            $f = $snapshot['finance'];
            $parts[] = "Revenue ({$period}): {$this->money($f['revenue_mtd'] ?? 0, $currency)}";
            $parts[] = "Collections: {$this->money($f['collections_mtd'] ?? 0, $currency)}";
            $parts[] = "Receivables: {$this->money($f['receivables'] ?? 0, $currency)}";
            $parts[] = "Profit estimate: {$this->money($f['profit_estimate_mtd'] ?? 0, $currency)}";
        }

        if (isset($snapshot['sales'])) {
            $s = $snapshot['sales'];
            $parts[] = "Orders MTD: {$s['orders_mtd']} | Today: {$s['orders_today']} | Returns: {$s['returns_mtd']}";
        }

        if (isset($snapshot['production'])) {
            $parts[] = 'Production today: ' . $this->number($snapshot['production']['quantity_today'] ?? 0) . ' units';
        }

        if (empty($parts)) {
            return 'I can only share summaries for modules you have permission to view.';
        }

        return "Quick business snapshot:\n" . implode("\n", array_map(fn ($line) => "• {$line}", $parts));
    }

    protected function healthReply(array $snapshot): string
    {
        $finance = $snapshot['finance'] ?? null;
        $target = $snapshot['sales_target'] ?? null;
        $tasks = $snapshot['task_total'] ?? 0;

        if (! $finance && ! $target) {
            return 'I need finance or sales access to assess overall business health. Ask your admin if you need broader permissions.';
        }

        $signals = [];

        if ($finance) {
            $rate = $finance['collection_rate'] ?? 0;
            $signals[] = $rate >= 70
                ? "Collections are healthy at {$rate}% of invoiced sales."
                : "Collections are at {$rate}% of invoiced sales — cash follow-up may help.";

            if (($finance['profit_estimate_mtd'] ?? 0) >= 0) {
                $signals[] = 'Profit estimate for the month is positive.';
            } else {
                $signals[] = 'Profit estimate is negative this month — review expenses and payroll.';
            }
        }

        if ($target && ($target['target_mtd'] ?? 0) > 0) {
            $progress = $target['progress_percent'] ?? 0;
            $signals[] = $progress >= 80
                ? "Sales target is {$progress}% achieved — strong pace."
                : "Sales target is {$progress}% achieved — still room to push.";
        }

        $signals[] = $tasks > 0
            ? "{$tasks} operational item(s) need attention (QC, stock, deliveries, or overdue invoices)."
            : 'No major operational alerts right now.';

        return implode(' ', $signals);
    }

    protected function metricLinks(?string $metric): array
    {
        return match ($metric) {
            'revenue_mtd', 'revenue_today', 'receivables', 'collections_mtd', 'collection_rate', 'overdue_invoices', 'profit_estimate_mtd', 'expenses_mtd', 'payroll_mtd' => [
                $this->linkFromRoute('admin.finance.index', 'Open Finance'),
                $this->linkFromRoute('admin.accounting.dashboard', 'Accounting dashboard'),
            ],
            'orders_mtd', 'orders_today', 'returns_mtd', 'pending_deliveries', 'sales_target' => [
                $this->linkFromRoute('admin.orders.index', 'Open Orders'),
            ],
            'active_agents' => [
                $this->linkFromRoute('admin.agents.index', 'Open Agents'),
            ],
            'production_today', 'pending_qc' => [
                $this->linkFromRoute('admin.production.index', 'Open Production'),
            ],
            'low_stock_count' => [
                $this->linkFromRoute('admin.inventory.low-stock', 'Low stock list'),
            ],
            'ops_tasks', 'business_summary', 'business_health' => [
                $this->linkFromRoute('admin.dashboard', 'Main dashboard'),
            ],
            default => [],
        };
    }

    protected function linkFromRoute(string $routeName, ?string $label = null): array
    {
        if (! app('router')->has($routeName)) {
            return [];
        }

        return [
            'label' => $label ?? 'Open',
            'url' => route($routeName),
        ];
    }

    protected function wrap(string $reply, array $links, ?array $suggestions = null): array
    {
        return [
            'reply' => $reply,
            'links' => array_values(array_filter($links)),
            'suggestions' => $suggestions ?? AssistantFaqCatalog::quickAsks(),
        ];
    }

    protected function timeGreeting(string $firstName, string $locale = 'en'): string
    {
        $hour = (int) now()->format('G');

        if ($locale === 'bn') {
            $salutation = match (true) {
                $hour < 12 => 'শুভ সকাল',
                $hour < 17 => 'শুভ দুপুর',
                default => 'শুভ সন্ধ্যা',
            };

            return "{$salutation}, {$firstName}!";
        }

        $salutation = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return "{$salutation}, {$firstName}!";
    }

    protected function quoteForToday(?string $locale = null): string
    {
        $index = (int) now()->format('z') % count($this->quotes);

        return $this->quotes[$index];
    }

    protected function money(float $amount, string $currency): string
    {
        return $currency . ' ' . number_format($amount, 0);
    }

    protected function number(float $value): string
    {
        return number_format($value, fmod($value, 1.0) === 0.0 ? 0 : 2);
    }
}
