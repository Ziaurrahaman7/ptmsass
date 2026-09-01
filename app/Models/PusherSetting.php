<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PusherSetting extends Model
{
    protected $fillable = [
        'is_enabled', 'app_id', 'key', 'secret', 'cluster', 'updated_by',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'secret' => 'encrypted',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'is_enabled' => false,
            'cluster' => 'ap2',
        ]);
    }

    public function isReady(): bool
    {
        return $this->exists
            && $this->is_enabled
            && filled($this->app_id)
            && filled($this->key)
            && filled($this->getRawOriginal('secret') ?: $this->secret)
            && filled($this->cluster);
    }
}
