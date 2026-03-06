<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BoardingHouse extends Model
{
    use HasFactory;
    private static array $schemaColumnCache = [];

    protected $fillable = [
        'owner_profile_id',
        'owner_id',
        'name',
        'address',
        'full_address',
        'latitude',
        'longitude',
        'region_id',
        'province_id',
        'city_id',
        'barangay_id',
        'description',
        'house_rules',
        'landlord_info',
        'contact_name',
        'contact_number',
        'contact_phone',
        'monthly_payment',
        'capacity',
        'is_active',
        'approval_status',
        'status',
        'approval_date',
        'approved_by',
        'rejection_reason',
        'price',
        'available_rooms',
        'exterior_image',
        'room_image',
        'cr_image',
        'kitchen_image',
        'featured_image',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'available_rooms' => 'integer',
    ];

    public function setPriceAttribute($value): void
    {
        $normalized = $this->normalizeMoneyValue($value);
        if ($this->tableHasColumn('price')) {
            $this->attributes['price'] = $normalized;
        }

        if (
            $this->tableHasColumn('monthly_payment')
            && (! isset($this->attributes['monthly_payment']) || trim((string) ($this->attributes['monthly_payment'] ?? '')) === '')
        ) {
            $this->attributes['monthly_payment'] = $normalized !== null ? (string) $normalized : null;
        }
    }

    public function setMonthlyPaymentAttribute($value): void
    {
        if ($this->tableHasColumn('monthly_payment')) {
            $this->attributes['monthly_payment'] = $value;
        }

        $normalized = $this->normalizeMoneyValue($value);
        if (
            $this->tableHasColumn('price')
            && $normalized !== null
            && (! isset($this->attributes['price']) || (float) $this->attributes['price'] <= 0)
        ) {
            $this->attributes['price'] = $normalized;
        }
    }

    public function setContactPhoneAttribute($value): void
    {
        if ($this->tableHasColumn('contact_phone')) {
            $this->attributes['contact_phone'] = $value;
        }
        if (
            $this->tableHasColumn('contact_number')
            && (! isset($this->attributes['contact_number']) || trim((string) ($this->attributes['contact_number'] ?? '')) === '')
        ) {
            $this->attributes['contact_number'] = $value;
        }
    }

    public function setContactNumberAttribute($value): void
    {
        if ($this->tableHasColumn('contact_number')) {
            $this->attributes['contact_number'] = $value;
        }
        if (
            $this->tableHasColumn('contact_phone')
            && (! isset($this->attributes['contact_phone']) || trim((string) ($this->attributes['contact_phone'] ?? '')) === '')
        ) {
            $this->attributes['contact_phone'] = $value;
        }
    }

    public function getEffectivePriceAttribute(): ?float
    {
        if ($this->price !== null) {
            return (float) $this->price;
        }

        $monthly = $this->normalizeMoneyValue($this->monthly_payment ?? null);
        if ($monthly !== null) {
            return $monthly;
        }

        if ($this->relationLoaded('rooms')) {
            $roomMin = $this->rooms->min('price');
            if ($roomMin !== null) {
                return (float) $roomMin;
            }
        }

        if ($this->relationLoaded('roomCategories')) {
            $categoryMin = $this->roomCategories->min('monthly_rate');
            if ($categoryMin !== null) {
                return (float) $categoryMin;
            }
        }

        return null;
    }

    public function getEffectiveContactAttribute(): ?string
    {
        return $this->contact_number
            ?: ($this->contact_phone
                ?: ($this->contact_person
                    ?: ($this->contact_name ?: null)));
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function ownerProfile()
    {
        return $this->belongsTo(OwnerProfile::class, 'owner_profile_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(CityMunicipality::class, 'city_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function tenants()
    {
        return $this->hasMany(User::class, 'boarding_house_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function roomCategories()
    {
        return $this->hasMany(RoomCategory::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'boarding_house_amenities')->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(BoardingHouseImage::class)->orderBy('is_primary', 'desc')->orderBy('sort_order');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->approved();
    }

    private function normalizeMoneyValue($value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9.]/', '', (string) $value);
        if ($normalized === null || $normalized === '') {
            return null;
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function tableHasColumn(string $column): bool
    {
        $connection = $this->getConnectionName() ?: config('database.default');
        $cacheKey = $connection.'.'.$this->getTable();

        if (! array_key_exists($cacheKey, self::$schemaColumnCache)) {
            try {
                self::$schemaColumnCache[$cacheKey] = Schema::connection($connection)->getColumnListing($this->getTable());
            } catch (\Throwable) {
                self::$schemaColumnCache[$cacheKey] = [];
            }
        }

        return in_array($column, self::$schemaColumnCache[$cacheKey], true);
    }
}
