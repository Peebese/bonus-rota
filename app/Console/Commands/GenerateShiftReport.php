<?php

namespace App\Console\Commands;

use App\Helpers\ShiftReport\ShiftReportHelper;
use App\Shifts;
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
        $getShifts = new ShiftReportHelper();
        $getShifts->generateShiftMannedReport();

        //$this->info('lol');
    }
}
