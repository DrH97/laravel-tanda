<?php

namespace DrH\Tanda\Models;

use Illuminate\Database\Eloquent\Model;

class TandaRequest extends Model
{
    protected $guarded = ['id'];

    protected $dates = [
        'last_modified'
    ];

    protected $casts = [
        'result' => 'array'
    ];
}
