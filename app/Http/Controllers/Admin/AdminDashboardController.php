<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // default hari_ini
        $filterchart = $request->query('filterchart', 'hari_ini');

        $query = DB::table('transaction')
        ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('SUM(total_harga) as total'))
        ->where('status', 'success');

        if ($filterchart === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } else if ($filterchart === 'month') {
            $query->whereMonth('created_at', Carbon::now()->month);
        } else if ($filterchart === 'year') {
            $query->whereYear('created_at', Carbon::now()->year);
        } else {
            $query->whereDate('created_at', Carbon::today());
        }

        $datapenjualan = $query->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();
        return view('admin.dashboard.index', ['datapenjualan' => $datapenjualan, 'filterchart' => $filterchart]);
    }
}
