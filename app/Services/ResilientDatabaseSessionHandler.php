<?php

namespace App\Services;

use SessionHandlerInterface;
use Illuminate\Session\ExistenceAwareInterface;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Resilient Database Session Handler
 * 
 * Provides seamless fallback to FileSessionHandler whenever
 * database connection fails, is refused, or becomes unavailable.
 * Prevents fatal 500 crashes during StartSession middleware.
 */
class ResilientDatabaseSessionHandler implements SessionHandlerInterface, ExistenceAwareInterface
{
    protected DatabaseSessionHandler $dbHandler;
    protected FileSessionHandler $fileHandler;
    protected bool $useFallback = false;

    public function __construct(DatabaseSessionHandler $dbHandler, FileSessionHandler $fileHandler)
    {
        $this->dbHandler = $dbHandler;
        $this->fileHandler = $fileHandler;
    }

    public function open(string $path, string $name): bool
    {
        try {
            return $this->dbHandler->open($path, $name);
        } catch (Throwable $e) {
            $this->useFallback = true;
            return $this->fileHandler->open($path, $name);
        }
    }

    public function close(): bool
    {
        if ($this->useFallback) {
            return $this->fileHandler->close();
        }
        try {
            return $this->dbHandler->close();
        } catch (Throwable $e) {
            return $this->fileHandler->close();
        }
    }

    public function read(string $id): string|false
    {
        if ($this->useFallback) {
            return $this->fileHandler->read($id);
        }
        try {
            return $this->dbHandler->read($id);
        } catch (Throwable $e) {
            Log::warning("Database session read failed (falling back to file session): " . $e->getMessage());
            $this->useFallback = true;
            return $this->fileHandler->read($id);
        }
    }

    public function write(string $id, string $data): bool
    {
        if ($this->useFallback) {
            return $this->fileHandler->write($id, $data);
        }
        try {
            return $this->dbHandler->write($id, $data);
        } catch (Throwable $e) {
            Log::warning("Database session write failed (falling back to file session): " . $e->getMessage());
            $this->useFallback = true;
            return $this->fileHandler->write($id, $data);
        }
    }

    public function destroy(string $id): bool
    {
        if ($this->useFallback) {
            return $this->fileHandler->destroy($id);
        }
        try {
            return $this->dbHandler->destroy($id);
        } catch (Throwable $e) {
            return $this->fileHandler->destroy($id);
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        if ($this->useFallback) {
            return $this->fileHandler->gc($max_lifetime);
        }
        try {
            return $this->dbHandler->gc($max_lifetime);
        } catch (Throwable $e) {
            return $this->fileHandler->gc($max_lifetime);
        }
    }

    public function setExists($value)
    {
        $this->dbHandler->setExists($value);
        return $this;
    }
}
