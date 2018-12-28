<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffTableSeeder extends Seeder
{
    const SHOP_ID = 1;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('staff')->insert([
            [
                'first_name' => 'Black',
                'surname'   => 'Widow',
                'shop_id'   => self::SHOP_ID
            ],
            [
                'first_name' => 'Thor',
                'shop_id'   => self::SHOP_ID
            ],
            [
                'first_name' => 'Wolverine',
                'shop_id'   => self::SHOP_ID
            ],
            [
                'first_name' => 'Gamora',
                'shop_id'   => self::SHOP_ID
            ]
        ]);
    }
}
