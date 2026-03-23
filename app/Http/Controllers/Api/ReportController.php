<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Ticket;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dashboard()
    {
        $data = [
            // Counts
            'contacts_count'  => Contact::count(),
            'leads_count'     => Lead::count(),
            'deals_count'     => Deal::count(),
            'accounts_count'  => Account::count(),
            'tickets_count'   => Ticket::where('status', 'open')->count(),
            'tasks_pending'   => Task::where('status', 'pending')->count(),

            // Revenue
            'revenue_total'   => Invoice::where('status', 'paid')->sum('total'),
            'revenue_pending' => Invoice::whereIn('status', ['sent', 'draft'])->sum('total'),
            'quotes_total'    => Quote::count(),

            // Deals pipeline by stage
            'deals_by_stage'  => Deal::selectRaw('stage, count(*) as count, sum(amount) as value')
                                     ->groupBy('stage')->get(),

            // Leads by status
            'leads_by_status' => Lead::selectRaw('status, count(*) as count')
                                     ->groupBy('status')->get(),

            // Conversion rate (won / total deals)
            'deals_won'       => Deal::where('stage', 'won')->count(),
            'deals_lost'      => Deal::where('stage', 'lost')->count(),

            // Tickets by priority
            'tickets_by_priority' => Ticket::selectRaw('priority, count(*) as count')
                                           ->groupBy('priority')->get(),

            // Monthly revenue (last 6 months)
            'monthly_revenue' => Invoice::where('status', 'paid')
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            // Top accounts by deal value
            'top_accounts'    => Account::withSum('deals', 'amount')
                ->orderByDesc('deals_sum_amount')
                ->limit(5)
                ->get(['id', 'name']),

            // Overdue invoices
            'overdue_invoices' => Invoice::where('status', 'sent')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return response()->json($data);
    }

    public function salesForecast()
    {
        $forecast = Deal::whereNotIn('stage', ['won', 'lost'])
            ->selectRaw('stage, SUM(amount * probability / 100) as weighted_value, SUM(amount) as total_value, COUNT(*) as count')
            ->groupBy('stage')
            ->get();

        return response()->json($forecast);
    }
}
