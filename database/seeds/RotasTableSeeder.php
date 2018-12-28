<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RotasTableSeeder extends Seeder
{
    const SHOP_ID = 1;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rotas')->insert([
            'shop_id' => self::SHOP_ID,
            'week_commence_date' => (new Carbon())->setDate(2019,1,7)
        ]);
    }
}
