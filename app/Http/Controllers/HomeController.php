<?php

namespace App\Http\Controllers;

use App\Models\TravelPackage;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index(Request $req)
    {
        $items = TravelPackage::with('galleries')->take(4)->get();
        return view('pages.home', compact('items'));
    }
}
