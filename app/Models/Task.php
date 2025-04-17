<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['task_title','task_description'];

    public function cohorts()
    {
        return $this->belongsToMany(Cohort::class, 'cohort_task');
    }

}
