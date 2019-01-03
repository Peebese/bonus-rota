<?php

namespace App\Helpers\ShiftReport;

use App\ShiftBreaks;
use App\Shifts;
use Carbon\Carbon;
use DateTime;

class ShiftReportHelper
{
    protected static function calculateSingleMannedHours(array $shifts) : bool
    {
        $formattedShifts = self::formatWithBreaks($shifts);

        $singleMannedHoursCollection = [];
        $singleHoursNonOverlapCollection = [];
        $recordOverlap = [];
        $recordNonOverlap = [];
        $overLap = false;

        $weekdaysTotalSingleHours = [];

       $shiftValueCount = self::getSoloShiftDays($formattedShifts);
       $soloShiftDurations = self::getDurationOfShifts($shiftValueCount);
       //echo '$soloShiftDurations';
       //var_dump($soloShiftDurations);
       $soloShiftDayTotalHours = self::calculateTotalHoursSoloShiftDay($soloShiftDurations);


        //dd($soloShiftDurations);

        foreach ($formattedShifts as $aShift) {

            $soloShiftDay = false;

            foreach ($formattedShifts as $bShift) {

                if ($bShift['has_break_shift'] || $aShift['has_break_shift']) {
                    continue;
                }

                $dateOne = date('d',strtotime($aShift['start_time']));
                $dateTwo = date('d',strtotime($bShift['start_time']));

                if (($dateOne === $dateTwo)
                    && ($aShift['first_name'] !== $bShift['first_name'])) {

                    if(
                        (isset($aShift['parent_id']) && isset($bShift['parent_id']))
                        && $aShift['timeOfDay'] !== $bShift['timeOfDay']){

                        continue;

                    }

                    $overLap = self::hasOverlap(
                        $aShift['start_time'],
                        $aShift['end_time'],
                        $bShift['start_time'],
                        $bShift['end_time']
                    );

                    if ($overLap === true
                        && !self::isOverlapRecorded($recordOverlap, $aShift, $bShift)) {

                        //echo ' $aShift '. $aShift['id'] . ' ' . $aShift['first_name'] . ' ' . $aShift['start_time'] . ' ' . $aShift['end_time']  . PHP_EOL;
                        //echo ' $bShift '. $bShift['id'] . ' ' . $bShift['first_name'] . ' ' . $bShift['start_time'] . ' ' . $bShift['end_time']  . PHP_EOL;

                        $singleMannedHoursCollection[] = self::calculateSingleMannedTime($aShift, $bShift); //array_merge($singleMannedHoursCollection, );
                        $recordOverlap[] = $aShift['id'] . ',' . $bShift['id'];
                    }

                    if ($overLap === false
                        && !isset($aShift['parent_id']) && !self::isOverlapRecorded($recordNonOverlap, $aShift, $bShift)) {

                        $singleHoursNonOverlapCollection[] = self::calculateSingleMannedTime($aShift, $bShift);
                        $recordNonOverlap[] = $aShift['id'] . ',' . $bShift['id'];
                    }
                }
            }
        }

        $dualShiftTotalHours = self::calculateTotalHours($singleMannedHoursCollection);
        $singleShiftTotalHours = self::calculateTotalHours($singleHoursNonOverlapCollection);


        dd($singleMannedHoursCollection);

        $weekdaysTotalSingleHours = self::createWeeklySingleHours($singleMannedHoursCollection,$weekdaysTotalSingleHours);
        $weekdaysTotalSingleHours = self::createWeeklySingleHours($singleHoursNonOverlapCollection,$weekdaysTotalSingleHours);
        $weekdaysTotalSingleHours = self::createWeeklySingleHours($soloShiftDurations,$weekdaysTotalSingleHours);

//        echo '$singleMannedHoursCollection';
//        var_dump($singleMannedHoursCollection);
//        echo '$singleHoursNonOverlapCollection';
//        var_dump($singleHoursNonOverlapCollection);
        dd($weekdaysTotalSingleHours);


        //dd(($singleShiftTotalHours + $dualShiftTotalHours + $soloShiftDayTotalHours));

        return $overLap;
    }

    private static function createWeeklySingleHours(array $singleHours, array $returnArray = []) : array
    {
        foreach ($singleHours as $shift) {

            foreach ($shift as $val) {


            if (!isset($returnArray[$val['shift_day']])) {
                $returnArray[$val['shift_day']]['single_manned_hours'] = $val['single_manned_hours'];
                continue;
            }

            $returnArray[$val['shift_day']]['single_manned_hours'] += $val['single_manned_hours'];
        }
    }
        return $returnArray;
    }


