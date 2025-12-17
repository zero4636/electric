<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogSuccessfulLogout
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
    public function handle(Logout $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties([
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'guard' => $event->guard,
            ])
            ->log('Đăng xuất');
    }
}
