<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminViewController extends Controller
{
    /**
     * Admin dashboard overview.
     */
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    /**
     * Manage categories page.
     */
    public function categories(): View
    {
        return view('admin.categories');
    }

    /**
     * Manage menus page.
     */
    public function menus(): View
    {
        return view('admin.menus');
    }

    /**
     * Manage staff members page.
     */
    public function staff(): View
    {
        return view('admin.staff');
    }

    /**
     * Manage customers page.
     */
    public function customers(): View
    {
        return view('admin.customers');
    }

    /**
     * Financial reports page.
     */
    public function reports(): View
    {
        return view('admin.reports');
    }
}
