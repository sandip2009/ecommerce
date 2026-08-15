<?php

namespace App\Core;

use Throwable;

class ExceptionHandler
{
    private string $logFile;

    public function __construct(string $logFile) {
        $this->logFile = $logFile;
    }

    public function register(): void {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
    }

    public function handleException(Throwable $exception): never {
        $this->log($exception);

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Internal server error.',
            'errors' => [],
        ]);
        exit;
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool {
        $exception = new \ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );

        $this->log($exception);
        return true;
    }

    private function log(Throwable $exception): void {
        $directory = dirname($this->logFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }

        $message = sprintf(
            "[%s] %s: %s in %s:%d%s",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            PHP_EOL
        );

        file_put_contents(
            $this->logFile,
            $message,
            FILE_APPEND | LOCK_EX
        );
    }
}