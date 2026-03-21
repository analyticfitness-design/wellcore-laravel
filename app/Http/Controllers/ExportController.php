<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export the full clients list to CSV.
     */
    public function clients(): StreamedResponse
    {
        return ExportService::exportClients();
    }

    /**
     * Export the full payments list to CSV.
     */
    public function payments(): StreamedResponse
    {
        return ExportService::exportPayments();
    }

    /**
     * Export the full check-ins list to CSV.
     */
    public function checkins(): StreamedResponse
    {
        return ExportService::exportCheckins();
    }
}
