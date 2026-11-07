<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['question', 'question_bn', 'answer', 'answer_bn', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function displayQuestion(): string
    {
        return app()->getLocale() === 'bn' && $this->question_bn ? $this->question_bn : $this->question;
    }

    public function displayAnswer(): string
    {
        return app()->getLocale() === 'bn' && $this->answer_bn ? $this->answer_bn : $this->answer;
    }
}
