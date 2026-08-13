<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            // Every integration value is encrypted at rest, including values
            // that are not credentials. This keeps the storage rule simple
            // and prevents future fields from accidentally being saved raw.
            'value' => 'encrypted',
        ];
    }
}
