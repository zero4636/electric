<?php

namespace App\Providers;

use App\Models\OrganizationUnit;
use App\Models\ElectricMeter;
use App\Models\MeterReading;
use App\Models\Bill;
use App\Policies\OrganizationUnitPolicy;
use App\Policies\ElectricMeterPolicy;
use App\Policies\MeterReadingPolicy;
use App\Policies\BillPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        OrganizationUnit::class => OrganizationUnitPolicy::class,
        ElectricMeter::class => ElectricMeterPolicy::class,
        MeterReading::class => MeterReadingPolicy::class,
        Bill::class => BillPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}