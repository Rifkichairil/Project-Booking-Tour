<?php

namespace App\Http\Controllers;

use App\Models\TravelPackage;
use Illuminate\Http\Request;

class DetailController extends Controller
{
    //
    public function index(Request $req, $slug)
    {
        $item = TravelPackage::with('galleries')->where('slug', $slug)->firstOrFail();
        return view('pages.detail', compact('item'));
    }
}
