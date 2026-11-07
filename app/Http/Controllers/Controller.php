<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

abstract class Controller
{
    /**
     * Customers may only see their own records. Responds 404 (not 403) so that
     * the existence of other customers' records is not revealed.
     */
    protected function ensureOwner(Model $model, string $column = 'user_id'): void
    {
        abort_unless((int) $model->{$column} === (int) auth()->id(), 404);
    }
}
