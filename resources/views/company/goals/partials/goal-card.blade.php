{{-- Single goal row. Expects $goal (with owner, team, projects loaded). --}}
<div class="goal-card">
    <div class="goal-status-dot" style="background:{{ $goal->status_color }};"></div>
    <div class="goal-main">
        <div class="goal-title-row">
            <span class="goal-title">{{ $goal->title }}</span>
            <span class="goal-status-badge" style="background:{{ $goal->status_color }}22; color:{{ $goal->status_color }};">{{ ucfirst(str_replace('_',' ',$goal->status)) }}</span>
        </div>
        <div class="goal-progress-row">
            <div class="goal-progress-track"><div class="goal-progress-fill" style="width:{{ $goal->progress }}%; background:{{ $goal->status_color }};"></div></div>
            <span class="goal-progress-pct">{{ $goal->progress }}%</span>
        </div>
        @if($goal->projects->isNotEmpty())
        <div class="goal-chips">
            @foreach($goal->projects as $proj)
            <span class="goal-chip">{{ $proj->name }}</span>
            @endforeach
        </div>
        @endif
    </div>
    <div class="goal-meta">
        <div class="goal-owner" title="{{ $goal->owner->name ?? '' }}">{{ strtoupper(substr($goal->owner->name ?? '?', 0, 2)) }}</div>
        <div class="goal-due">{{ $goal->due_date?->format('d M') ?? '—' }}</div>
        <button type="button" class="goal-edit-btn" onclick="openGoalModal({{ $goal->id }})" title="Edit goal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
    </div>
</div>
