<?php

namespace App\Services;

use App\Models\PusherSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class PlatformBroadcast
{
    public static function apply(?PusherSetting $settings = null): bool
    {
        try {
            if (! Schema::hasTable('pusher_settings')) {
                return false;
            }
        } catch (\Throwable) {
            return false;
        }

        $settings ??= PusherSetting::query()->first();
        if (! $settings || ! $settings->isReady()) {
            return false;
        }

        Config::set('broadcasting.default', 'pusher');
        Config::set('broadcasting.connections.pusher.driver', 'pusher');
        Config::set('broadcasting.connections.pusher.key', $settings->key);
        Config::set('broadcasting.connections.pusher.secret', $settings->secret);
        Config::set('broadcasting.connections.pusher.app_id', $settings->app_id);
        Config::set('broadcasting.connections.pusher.options.cluster', $settings->cluster);
        Config::set('broadcasting.connections.pusher.options.useTLS', true);

        try {
            app()->forgetInstance(\Illuminate\Broadcasting\BroadcastManager::class);
            app()->forgetInstance('broadcast');
        } catch (\Throwable) {
        }

        return true;
    }
}
