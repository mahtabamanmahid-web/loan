<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Business-rule key-value settings (loan limits, maintenance mode...). */
class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];
}
