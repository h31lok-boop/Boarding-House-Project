<?php

namespace App\Models;

/**
 * Backwards-compatible alias for code that still refers to tenant preferences.
 */
class TenantPreference extends UserPreference
{
    protected $table = 'user_preferences';
}
