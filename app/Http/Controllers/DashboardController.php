<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        //  Use Auth::user() instead of custom method
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get user's accessible stations
        $stations = DB::table('user_stations')
            ->join('stations', 'user_stations.station_id', '=', 'stations.id')
            ->where('user_stations.user_id', $user->id)
            ->where('user_stations.is_active', 1)
            ->select('stations.*', 'user_stations.assigned_role')
            ->get();

        // Get recent variances for CEO/managers
        $recentVariances = collect();
        if (in_array($user->role, ['CEO', 'STATION_MANAGER'])) {
            $recentVariances = DB::table('variances')
                ->join('readings', 'variances.reading_id', '=', 'readings.id')
                ->join('tanks', 'readings.tank_id', '=', 'tanks.id')
                ->join('stations', 'tanks.station_id', '=', 'stations.id')
                ->where('variances.variance_status', 'PENDING')
                ->select([
                    'variances.*',
                    'stations.station_name',
                    'tanks.tank_number',
                    'readings.reading_date'
                ])
                ->orderBy('variances.created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('dashboard', compact('user', 'stations', 'recentVariances'));
    }
}
