<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftBreaks extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shift_id', 'start_time', 'end_time'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getShiftBreak(int $shift_id)
    {
        return self::query()
            ->select(['id', 'start_time', 'end_time'])
            ->where('shift_id','=', $shift_id)
            ->get()
            ->toArray();
        ;
    }
}
