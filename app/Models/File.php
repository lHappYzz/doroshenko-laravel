<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    /** @var string[] */
    protected $fillable = ['path', 'name'];
}
