<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TravelPackage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index(Request $req)
    {
        $travel_package = TravelPackage::count();
        $transaction = Transaction::count();
        $transaction_pending = Transaction::where('transactional_status', 'PENDING')->count();
        $transaction_success = Transaction::where('transactional_status', 'SUCCESS')->count();
        return view('pages.admin.dashboard', compact('travel_package', 'transaction', 'transaction_pending', 'transaction_success'));
    }
}
