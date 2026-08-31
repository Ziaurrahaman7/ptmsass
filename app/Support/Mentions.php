<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class Mentions
{
    public static function extractIds(string $text, Collection $members, array $explicit = []): array
    {
        $ids = collect($explicit)->map(fn ($id) => (int) $id)->filter()->all();

        $sorted = $members->sortByDesc(fn (User $u) => mb_strlen($u->name));
        foreach ($sorted as $user) {
            $name = preg_quote($user->name, '/');
            if (preg_match('/@'.$name.'(?![\p{L}\p{N}_])/ui', $text)) {
                $ids[] = (int) $user->id;
            }
        }

        return array_values(array_unique($ids));
    }

    public static function toHtml(string $text, Collection $members): string
    {
        $safe = e($text);
        $sorted = $members->sortByDesc(fn (User $u) => mb_strlen($u->name));
        foreach ($sorted as $user) {
            $name = e($user->name);
            $safe = preg_replace(
                '/@'.preg_quote($name, '/').'(?![\p{L}\p{N}_])/ui',
                '<span style="color:#22d3ee; font-weight:700; background:rgba(34,211,238,0.14); padding:1px 5px; border-radius:4px;">$0</span>',
                $safe
            );
        }

        return nl2br($safe);
    }
}
