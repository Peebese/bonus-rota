<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shifts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rota_id', 'staff_id', 'start_time', 'end_time'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    protected static function getShifts() : array
    {
        return self::query()
            ->select([
                'shifts.id',
                'rota_id',
                'shop_id',
                'first_name',
                'shifts.start_time',
                'shifts.end_time',
                'shift_breaks.start_time AS break_start_time',
                'shift_breaks.end_time AS break_end_time'
            ])
            ->leftJoin('shift_breaks','shifts.id','=','shift_breaks.shift_id')
            ->leftJoin('staff','shifts.staff_id','=','staff.id')
            ->get()
            ->toArray();
    }
}
