<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class WebController extends Controller
{
    public function dashboard(Request $request): Response
    {
        return Inertia::render('Dashboard');
    }

    public function welcome(Request $request): Response
    {
        return Inertia::render('Welcome');
    }
} 