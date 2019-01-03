<?php

namespace App\Helpers\ShiftReport;

use App\ShiftBreaks;
use App\Shifts;
use Carbon\Carbon;
use DateTime;

class ShiftReportHelper
{
    /**
     * @var array
     */
    protected $singleMannedHoursCollection = [];

    /**
     * @var array
     */
    protected $singleHoursNonOverlapCollection = [];

    /**
     * @var array
     */
    protected $soloShiftDurations = [];

    /**
     * @param array $shifts
     *
     * @return array
     */
    protected function formatSolodayShiftDurations(array $shifts) : array
    {
        $daySoloShifts = $this->getDaysSoloShifts($shifts);
        return $this->getDurationOfShifts($daySoloShifts);
    }

    /**
     * @param array $shifts
     *
     * @return bool
     */
    protected function calculateSingleMannedHours(array $shifts) : bool
    {
        $recordOverlap = [];
        $recordNonOverlap = [];
        $overLap = false;

        foreach ($shifts as $aShift) {

            foreach ($shifts as $bShift) {

                if ($bShift['has_break_shift'] || $aShift['has_break_shift']) {
                    continue;
                }

                $dateOne = self::formatShiftDay($aShift['start_time']);
                $dateTwo = self::formatShiftDay($bShift['start_time']);

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
                        && !$this->isOverlapRecorded($recordOverlap, $aShift, $bShift)) {

                        $this->singleMannedHoursCollection[] = self::calculateSingleMannedTime($aShift, $bShift);
                        $recordOverlap[] = $aShift['id'] . ',' . $bShift['id'];
                    }

                    if ($overLap === false
                        && !isset($aShift['parent_id']) && !$this->isOverlapRecorded($recordNonOverlap, $aShift, $bShift)) {

                        $this->singleHoursNonOverlapCollection[] = self::calculateSingleMannedTime($aShift, $bShift);
                        $recordNonOverlap[] = $aShift['id'] . ',' . $bShift['id'];
                    }
                }
            }
        }

        $weekdaysTotalSingleHours = [];
        $this->soloShiftDurations = $this->formatSolodayShiftDurations($shifts);
        $soloShiftDayTotalHours = $this->calculateTotalHoursSoloShiftDay($this->soloShiftDurations);

        $dualShiftTotalHours = self::calculateTotalHours($this->singleMannedHoursCollection);
        $singleShiftTotalHours = self::calculateTotalHours($this->singleHoursNonOverlapCollection);

        $weekdaysTotalSingleHours = $this->createWeeklySingleHours($this->singleMannedHoursCollection,$weekdaysTotalSingleHours);
        $weekdaysTotalSingleHours = $this->createWeeklySingleHours($this->singleHoursNonOverlapCollection,$weekdaysTotalSingleHours);
        $weekdaysTotalSingleHours = $this->createWeeklySingleHours($this->soloShiftDurations,$weekdaysTotalSingleHours);

        dd($weekdaysTotalSingleHours);


        //dd(($singleShiftTotalHours + $dualShiftTotalHours + $soloShiftDayTotalHours));

        return $overLap;
    }

    private function createWeeklySingleHours(array $singleHours, array $returnArray = []) : array
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


    private function getDaysSoloShifts(array $shifts) : array
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

    /**
     * Calculates single manned hours of a shift
     *
     * @param array $shifts
     *
     * @return array
     */
    private function getDurationOfShifts(array $shifts) : array
    {
        $shiftDurations = [];

        foreach ($shifts as $shift) {
            $shiftDurations[] = [
                'start_diff' => [
                    'shift_id' => $shift['id'],
                    'shift_day' => self::formatShiftDay($shift['start_time']),
                    'single_manned_hours' => $this->calculateShiftDuration($shift)
                ]
            ];
        }
        return $shiftDurations;
    }

    /**
     * Calculates durations of a shift
     *
     * @param array $shift
     *
     * @return int
     */
    private function calculateShiftDuration(array $shift) : int
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

    private function calculateTotalHoursSoloShiftDay(array $hoursArray)
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

    private function isOverlapRecorded(array $recordedOverlap, array $aShift, array $bShift) : bool
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

    public function generateShiftMannedReport() : bool
    {
        $shiftArray      = Shifts::getShifts();
        $formattedShifts = $this->formatWithBreaks($shiftArray);
        return $this->calculateSingleMannedHours($formattedShifts);
    }

    protected static function getShiftBreak(int $shiftId)
    {
        return ShiftBreaks::getShiftBreak($shiftId);
    }

    /**
     * Divides shifts with breaks into individual shifts
     *
     * @param array $shifts
     *
     * @return array
     */
    private function formatWithBreaks(array $shifts) : array
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