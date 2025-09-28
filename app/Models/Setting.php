<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['label', 'data'];

    public static function getValue($label, $default = null)
    {
        return static::where('label', $label)->value('data') ?? $default;
    }

    public static function setValue($label, $value)
    {
        return static::updateOrCreate(
            ['label' => $label],
            ['data' => $value]
        );
    }
}
