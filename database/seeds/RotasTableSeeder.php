<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RotasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shopId = DB::table('shops')
            ->select()
            ->where(['name' => 'Fun House'])
            ->first()->id
            ;

        DB::table('rotas')->insert([
            'shop_id' => $shopId,
            'week_commence_date' => (new Carbon())->setDateTime(2019,1,7, 9, 0)->toDateTimeString()
        ]);
    }
}
