<x-company-layout title="API & webhooks">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Integrations</div>
<form method="POST" action="{{ route('company.integrations.tokens.store', $slug) }}" class="ptm-card" style="padding:16px; margin-bottom:16px; display:flex; gap:8px;">
    @csrf
    <input name="name" class="ptm-input" placeholder="Token name" required>
    <button class="ptm-btn-primary">Create API token</button>
</form>
@foreach($tokens as $token)
<div style="font-size:13px; color:var(--muted); margin-bottom:6px;">{{ $token->name }} · last used {{ $token->last_used_at?->diffForHumans() ?? 'never' }}</div>
@endforeach
<form method="POST" action="{{ route('company.integrations.webhooks.store', $slug) }}" class="ptm-card" style="padding:16px; margin-top:16px; display:flex; flex-direction:column; gap:8px;">
    @csrf
    <input name="url" class="ptm-input" placeholder="https://example.com/hook" required>
    <input name="event" class="ptm-input" value="task.created">
    <button class="ptm-btn-primary">Add webhook</button>
</form>
@foreach($webhooks as $hook)
<div style="font-size:12px; color:var(--muted); margin-top:8px;">{{ $hook->event }} → {{ $hook->url }}</div>
@endforeach
</x-company-layout>
