<?php

namespace DrH\Tanda\Models;

use Illuminate\Database\Eloquent\Model;

class TandaRequest extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'result'        => 'array',
        'last_modified' => 'datetime'
    ];
}
