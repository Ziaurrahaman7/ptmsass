<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PusherSetting;
use App\Services\PlatformBroadcast;
use Illuminate\Http\Request;
use Pusher\Pusher;

class PusherSettingController extends Controller
{
    public function edit()
    {
        $settings = PusherSetting::current();

        return view('superadmin.pusher.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'app_id' => 'required|string|max:64',
            'key' => 'required|string|max:128',
            'secret' => 'nullable|string|max:255',
            'cluster' => 'required|string|max:32',
        ]);

        $settings = PusherSetting::query()->first() ?? new PusherSetting;

        if (blank($data['secret'] ?? null) && ! $settings->exists) {
            return back()->withInput()->withErrors(['secret' => 'Secret is required the first time.']);
        }

        $settings->fill([
            'is_enabled' => $request->boolean('is_enabled'),
            'app_id' => $data['app_id'],
            'key' => $data['key'],
            'cluster' => $data['cluster'],
            'updated_by' => auth()->id(),
        ]);

        if (filled($data['secret'] ?? null)) {
            $settings->secret = $data['secret'];
        }

        $settings->save();
        PlatformBroadcast::apply($settings->fresh());

        return back()->with('success', 'Pusher settings saved.');
    }

    public function test()
    {
        $settings = PusherSetting::query()->first();
        if (! $settings || ! $settings->isReady()) {
            return back()->with('error', 'Save and enable Pusher first, then send a test.');
        }

        try {
            PlatformBroadcast::apply($settings);
            $pusher = new Pusher(
                $settings->key,
                $settings->secret,
                $settings->app_id,
                ['cluster' => $settings->cluster, 'useTLS' => true]
            );
            $result = $pusher->trigger('ptmsass-test', 'ping', [
                'ok' => true,
                'at' => now()->toIso8601String(),
            ]);

            if ($result === false) {
                return back()->with('error', 'Pusher rejected the test event.');
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Pusher test failed: '.$e->getMessage());
        }

        return back()->with('success', 'Pusher accepted the test event. Real-time bells will work for company users.');
    }
}
