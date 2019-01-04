<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateShiftReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shifts:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Shift report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $getShifts      = app()->make('shiftReporter');
        $weeklyReport   = $getShifts->generateShiftMannedReport();
        $response       = Response()->json($weeklyReport)->content();

        $this->info($response);
    }
}
