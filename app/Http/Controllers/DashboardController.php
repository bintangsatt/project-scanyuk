<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArProject;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = ArProject::with(['marker', 'template'])
            ->latest()
            ->paginate(12);

        return view('dashboard', compact('projects'));
    }
}
