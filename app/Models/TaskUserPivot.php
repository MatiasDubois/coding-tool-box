<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;


class TaskUserPivot extends Pivot
{
    protected $table = 'task_user';

    protected $fillable = [
        'validated_at',
        'comment',
    ];

    protected $dates = ['validated_at'];
}