    private static function getSoloShiftDays(array $shifts) : array
    {
       $dayShiftCount = self::formatShiftsByDay($shifts);
       $dayShiftArr = [];

       foreach ($dayShiftCount as $dayShift) {

           if (count($dayShift) == 1) {
               $dayShiftArr = array_merge($dayShiftArr, $dayShift);
           }
       }

       return $dayShiftArr;
    }

    private static function formatShiftDay(string $dateTime)
    {
        return date('D',strtotime($dateTime));
    }

    private static function getDurationOfShifts(array $shifts) : array
    {
        $shiftDurations = [];

        foreach ($shifts as $shift) {
            $shiftDurations[] = [
                'start_diff' => [
                    'shift_id' => $shift['id'],
                    'shift_day' => self::formatShiftDay($shift['start_time']),
                    'single_manned_hours' => self::calculateShiftDuration($shift)
                ]
            ];
        }

        return $shiftDurations;
    }

    private static function calculateShiftDuration(array $shift) : int
    {
        $shiftStartTime = new Carbon($shift['start_time']);
        $shiftEndTime   = new Carbon($shift['end_time']);

         return $shiftStartTime->diff($shiftEndTime)->h;
    }

    private static function formatShiftsByDay(array $shifts) : array
    {
        $startTimeArr = [];

        foreach ($shifts as $shift) {

            $day = date('D', strtotime($shift['start_time']));
            $startTimeArr[$day][] = $shift;
        }

         return $startTimeArr;
    }

    private static function calculateTotalHoursSoloShiftDay(array $hoursArray)
    {
        return  array_reduce($hoursArray, function($carry, $item){
            return ($carry + $item['start_diff']['single_manned_hours']);
        });
    }

    private static function calculateTotalHours(array $hoursArray)
    {
        return  array_reduce($hoursArray, function($carry, $item){
            $startDiffHours = $item['start_diff']['single_manned_hours'];
            $endDiffHours   = $item['end_diff']['single_manned_hours'];
            return ($carry + $startDiffHours + $endDiffHours);
        });
    }

    private static function isOverlapRecorded(array $recordedOverlap, array $aShift, array $bShift) : bool
    {
        $aShiftId = $aShift['id'];
        $bShiftId = $bShift['id'];

        foreach ($recordedOverlap as $record) {

            $result = explode(',', $record);

            if (in_array($aShiftId ,$result) && in_array($bShiftId ,$result)) {
                return true;
            };
        }

        return false;
    }

    private static function isNonOverlapRecorded(array $recordNonOverlap, $aShift)
    {
        foreach ($recordNonOverlap as $record) {

            $result = explode(',', $record);

            if (in_array($aShift['id'] ,$result)) {
                return true;
            };
        }

        return false;
    }

    private static function calculateSingleMannedNonOverlap(array $aShift)
    {
        $aShiftStart    = new Carbon($aShift['start_time']);
        $aShiftEnd      = new Carbon($aShift['end_time']);

        $diff = $aShiftStart->diff($aShiftEnd);

        $array = [
            'shift_id' => $aShift['id'],
            'diff' => $diff->h
        ];

        return $array;
    }

    private static function calculateSingleMannedTime(array $aShift, array $bShift)
    {
        $aShiftStart    = new Carbon($aShift['start_time']);
        $aShiftEnd      = new Carbon($aShift['end_time']);
        $bShiftStart    = new Carbon($bShift['start_time']);
        $bShiftEnd      = new Carbon($bShift['end_time']);

        $startDiff = $aShiftStart->diff($bShiftStart);
        $endDiff = $aShiftEnd->diff($bShiftEnd);

        return [
            'start_diff' => self::singleHoursStart($aShiftStart,$bShiftStart,$startDiff, $aShift, $bShift),
            'end_diff'   => self::singleHoursEnd($aShiftStart,$bShiftStart,$endDiff, $aShift, $bShift)

        ];
    }

    private static function calculateDaySingleMannedHoursTotal(array $shiftHours) : array
    {
        $startTimeArr = [];

        foreach ($shiftHours as $shift) {

            $day = date('D', strtotime($shift['start_time']));

            if(!isset($startTimeArr[$day])){
                $startTimeArr[$day] = '';
            }

            $startTimeArr[$day][] = $shift;
        }

        return $startTimeArr;
    }

