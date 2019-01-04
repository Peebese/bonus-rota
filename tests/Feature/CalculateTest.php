<?php


namespace Tests\Feature;

use App\Helpers\CalculatorHelper\CalculatorHelper;
use Tests\TestCase;

class CalculateTest extends TestCase
{
    public $calculator;

    /**
     * Initial step
     */
    public function setUp()
    {
        $this->calculator   = new CalculatorHelper();
    }

    /**
     * @return array
     */
    private function getHoursArray()
    {
        return [
            "start_diff" => [
                "shift_id" => 5,
                "single_manned_hours" => 2,
                "shift_day" => "Thu"
                ],
            "end_diff" => [
                "shift_id" => 6,
                "single_manned_hours" => 7,
                "shift_day" => "Thu"
                ],
            ];
    }

    /**
     * Test calculation of shift duration
     */
    public function testCalculateShiftDuration()
    {
        $shiftArray = [
            'start_time' => '2019-01-09 09:00:00',
            'end_time'   => '2019-01-09 15:00:00'

        ];
        $shiftDuration = $this->calculator->calculateShiftDuration($shiftArray);

        $this->assertEquals(6,$shiftDuration);
        $this->assertTrue(gettype($shiftDuration) === 'integer');
    }

    /**
     * tests calculation of total hours
     */
    public function testCalculateTotalHours()
    {
        $hours = $this->getHoursArray();
        $totalHours = $this->calculator->calculateTotalHoursSoloShiftDay($hours);
        $this->assertEquals(9, $totalHours);
    }


}