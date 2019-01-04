<?php

namespace App\Helpers\ShiftReport;

use App\Helpers\CalculatorHelper\CalculatorHelper;
use App\Helpers\Formatter\FormatterHelper;
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
    protected $soloShiftHoursCollection = [];

    /**
     * @var array
     */
    protected $recordOverlap = [];

    /**
     * @var CalculatorHelper
     */
    public $calculator;

    /**
     * @var FormatterHelper
     */
    protected $formatter;

    /**
     * @var array
     */
    public $weekdaysTotalSingleHours = [];


    public function __construct(
        FormatterHelper $formatter,
        CalculatorHelper $calculatorHelper
    )
    {
        $this->formatter = $formatter;
        $this->calculator = $calculatorHelper;
    }

    /**
     * @param array $shifts
     *
     * @return array
     */
    protected function formatSoloDayShiftDurations(array $shifts) : array
    {
        $daySoloShifts = $this->getDaysSoloShifts($shifts);
        return $this->getDurationOfShifts($daySoloShifts);
    }

    /**
     * @param array $shifts
     */
    protected function calculateSingleMannedHours(array $shifts)
    {
        foreach ($shifts as $aShift) {

            foreach ($shifts as $bShift) {

                if ($bShift['has_break_shift'] || $aShift['has_break_shift']) {
                    continue;
                }

                $dateOne = FormatterHelper::formatShiftDay($aShift['start_time']);
                $dateTwo = FormatterHelper::formatShiftDay($bShift['start_time']);

                if (($dateOne === $dateTwo)
                    && ($aShift['first_name'] !== $bShift['first_name'])) {

                    if (
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

                    if ($overLap === true && !$this->isOverlapRecorded($aShift, $bShift)) {
                        $this->singleMannedHoursCollection[] = $this->calculator->calculateSingleMannedTime($aShift, $bShift);
                    }

                    if ($overLap === false
                        && !isset($aShift['parent_id']) && !$this->isOverlapRecorded($aShift, $bShift)) {

                        $this->singleMannedHoursCollection[] = $this->calculator->calculateSingleMannedTime($aShift, $bShift);
                    }
                }
            }
        }

        $this->soloShiftHoursCollection = $this->formatSoloDayShiftDurations($shifts);

        $this->addToWeeklySingleHours($this->soloShiftHoursCollection);
        $this->addToWeeklySingleHours($this->singleMannedHoursCollection);


        return $this->weekdaysTotalSingleHours;
    }

    public function getTotalSingleMannedHours(array $shifts) : int
    {
        $formattedSingleShifts  = $this->formatSoloDayShiftDurations($shifts);
        $soloShiftDayTotalHours = $this->calculator->calculateTotalHoursSoloShiftDay($formattedSingleShifts);
        $dualShiftTotalHours    = $this->calculator->calculateTotalHours($this->singleMannedHoursCollection);

        return ($soloShiftDayTotalHours + $dualShiftTotalHours);
    }

    /**
     * @param array $singleHours
     * @return void
     */
    private function addToWeeklySingleHours(array $singleHours) : void
    {
        foreach ($singleHours as $shift) {

            foreach ($shift as $val) {

                if (!isset($this->weekdaysTotalSingleHours[$val['shift_day']])) {
                    $this->weekdaysTotalSingleHours[$val['shift_day']]['single_manned_hours'] = $val['single_manned_hours'];
                    continue;
                }

                $this->weekdaysTotalSingleHours[$val['shift_day']]['single_manned_hours'] += $val['single_manned_hours'];
            }
        }
    }

    /**
     * @param array $shifts
     * @return array
     */
    private function getDaysSoloShifts(array $shifts) : array
    {
       $dayShiftCount = FormatterHelper::formatShiftsByDay($shifts);
       $dayShiftArr = [];

       foreach ($dayShiftCount as $dayShift) {

           if (count($dayShift) == 1) {
               $dayShiftArr = array_merge($dayShiftArr, $dayShift);
           }
       }

       return $dayShiftArr;
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
                    'shift_day' => FormatterHelper::formatShiftDay($shift['start_time']),
                    'single_manned_hours' => $this->calculator->calculateShiftDuration($shift)
                ]
            ];
        }

        return $shiftDurations;
    }

    /**
     * @param array $aShift
     * @param array $bShift
     * @return bool
     */
    private function isOverlapRecorded(array $aShift, array $bShift) : bool
    {
        $aShiftId = $aShift['id'];
        $bShiftId = $bShift['id'];
        $isRecorded = false;

        foreach ($this->recordOverlap as $record) {

            $result = explode(',', $record);

            if (in_array($aShiftId ,$result) && in_array($bShiftId ,$result)) {

                $isRecorded = true;
            };
        }

        if (!$isRecorded) {
            $this->recordOverlap[] = $aShiftId . ',' . $bShiftId;
        }

        return $isRecorded;
    }

    /**
     * @param Carbon $aShiftStart
     * @param Carbon $bShiftStart
     * @param \DateInterval $startDiff
     * @param $aShift
     * @param $bShift
     * @return array
     */
    public static function getSingleHours(Carbon $aShiftStart, Carbon $bShiftStart, \DateInterval $startDiff, $aShift, $bShift)
    {
        $startTime = $aShiftStart->getTimestamp();
        $startTimeToCompare = $bShiftStart->addHours($startDiff->h)->getTimestamp();
        $shiftId = isset($aShift['parent_id']) ? $aShift['parent_id'] : $aShift['id'];

        if ($startTime === $startTimeToCompare) {
            $shiftId = isset($bShift['parent_id']) ? $bShift['parent_id'] : $bShift['id'];
        }

        return [
            'shift_id' => $shiftId,
            'single_manned_hours' => $startDiff->h,
            'shift_day'  => FormatterHelper::formatShiftDay($aShift['start_time'])
        ];
    }

    /**
     * @param string $startTimeA
     * @param string $endTimeA
     * @param string $startTimeB
     * @param string $endTimeB
     * @return bool
     * @throws \Exception
     */
    private static function hasOverlap(
        string $startTimeA,
        string $endTimeA,
        string $startTimeB,
        string $endTimeB) : bool
    {
        $startTimeADate = new DateTime($startTimeA);
        $endTimeADate   = new DateTime($endTimeA);
        $startTimeBDate = new DateTime($startTimeB);
        $endTimeBDate   = new DateTime($endTimeB);

        if (($startTimeBDate->getTimestamp() >= $startTimeADate->getTimestamp())
            && ($endTimeADate->getTimestamp() > $startTimeBDate->getTimestamp())) {
            return true;
        }

        if (($endTimeBDate->getTimestamp() <= $endTimeADate->getTimestamp())
            && ($startTimeADate->getTimestamp() < $endTimeBDate->getTimestamp())) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function generateShiftMannedReport() : array
    {
        $shiftArray      = Shifts::getShifts();
        $formattedShifts = $this->formatter->formatWithBreaks($shiftArray);
        return $this->calculateSingleMannedHours($formattedShifts);
    }
}