    private static function singleHoursStart(Carbon $aShiftStart, Carbon $bShiftStart, \DateInterval $startDiff, $aShift, $bShift)
    {
        $startTime = $aShiftStart->getTimestamp();
        $startTimeToCompare = $bShiftStart->addHours($startDiff->h)->getTimestamp();

        if ($startTime === $startTimeToCompare){

            return [
                'shift_id' => isset($bShift['parent_id']) ? $bShift['parent_id'] : $bShift['id'],
                'single_manned_hours' => $startDiff->h,
                'shift_day'  => self::formatShiftDay($aShift['start_time'])
            ];
        }

        return [
            'shift_id' => isset($aShift['parent_id']) ? $aShift['parent_id'] : $aShift['id'],
            'single_manned_hours' => $startDiff->h,
            'shift_day'  => self::formatShiftDay($aShift['start_time'])
        ];

    }

    private static function singleHoursEnd(Carbon $aShiftEnd, Carbon $bShiftEnd, \DateInterval $endDiff, $aShift, $bShift)
    {
        $endTime = $aShiftEnd->getTimestamp();
        $endTimeToCompare = $bShiftEnd->subHours($endDiff->h)->getTimestamp();

        if ($endTime === $endTimeToCompare){

            return [
                'shift_id' => isset($aShift['parent_id']) ? $aShift['parent_id'] : $aShift['id'],
                'single_manned_hours' => $endDiff->h,
                'shift_day'  => self::formatShiftDay($aShift['start_time'])
            ];
        }

        return [
            'shift_id' => isset($bShift['parent_id']) ? $bShift['parent_id'] : $bShift['id'],
            'single_manned_hours' => $endDiff->h,
            'shift_day'  => self::formatShiftDay($aShift['start_time'])
        ];
    }


    private static function hasOverlap(string $startTimeA, string $endTimeA, string $startTimeB, string $endTimeB) : bool
    {
        $startTimeADate = new DateTime($startTimeA);
        $endTimeADate   = new DateTime($endTimeA);
        $startTimeBDate = new DateTime($startTimeB);
        $endTimeBDate = new DateTime($endTimeB);

        //echo ' Times: '.$startTimeBDate->getTimestamp(). ' ' .$startTimeADate->getTimestamp().PHP_EOL;

        if (
            ($startTimeBDate->getTimestamp() >= $startTimeADate->getTimestamp())
            && ($endTimeADate->getTimestamp() > $startTimeBDate->getTimestamp())
        ){
            return true;
        }

        if (
            ($endTimeBDate->getTimestamp() <= $endTimeADate->getTimestamp())
            && ($startTimeADate->getTimestamp() < $endTimeBDate->getTimestamp())
        ) {
            return true;
        }

        return false;
    }

    public static function generateShiftMannedReport() : bool
    {
        $shiftArray = Shifts::getShifts();
        return self::calculateSingleMannedHours($shiftArray);
    }

    protected static function getShiftBreak(int $shiftId)
    {
        return ShiftBreaks::getShiftBreak($shiftId);
    }

    private static function formatWithBreaks(array $shifts) : array
    {
        $shiftBreakArray = [];

        foreach ($shifts as &$shift) {

            $shift['has_break_shift'] = 0;

            if (!is_null($shift['break_start_time'])) {

                $shiftBreakArray[] = [
                    'id' => $shift['id'].'-start',
                    'timeOfDay' => 'morning',
                    'parent_id' => $shift['id'],
                    'rota_id' => $shift['rota_id'],
                    'shop_id' => $shift['shop_id'],
                    'first_name' => $shift['first_name'],
                    'start_time' => $shift['start_time'],
                    'end_time'   => $shift['break_start_time'],
                    'has_break_shift' => 0
                    ];

                $shiftBreakArray[] = [
                    'id' => $shift['id'].'-end',
                    'timeOfDay' => 'evening',
                    'parent_id' => $shift['id'],
                    'rota_id' => $shift['rota_id'],
                    'shop_id' => $shift['shop_id'],
                    'first_name' => $shift['first_name'],
                    'start_time' => $shift['break_end_time'],
                    'end_time'   => $shift['end_time'],
                    'has_break_shift' => 0
                    ];

                $shift['has_break_shift'] = 1;
            }
        }

        return array_merge($shifts, $shiftBreakArray);
    }
}