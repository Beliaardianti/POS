<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Profit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Exports\ProfitsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfitController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        return Inertia::render('Apps/Profits/Index');
    }

    /**
     * filter
     *
     * @param  mixed $request
     * @return void
     */
    public function filter(Request $request)
    {
        $this->validate($request, [
            'start_date'  => 'required',
            'end_date'    => 'required',
        ]);

        // Get data profits by range date - EXCLUDE VOIDED TRANSACTIONS
        $profits = Profit::with(['transaction' => function ($query) {
            $query->where('status', '!=', 'voided');
        }])
            ->whereHas('transaction', function ($query) use ($request) {
                $query->where('status', '!=', 'voided')
                    ->whereDate('created_at', '>=', $request->start_date)
                    ->whereDate('created_at', '<=', $request->end_date);
            })
            ->get();

        // Get total profit by range date - EXCLUDE VOIDED
        $total = Profit::whereHas('transaction', function ($query) use ($request) {
            $query->where('status', '!=', 'voided')
                ->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
        })
            ->sum('total');

        return Inertia::render('Apps/Profits/Index', [
            'profits'   => $profits,
            'total'     => (int) $total
        ]);
    }

    /**
     * export
     *
     * @param  mixed $request
     * @return void
     */
    public function export(Request $request)
    {
        return Excel::download(
            new ProfitsExport($request->start_date, $request->end_date),
            'profits : ' . $request->start_date . ' — ' . $request->end_date . '.xlsx'
        );
    }

    /**
     * pdf
     *
     * @param  mixed $request
     * @return void
     */
    public function pdf(Request $request)
    {
        // Get data profits by range date - EXCLUDE VOIDED
        $profits = Profit::with(['transaction' => function ($query) {
            $query->where('status', '!=', 'voided');
        }])
            ->whereHas('transaction', function ($query) use ($request) {
                $query->where('status', '!=', 'voided')
                    ->whereDate('created_at', '>=', $request->start_date)
                    ->whereDate('created_at', '<=', $request->end_date);
            })
            ->get();

        // Get total profit by range date - EXCLUDE VOIDED
        $total = Profit::whereHas('transaction', function ($query) use ($request) {
            $query->where('status', '!=', 'voided')
                ->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
        })
            ->sum('total');

        // Load view PDF with data
        $pdf = PDF::loadView('exports.profits', compact('profits', 'total'));

        // Download PDF
        return $pdf->download('profits : ' . $request->start_date . ' — ' . $request->end_date . '.pdf');
    }
}
