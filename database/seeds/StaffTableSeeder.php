<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffTableSeeder extends Seeder
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

        DB::table('staff')->insert([
            [
                'first_name' => 'Black',
                'surname'   => 'Widow',
                'shop_id'   => $shopId
            ],
            [
                'first_name' => 'Thor',
                'surname'   => 'surname',
                'shop_id'   => $shopId
            ],
            [
                'first_name' => 'Wolverine',
                'surname'   => 'surname',
                'shop_id'   => $shopId
            ],
            [
                'first_name' => 'Gamora',
                'surname'   => 'surname',
                'shop_id'   => $shopId
            ]
        ]);
    }
}
