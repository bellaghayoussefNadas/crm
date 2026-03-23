<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SyncController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Import / Export (Must be before resources to avoid route name collision)
    Route::post('/contacts/import', [ContactController::class, 'import']);
    Route::get('/contacts/export',  [ContactController::class, 'export']);
    Route::post('/leads/import',    [LeadController::class, 'import']);
    Route::get('/leads/export',     [LeadController::class, 'export']);

    // Core CRM
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('contacts', ContactController::class);
    Route::apiResource('deals', DealController::class);
    Route::apiResource('leads', LeadController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('activities', ActivityController::class);
    Route::apiResource('users', UserController::class);

    // Finance
    Route::post('quotes/{quote}/convert-to-invoice', [QuoteController::class, 'convertToInvoice']);
    Route::apiResource('quotes', QuoteController::class);
    Route::apiResource('invoices', InvoiceController::class);

    // Support
    Route::apiResource('tickets', TicketController::class);

    // Documents
    Route::apiResource('documents', DocumentController::class);
    Route::post('documents/folders', [DocumentController::class, 'storeFolder']);
    Route::patch('documents/{document}/move', [DocumentController::class, 'move']);

    // Marketing
    Route::apiResource('campaigns', CampaignController::class);

    // Reports & KPIs
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/reports/forecast',  [ReportController::class, 'salesForecast']);

    // Marketing Actions
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send']);
    // Settings
    Route::get('/settings',     [SettingController::class, 'get']);
    Route::post('/settings',    [SettingController::class, 'update']);
    // Sync
    Route::post('/sync/emails', [SyncController::class, 'syncEmails']);
});
