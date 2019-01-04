<?php
namespace App\Helpers\CalculatorHelper;

use App\Helpers\ShiftReport\ShiftReportHelper;
use Carbon\Carbon;

class CalculatorHelper
{
    /**
     * Calculates durations of a shift
     *
     * @param array $shift
     *
     * @return int
     */
    public function calculateShiftDuration(array $shift) : int
    {
        $shiftStartTime = new Carbon($shift['start_time']);
        $shiftEndTime   = new Carbon($shift['end_time']);

        return $shiftStartTime->diff($shiftEndTime)->h;
    }

    /**
     * Calculate
     * @param array $hoursArray
     * @return mixed
     */
    public function calculateTotalHoursSoloShiftDay(array $hoursArray)
    {
        return  array_reduce($hoursArray, function($carry, $item){
            return ($carry + $item['single_manned_hours']);
        });
    }

    /**
     * @param array $hoursArray
     * @return mixed
     */
    public static function calculateTotalHours(array $hoursArray)
    {
        return  array_reduce($hoursArray, function($carry, $item){
            $startDiffHours = $item['start_diff']['single_manned_hours'];
            $endDiffHours   = $item['end_diff']['single_manned_hours'];
            return ($carry + $startDiffHours + $endDiffHours);
        });
    }

    /**
     * @param array $aShift
     * @param array $bShift
     * @return array
     */
    public static function calculateSingleMannedTime(array $aShift, array $bShift)
    {
        $aShiftStart    = new Carbon($aShift['start_time']);
        $aShiftEnd      = new Carbon($aShift['end_time']);
        $bShiftStart    = new Carbon($bShift['start_time']);
        $bShiftEnd      = new Carbon($bShift['end_time']);

        $startDiff = $aShiftStart->diff($bShiftStart);
        $endDiff = $aShiftEnd->diff($bShiftEnd);

        return [
            'start_diff' => ShiftReportHelper::getSingleHours($aShiftStart,$bShiftStart,$startDiff, $aShift, $bShift),
            'end_diff'   => ShiftReportHelper::getSingleHours($aShiftStart,$bShiftStart,$endDiff, $aShift, $bShift)

        ];
    }
}