<?php
namespace App\Helpers\Formatter;

class FormatterHelper
{
    /**
     *
     * @param array $shifts
     * @return array
     */
    public static function formatShiftsByDay(array $shifts) : array
    {
        $startTimeArr = [];

        foreach ($shifts as $shift) {

            $day = date('D', strtotime($shift['start_time']));
            $startTimeArr[$day][] = $shift;
        }

        return $startTimeArr;
    }

    /**
     * Format date, return abbrev string of weekday
     *
     * @param string $dateTime
     * @return false|string
     */
    public static function formatShiftDay(string $dateTime)
    {
        return date('D',strtotime($dateTime));
    }

    /**
     * Divides shifts with breaks into individual shifts
     *
     * @param array $shifts
     *
     * @return array
     */
    public function formatWithBreaks(array $shifts) : array
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