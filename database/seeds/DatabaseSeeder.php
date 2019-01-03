<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ShopsTableSeeder::class);
        $this->call(RotasTableSeeder::class);
        $this->call(StaffTableSeeder::class);
        $this->call(ShiftsTableSeeder::class);
        $this->call(ShiftBreaksTableSeeder::class);
    }
}
