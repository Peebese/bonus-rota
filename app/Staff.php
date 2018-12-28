<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'surname', 'shop_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function insertStaff() : void
    {
        $data = [
            [
                'first_name' => 'Black',
                'surname'   => 'Widow',
                'shop_id'   => 1
            ],
            [
                'first_name' => 'Thor',
                'shop_id'   => 1
            ],
            [
                'first_name' => 'Wolverine',
                'shop_id'   => 1
            ],
            [
                'first_name' => 'Gamora',
                'shop_id'   => 1
            ]
        ];

        self::query()->insert($data);
    }
}
