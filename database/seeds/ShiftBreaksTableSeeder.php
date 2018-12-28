<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftBreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $carbonDateObj = new Carbon();
        $dateDay = 10;

        $wolverineShiftId  = DB::table('shifts')
            ->select()
            ->where(['first_name' => 'Wolverine'])
            ->whereDay('start_time','=', $dateDay)
            ->leftJoin('staff', 'shifts.staff_id','=','staff.id')
            ->first()->id
        ;

        //dd($wolverineShiftId);

        $gamoraShiftId  = DB::table('shifts')
            ->select(['shifts.id'])
            ->where(['first_name' => 'Gamora'])
            ->whereDay('start_time', '=', $dateDay)
            ->leftJoin('staff', 'shifts.staff_id','=','staff.id')
            ->first()->id
        ;

        $data = [
            [
                'shift_id'      => $wolverineShiftId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,$dateDay,12,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,$dateDay,13,0)
            ],
            [
                'shift_id'      => $gamoraShiftId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,$dateDay,14,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,$dateDay,15,0)
            ],

        ];

        DB::table('shift_breaks')->insert($data);
    }
}
