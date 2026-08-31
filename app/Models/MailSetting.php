<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailSetting extends Model
{
    protected $fillable = [
        'is_enabled', 'host', 'port', 'scheme',
        'username', 'password', 'from_address', 'from_name', 'updated_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'port'       => 'integer',
        'password'   => 'encrypted',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'is_enabled'   => false,
            'host'         => 'smtp.hostinger.com',
            'port'         => 465,
            'scheme'       => 'smtps',
            'from_name'    => config('app.name'),
        ]);
    }

    public function isReady(): bool
    {
        return $this->exists
            && $this->is_enabled
            && filled($this->host)
            && filled($this->username)
            && filled($this->from_address)
            && filled($this->getRawOriginal('password') ?: $this->password);
    }

    public function encryption(): string
    {
        return $this->scheme === 'smtps' ? 'ssl' : 'tls';
    }
}
