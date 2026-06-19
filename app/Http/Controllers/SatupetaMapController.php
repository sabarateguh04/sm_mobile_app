<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class SatupetaMapController extends Controller
{
    /**
     * Redirect ke halaman SatuPeta Laravel (PemetaanNew).
     */
    public function index(): RedirectResponse
    {
        return Redirect::route('satupeta.pemetaan_new');
    }
}
