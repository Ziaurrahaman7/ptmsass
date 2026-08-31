<?php

namespace App\Services;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class PlatformMail
{
    public static function apply(?MailSetting $settings = null): bool
    {
        try {
            if (! Schema::hasTable('mail_settings')) {
                return false;
            }
        } catch (\Throwable) {
            return false;
        }

        $settings ??= MailSetting::query()->first();
        if (! $settings || ! $settings->isReady()) {
            return false;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $settings->host);
        Config::set('mail.mailers.smtp.port', $settings->port);
        Config::set('mail.mailers.smtp.scheme', $settings->scheme);
        Config::set('mail.mailers.smtp.username', $settings->username);
        Config::set('mail.mailers.smtp.password', $settings->password);
        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name ?: config('app.name'));

        try {
            app('mail.manager')->purge('smtp');
        } catch (\Throwable) {
        }

        return true;
    }
}
