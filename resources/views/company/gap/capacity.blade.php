<x-company-layout title="Capacity">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Capacity planning</div>
@foreach($rows as $row)
<div class="ptm-card" style="padding:14px 16px; margin-bottom:8px; {{ $row['overloaded'] ? 'border-color:rgba(248,113,113,.4);' : '' }}">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center;">
        <div>
            <div style="font-weight:600;">{{ $row['user']->name }}</div>
            <div style="font-size:12px; color:var(--muted);">{{ $row['used'] }}h used / {{ $row['capacity'] }}h capacity · {{ $row['open'] }} open tasks{{ $row['overloaded'] ? ' · Overloaded' : '' }}</div>
        </div>
        <form method="POST" action="{{ route('company.capacity.update', [$slug, $row['user']]) }}" style="display:flex; gap:6px;">
            @csrf
            <input type="number" name="weekly_capacity_hours" value="{{ $row['capacity'] }}" class="ptm-input" style="width:80px;">
            <button class="ptm-btn-ghost">Save</button>
        </form>
    </div>
</div>
@endforeach
</x-company-layout>
