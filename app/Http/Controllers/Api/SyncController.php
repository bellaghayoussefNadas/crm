<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SyncController extends Controller
{
    public function syncEmails()
    {
        try {
            // Run the crm:sync-emails command
            Artisan::call('crm:sync-emails');
            $output = Artisan::output();
            
            return response()->json([
                'message' => 'Synchronisation terminée',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur de synchronisation : ' . $e->getMessage()
            ], 500);
        }
    }
}
