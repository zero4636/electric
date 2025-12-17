<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected Request $request
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        activity()
            ->withProperties([
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'guard' => $event->guard,
                'credentials' => [
                    'email' => $event->credentials['email'] ?? 'unknown',
                ],
            ])
            ->log('Đăng nhập thất bại');
    }
}
