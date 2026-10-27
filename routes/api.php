<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HardwareAuditApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Hardware Audit REST API Suite
Route::prefix('hardware-audits')->group(function () {
    Route::get('/', [HardwareAuditApiController::class, 'index'])->name('api.hardware-audits.index');
    Route::post('/', [HardwareAuditApiController::class, 'submit'])->name('api.hardware-audits.store');
    Route::get('/stats', [HardwareAuditApiController::class, 'stats'])->name('api.hardware-audits.stats');
    Route::get('/schema', [HardwareAuditApiController::class, 'schema'])->name('api.hardware-audits.schema');
    Route::post('/batch-approve', [HardwareAuditApiController::class, 'batchApprove'])->name('api.hardware-audits.batch-approve');
    Route::post('/batch-delete', [HardwareAuditApiController::class, 'batchDelete'])->name('api.hardware-audits.batch-delete');
    Route::post('/create-asset', [HardwareAuditApiController::class, 'createAsset'])->name('api.hardware-audits.create-asset.alias');
    Route::match(['get', 'post'], '/command-poll', [\App\Http\Controllers\HardwareAuditController::class, 'pollCommand'])->name('api.hardware-audits.command-poll');
    Route::post('/command-complete/{id}', [\App\Http\Controllers\HardwareAuditController::class, 'completeCommand'])->name('api.hardware-audits.command-complete')->where('id', '[0-9]+');

    Route::get('/{id}', [HardwareAuditApiController::class, 'show'])->name('api.hardware-audits.show')->where('id', '[0-9]+');
    Route::put('/{id}', [HardwareAuditApiController::class, 'update'])->name('api.hardware-audits.update')->where('id', '[0-9]+');
    Route::patch('/{id}', [HardwareAuditApiController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/{id}', [HardwareAuditApiController::class, 'destroy'])->name('api.hardware-audits.destroy')->where('id', '[0-9]+');
    Route::post('/{id}/approve', [HardwareAuditApiController::class, 'approve'])->name('api.hardware-audits.approve')->where('id', '[0-9]+');
    Route::post('/{id}/reject', [HardwareAuditApiController::class, 'reject'])->name('api.hardware-audits.reject')->where('id', '[0-9]+');
    Route::post('/{id}/link-asset', [HardwareAuditApiController::class, 'linkAsset'])->name('api.hardware-audits.link-asset')->where('id', '[0-9]+');
    Route::post('/{id}/unlink-asset', [HardwareAuditApiController::class, 'unlinkAsset'])->name('api.hardware-audits.unlink-asset')->where('id', '[0-9]+');
    Route::post('/{id}/create-asset', [HardwareAuditApiController::class, 'createAsset'])->name('api.hardware-audits.create-asset')->where('id', '[0-9]+');
});

// Backward compatibility alias endpoints
Route::match(['get', 'post'], '/hardware-audit/submit', [HardwareAuditApiController::class, 'submit'])->name('api.hardware-audit.submit');
