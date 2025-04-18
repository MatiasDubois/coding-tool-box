<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['task_title','task_description'];

    // Relation with Cohort
    public function cohorts()
    {
        return $this->belongsToMany(Cohort::class, 'cohort_task');
    }

    // Relation with User
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('validated_at' , 'comment')
                    ->using(TaskUserPivot::class)
                    ->withTimestamps();
    }
}
