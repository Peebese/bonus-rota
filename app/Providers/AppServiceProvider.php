<?php

namespace App\Providers;

use App\Helpers\CalculatorHelper\CalculatorHelper;
use App\Helpers\Formatter\FormatterHelper;
use App\Helpers\ShiftReport\ShiftReportHelper;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerShiftReportService();
    }

    public function registerShiftReportService()
    {
        $this->app->bind('shiftReporter', function() {
            return  new ShiftReportHelper(New FormatterHelper, New CalculatorHelper);
        });
    }
}
