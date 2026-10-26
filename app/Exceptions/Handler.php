<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use PDOException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Graceful handling for Database Connection Refused (2002)
        $this->renderable(function (QueryException $e, $request) {
            if ($this->isConnectionRefused($e)) {
                return $this->handleDatabaseConnectionRefused($e, $request);
            }
        });

        $this->renderable(function (PDOException $e, $request) {
            if ($this->isConnectionRefused($e)) {
                return $this->handleDatabaseConnectionRefused($e, $request);
            }
        });
    }

    /**
     * Check if exception is SQLSTATE 2002 (Connection Refused).
     */
    private function isConnectionRefused(Throwable $e): bool
    {
        $msg = $e->getMessage();
        return $e->getCode() === 2002 ||
               str_contains($msg, '2002') ||
               str_contains($msg, 'actively refused') ||
               str_contains($msg, 'Connection refused');
    }

    /**
     * Render friendly response when database connection is refused.
     */
    private function handleDatabaseConnectionRefused(Throwable $e, $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ฐานข้อมูลได้ในขณะนี้ (Database Connection Refused)',
                'error_code' => 2002,
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 503);
        }

        return response()->view('errors.database', [
            'exception' => $e,
            'message' => $e->getMessage(),
        ], 503);
    }
}
