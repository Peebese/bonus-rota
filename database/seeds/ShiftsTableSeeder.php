<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ShiftsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $carbonDateObj = new Carbon();

        $staffTable   = DB::table('staff')->select(['id']);
        $blackId      = $staffTable->where(['first_name' => 'black']);
        $thorId       = $staffTable->where(['first_name' => 'thor']);
        $wolverineId  = $staffTable->where(['first_name' => 'wolverine']);
        $gamoraId     = $staffTable->where(['first_name' => 'gamora']);

        $data = [
            // Monday, Black widow works alone
            [
                'staff_id'      => $blackId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,7,9,0,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,7, 18,0,0)
            ],
            // Tuesday, Black and thor
            [
                'staff_id'      => $blackId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,8,9,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,8,14,0)
            ],
            [
                'staff_id'      => $thorId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,8,14,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,8,22,0)
            ],
            // Wednesday, Wolverine and Gamora
            [
                'staff_id'      => $wolverineId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,9,9,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,9,18,0)
            ],
            [
                'staff_id'      => $gamoraId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,9,12,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,9,22,0)
            ],
            // Thursday, Wolverine and Gamora
            [
                'staff_id'      => $wolverineId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,10,9,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,10,18,0)
            ],
            [
                'staff_id'      => $gamoraId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,10,9,0),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,10,18,0)
            ]
        ];

        DB::table('shifts')->insert($data);
    }
}
