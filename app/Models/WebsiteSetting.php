<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Branding / homepage key-value settings. Read through App\Services\SettingService. */
class WebsiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];
}
