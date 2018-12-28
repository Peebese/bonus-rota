<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rotas extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id', 'week_commence_data',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function insertRotas()
    {
        $data = [
            'shop_id' => 1,
            'week_commence_date' => date('Y-m-d',strtotime('2019-'))
        ];


    }
}
