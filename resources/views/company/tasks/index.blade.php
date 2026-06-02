<x-company-layout title="All Tasks">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
        <div>
            <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">All Tasks</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Tasks across all projects</div>
        </div>
    </div>

    <div class="ptm-card" style="overflow:hidden;">
        <table class="ptm-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 18px; text-align:left;">Task</th>
                    <th style="padding:12px 18px; text-align:left;">Project</th>
                    <th style="padding:12px 18px; text-align:left;">Assignee</th>
                    <th style="padding:12px 18px; text-align:left;">Priority</th>
                    <th style="padding:12px 18px; text-align:left;">Status</th>
                    <th style="padding:12px 18px; text-align:left;">Due Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr style="border-bottom:1px solid var(--border); transition:background 0.1s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 18px; font-size:13px; font-weight:500; color:var(--text);">{{ $task->title }}</td>
                    <td style="padding:12px 18px;">
                        <a href="{{ route('company.projects.show', $task->project) }}" style="font-size:13px; color:var(--accent); text-decoration:none;">{{ $task->project->name }}</a>
                    </td>
                    <td style="padding:12px 18px; font-size:13px; color:var(--muted); font-family:var(--mono);">{{ $task->assignee?->name ?? '—' }}</td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $task->priority === 'urgent' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                               ($task->priority === 'high' ? 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                               ($task->priority === 'medium' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $task->status === 'done' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                               ($task->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                               ($task->status === 'in_review' ? 'color:#a78bfa; border-color:rgba(167,139,250,0.3); background:rgba(167,139,250,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                            {{ ucfirst(str_replace('_',' ',$task->status)) }}
                        </span>
                    </td>
                    <td style="padding:12px 18px; font-size:12px; font-family:var(--mono); {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">
                        {{ $task->due_date?->format('d M Y') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:48px; text-align:center; color:var(--muted); font-size:13px;">No tasks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
    <div style="margin-top:16px;">{{ $tasks->links() }}</div>
    @endif

</x-company-layout>
