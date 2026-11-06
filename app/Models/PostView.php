<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostView extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['post_id', 'user_id', 'created_at'];
}
