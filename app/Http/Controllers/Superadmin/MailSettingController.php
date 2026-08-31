<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use App\Services\PlatformMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailSettingController extends Controller
{
    public function edit()
    {
        $settings = MailSetting::current();

        return view('superadmin.smtp.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'is_enabled'   => 'nullable|boolean',
            'host'         => 'required|string|max:255',
            'port'         => 'required|integer|min:1|max:65535',
            'encryption'   => 'required|in:ssl,tls',
            'username'     => 'required|string|max:255',
            'password'     => 'nullable|string|max:255',
            'from_address' => 'required|email|max:255',
            'from_name'    => 'required|string|max:255',
        ]);

        $settings = MailSetting::query()->first() ?? new MailSetting;

        if (blank($data['password'] ?? null) && ! $settings->exists) {
            return back()->withInput()->withErrors(['password' => 'Password is required the first time.']);
        }

        $settings->fill([
            'is_enabled'   => $request->boolean('is_enabled'),
            'host'         => $data['host'],
            'port'         => $data['port'],
            'scheme'       => $data['encryption'] === 'ssl' ? 'smtps' : 'smtp',
            'username'     => $data['username'],
            'from_address' => $data['from_address'],
            'from_name'    => $data['from_name'],
            'updated_by'   => auth()->id(),
        ]);

        if (filled($data['password'] ?? null)) {
            $settings->password = $data['password'];
        }

        $settings->save();
        PlatformMail::apply($settings->fresh());

        return back()->with('success', 'SMTP settings saved.');
    }

    public function test(Request $request)
    {
        $data = $request->validate([
            'test_email' => 'required|email',
        ]);

        $settings = MailSetting::query()->first();
        if (! $settings || ! $settings->isReady()) {
            return back()->with('error', 'Save and enable SMTP first, then send a test.');
        }

        try {
            PlatformMail::apply($settings);
            Mail::raw(
                'PTMSaaS SMTP test. If you received this, platform email is working.',
                function ($message) use ($data, $settings) {
                    $message->to($data['test_email'])
                        ->subject('PTMSaaS SMTP test');
                    if ($settings->from_address) {
                        $message->from($settings->from_address, $settings->from_name ?: config('app.name'));
                    }
                }
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Test email failed: '.$e->getMessage());
        }

        return back()->with('success', 'Test email sent to '.$data['test_email'].'.');
    }
}
