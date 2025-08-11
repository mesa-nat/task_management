<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Allow mass assignment for these fields
    protected $fillable = ['title', 'description', 'completed', 'due_date'];

}
