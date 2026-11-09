<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\MattiServiceArea;

class BoardingHouse extends Model
{
    use HasFactory;

    private static array $schemaColumnCache = [];

    protected static function booted(): void
    {
        static::saving(function (self $house): void {
            // Coordinates are validated at user-facing write boundaries. Do not
            // reject legacy or address-only records at the model layer; public
            // queries explicitly exclude records outside the service area.
            if (filled($house->latitude) && filled($house->longitude)) {
                $house->barangay = config('service-area.barangay');
            }
        });
    }

    protected $fillable = [
        'owner_profile_id',
        'owner_id',
        'user_id',
        'name',
        'slug',
        'address',
        'full_address',
        'proof_of_ownership',
        'latitude',
        'longitude',
        'region_id',
        'province_id',
        'city_id',
        'barangay_id',
        'barangay',
        'nearby_landmark',
        'distance_from_dssc',
        'is_near_dssc',
        'location_status',
        'description',
        'house_rules',
        'landlord_info',
        'contact_name',
        'contact_person',
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
        'advance_rent',
        'security_deposit',
        'billing_cycle',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'available_rooms' => 'integer',
        'distance_from_dssc' => 'decimal:2',
        'advance_rent' => 'integer',
        'security_deposit' => 'decimal:2',
        'is_near_dssc' => 'boolean',
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

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function barangayReference()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function getDisplayBarangayAttribute(): ?string
    {
        return $this->barangay ?: $this->barangayReference?->barangay_name;
    }

    /**
     * The owner-entered address is the canonical property address. The legacy
     * full_address column is only used when the newer address field is empty.
     */
    public function getCanonicalAddressAttribute(): ?string
    {
        $address = trim((string) ($this->address ?: $this->full_address ?: ''));

        return $address !== '' ? $address : null;
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
        return $this->hasMany(BoardingHouseImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverImage()
    {
        return $this->hasOne(BoardingHouseImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function photos()
    {
        return $this->hasMany(BoardingHousePhoto::class);
    }

    public function getCoverImagePathAttribute(): ?string
    {
        $imagePaths = $this->relationLoaded('images')
            ? $this->images
                ->sortBy(fn ($image) => [
                    $image->is_primary ? 0 : 1,
                    (int) ($image->sort_order ?? 0),
                    (int) $image->id,
                ])
                ->pluck('image_path')
            : collect([
                $this->images()->where('is_primary', true)->value('image_path'),
                $this->coverImage()->value('image_path'),
            ]);

        $imagePaths = $imagePaths->merge([
            $this->featured_image,
            $this->exterior_image,
            $this->room_image,
            $this->cr_image,
            $this->kitchen_image,
        ]);

        $imagePaths = $imagePaths->merge(
            $this->relationLoaded('photos')
                ? $this->photos->pluck('photo_path')
                : collect([$this->photos()->value('photo_path')])
        );

        foreach ($imagePaths->filter()->map(fn ($path) => $this->normalizePublicImagePath((string) $path)) as $path) {
            if ($this->publicImagePathExists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function getCoverImageUrlAttribute(): string
    {
        $path = $this->cover_image_path;

        if (! $path) {
            return asset('images/boarding-house-placeholder.svg');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $parts = parse_url($path);
            $host = strtolower((string) ($parts['host'] ?? ''));
            if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                return asset(ltrim((string) ($parts['path'] ?? ''), '/'));
            }

            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    private function normalizePublicImagePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $parts = parse_url($path);

        if (($parts['scheme'] ?? null) !== null || ($parts['host'] ?? null) !== null) {
            $host = strtolower((string) ($parts['host'] ?? ''));
            if (! in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                return $path;
            }
            $path = (string) ($parts['path'] ?? '');
        }

        $path = ltrim($path, '/');
        foreach (['storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
            }
        }

        return $path;
    }

    private function publicImagePathExists(string $path): bool
    {
        return Str::startsWith($path, ['http://', 'https://'])
            || Storage::disk('public')->exists($path)
            || is_file(public_path($path));
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function matches()
    {
        return $this->hasMany(BoardingHouseMatch::class);
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
        return $this->hasMany(Review::class)->where('status', 'published');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentReceipts()
    {
        return $this->hasManyThrough(PaymentReceipt::class, Booking::class);
    }

    public function services()
    {
        return $this->hasMany(BoardingHouseService::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->approved();
    }

    /**
     * Restrict a query to listings inside the configured public service area.
     *
     * This is intentionally explicit instead of a global scope so administrators
     * can still inspect historical or legacy listings outside the service area.
     */
    public function scopeInServiceArea($query)
    {
        $model = $query->getModel();

        return $query->whereIntegerInRaw(
            $model->qualifyColumn('id'),
            app(MattiServiceArea::class)->eligibleIds($model->getConnectionName()),
        );
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
