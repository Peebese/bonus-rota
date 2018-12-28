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

        $blackId        = DB::table('staff')->select()->where(['first_name' => 'Black'])->first()->id;
        $thorId         = DB::table('staff')->select()->where(['first_name' => 'Thor'])->first()->id;
        $wolverineId    = DB::table('staff')->select()->where(['first_name' => 'Wolverine'])->first()->id;
        $gamoraId       = DB::table('staff')->select()->where(['first_name' => 'Gamora'])->first()->id;


        $rotaId = DB::table('rotas')
            ->select()
            ->where(['name' => 'FunHouse'])
            ->leftJoin('shops','rotas.shop_id','=','shops.id')
            ->first()->id;

//        dd([
//            $blackId, $thorId, $wolverineId, $gamoraId, $rotaId
//        ]);

        $data = [
            // Monday, Black widow works alone
            [
                'staff_id'      => $blackId,
                'rota_id'       => $rotaId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,7,9,0,0)->toDateTimeString(),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,7, 18,0,0)->toDateTimeString()
            ],
            // Tuesday, Black and thor
            [
                'staff_id'      => $blackId,
                'rota_id'       => $rotaId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,8,9,0)->toDateTimeString(),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,8,14,0)->toDateTimeString()
            ],
            [
                'staff_id'      => $thorId,
                'rota_id'       => $rotaId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,8,14,0)->toDateTimeString(),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,8,22,0)->toDateTimeString()
            ],
            // Wednesday, Wolverine and Gamora
            [
                'staff_id'      => $wolverineId,
                'rota_id'       => $rotaId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,9,9,0)->toDateTimeString(),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,9,18,0)->toDateTimeString()
            ],
            [
                'staff_id'      => $gamoraId,
                'rota_id'       => $rotaId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,9,12,0)->toDateTimeString(),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,9,22,0)->toDateTimeString()
            ],
            // Thursday, Wolverine and Gamora
            [
                'staff_id'      => $wolverineId,
                'rota_id'       => $rotaId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,10,9,0)->toDateTimeString(),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,10,18,0)->toDateTimeString()
            ],
            [
                'staff_id'      => $gamoraId,
                'rota_id'       => $rotaId,
                'start_time'    => $carbonDateObj->setDateTime(2019,1,10,9,0)->toDateTimeString(),
                'end_time'      => $carbonDateObj->setDateTime(2019,1,10,18,0)->toDateTimeString()
            ]
        ];

        DB::table('shifts')->insert($data);
    }
}
