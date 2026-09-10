<?php

namespace App\Http\Controllers;

use App\Models\SystemUpdate;
use App\Services\SystemUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class SystemUpdateController extends Controller
{
    public function __construct(
        protected SystemUpdateService $updateService
    ) {}

    /**
     * Display the System Update Dashboard
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50])) {
            $perPage = 10;
        }

        // Get git update status
        $gitStatus = $this->updateService->checkRemoteUpdates();

        // Get historical updates
        $updates = SystemUpdate::with('triggeredBy')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // System environment info
        $envInfo = [
            'app_version' => function_exists('app_version') ? app_version() : config('version.version', '2.2.2'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env', 'production'),
            'os' => PHP_OS_FAMILY,
            'disk_free' => disk_free_space(base_path()) ? $this->formatBytes(disk_free_space(base_path())) : 'ไม่ระบุ',
            'target_repo' => config('version.update_repo_url', 'https://github.com/monojung/it-system-deploy.git'),
            'target_branch' => config('version.update_branch', 'main'),
        ];

        return view('system_updates.index', compact('gitStatus', 'updates', 'envInfo'));
    }

    /**
     * AJAX endpoint to check for remote updates
     */
    public function check(): JsonResponse
    {
        $gitStatus = $this->updateService->checkRemoteUpdates();
        return response()->json($gitStatus);
    }

    /**
     * Execute system update pipeline
     */
    public function apply(Request $request): JsonResponse|RedirectResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '256M');

        try {
            $record = $this->updateService->executeUpdate(
                userId: Auth::id()
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'อัปเดตระบบเสร็จสมบูรณ์เรียบร้อยแล้ว!',
                    'record' => [
                        'id' => $record->id,
                        'version' => $record->version,
                        'commit' => $record->short_commit,
                        'previous_commit' => $record->short_previous_commit,
                        'backup_file' => $record->backup_file,
                        'duration' => $record->formatted_duration,
                        'status' => $record->status,
                        'log' => $record->output_log,
                    ],
                ]);
            }

            return redirect()->route('system-updates.index')->with('success', "อัปเดตระบบเสร็จสิ้นแล้ว! (Commit: {$record->short_commit})");

        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการอัปเดตระบบ: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('system-updates.index')->with('error', 'เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
        }
    }

    /**
     * View log for specific update record
     */
    public function log(int $id): JsonResponse
    {
        $record = SystemUpdate::with('triggeredBy')->findOrFail($id);

        return response()->json([
            'id' => $record->id,
            'version' => $record->version,
            'commit' => $record->short_commit,
            'previous_commit' => $record->short_previous_commit,
            'status' => $record->status,
            'status_badge' => $record->status_badge,
            'backup_file' => $record->backup_file,
            'duration' => $record->formatted_duration,
            'started_at' => $record->started_at ? $record->started_at->format('d/m/Y H:i:s') : '-',
            'completed_at' => $record->completed_at ? $record->completed_at->format('d/m/Y H:i:s') : '-',
            'triggered_by' => $record->triggeredBy ? ($record->triggeredBy->name ?? $record->triggeredBy->username) : 'ระบบอัตโนมัติ / CLI',
            'output_log' => $record->output_log ?: 'ไม่มีบันทึกข้อความ',
            'error_message' => $record->error_message,
        ]);
    }

    /**
     * Upload and apply patch ZIP file
     */
    public function uploadPatch(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'patch_file' => 'required|file|mimes:zip|max:102400',
        ], [
            'patch_file.required' => 'กรุณาเลือกไฟล์ ZIP ที่ต้องการอัปเดต',
            'patch_file.mimes' => 'รูปแบบไฟล์ต้องเป็น .zip เท่านั้น',
            'patch_file.max' => 'ขนาดไฟล์ต้องไม่เกิน 100 MB',
        ]);

        try {
            @set_time_limit(300);
            @ini_set('max_execution_time', '300');
            @ini_set('memory_limit', '256M');

            $file = $request->file('patch_file');
            $record = $this->updateService->applyPatchZip(
                zipPath: $file->getRealPath(),
                userId: Auth::id()
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'อัปเดตระบบด้วยไฟล์แพตช์ ZIP เรียบร้อยแล้ว!',
                    'record' => [
                        'id' => $record->id,
                        'version' => $record->version,
                        'commit' => $record->short_commit,
                        'backup_file' => $record->backup_file,
                        'duration' => $record->formatted_duration,
                        'status' => $record->status,
                        'log' => $record->output_log,
                    ],
                ]);
            }

            return redirect()->route('system-updates.index')->with('success', 'อัปเดตระบบด้วยไฟล์แพตช์ ZIP เรียบร้อยแล้ว!');

        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการติดตั้งแพตช์: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('system-updates.index')->with('error', 'เกิดข้อผิดพลาดในการติดตั้งแพตช์: ' . $e->getMessage());
        }
    }

    /**
     * Helper to format bytes to human readable string
     */
    protected function formatBytes(float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
