<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class KasirViewController extends Controller
{
    /**
     * Kasir dashboard overview.
     */
    public function dashboard(): View
    {
        return view('kasir.dashboard');
    }

    /**
     * Order queue management page.
     */
    public function orders(): View
    {
        return view('kasir.orders');
    }

    /**
     * Customer lookup and registration page.
     */
    public function customers(): View
    {
        return view('kasir.customers');
    }
}
