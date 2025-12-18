<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Request;
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
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Nếu là request từ Filament admin panel
        if ($request->is('admin/*')) {
            
            // Xử lý lỗi không có quyền (403)
            if ($exception instanceof AuthorizationException || $exception instanceof AccessDeniedHttpException) {
                return response()->view('errors.403', [
                    'message' => 'Bạn không có quyền truy cập tài nguyên này.',
                ], 403);
            }

            // Xử lý lỗi không tìm thấy (404) 
            if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                // Kiểm tra nếu user có quyền hạn bị giới hạn
                $user = auth()->user();
                if ($user && !$user->isSuperAdmin()) {
                    return response()->view('errors.403', [
                        'message' => 'Bạn không có quyền truy cập tài nguyên này.',
                    ], 403);
                }
                
                return response()->view('errors.404', [
                    'message' => 'Trang hoặc tài nguyên không tồn tại.',
                ], 404);
            }
        }

        return parent::render($request, $exception);
    }
}