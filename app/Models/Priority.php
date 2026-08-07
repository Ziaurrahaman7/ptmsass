<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Priority extends Model
{
    protected $fillable = [
        'company_id', 'name', 'slug', 'color', 'position', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'position'   => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function forCompany(int $companyId)
    {
        return static::where('company_id', $companyId)->orderBy('position')->orderBy('id')->get();
    }

    public static function defaultSlugFor(int $companyId): string
    {
        $priorities = static::forCompany($companyId);
        return $priorities->firstWhere('is_default', true)?->slug
            ?? $priorities->first()?->slug
            ?? 'medium';
    }

    // Company's standard starter set — used when a new company is created.
    public static function seedDefaults(int $companyId): void
    {
        $defaults = [
            ['name' => 'Low',    'color' => '#6b7385', 'position' => 0, 'is_default' => false],
            ['name' => 'Medium', 'color' => '#fbbf24', 'position' => 1, 'is_default' => true],
            ['name' => 'High',   'color' => '#fb923c', 'position' => 2, 'is_default' => false],
            ['name' => 'Urgent', 'color' => '#f87171', 'position' => 3, 'is_default' => false],
        ];

        foreach ($defaults as $d) {
            static::create($d + ['company_id' => $companyId, 'slug' => Str::slug($d['name'])]);
        }
    }

    public static function uniqueSlug(int $companyId, string $name): string
    {
        $base = Str::slug($name) ?: 'priority';
        $slug = $base;
        $i = 2;
        while (static::where('company_id', $companyId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
