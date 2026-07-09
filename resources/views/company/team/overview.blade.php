@php
    $slug = auth()->user()->company->slug;
    $company = auth()->user()->company;
@endphp

<x-company-layout :title="$team->name . ' — Team'">

    {{-- Cover / Header --}}
    <div style="margin:-24px -24px 0; background:linear-gradient(135deg, #1a1d2e 0%, #12141f 100%); border-bottom:1px solid var(--border); padding:40px 32px 0; position:relative; overflow:hidden;">
        <div style="position:absolute; top:-60px; right:-60px; width:300px; height:300px; border-radius:50%; background:rgba(74,222,128,0.04); pointer-events:none;"></div>
        <div style="position:absolute; bottom:-80px; left:200px; width:200px; height:200px; border-radius:50%; background:rgba(34,211,238,0.03); pointer-events:none;"></div>

        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:20px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:20px;">
                <div style="width:72px; height:72px; border-radius:16px; background:linear-gradient(135deg, rgba(74,222,128,0.2), rgba(34,211,238,0.2)); border:2px solid rgba(74,222,128,0.3); display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; color:#4ade80; flex-shrink:0;">
                    {{ strtoupper(substr($team->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:22px; font-weight:700; color:var(--text); letter-spacing:-0.4px;">{{ $team->name }}</div>
                    <div style="font-size:13px; color:var(--muted); margin-top:4px;">{{ $members->count() }} members · {{ $projects->count() }} projects</div>
                    @if($team->description)
                    <div style="font-size:12px; color:var(--muted); margin-top:2px;">{{ $team->description }}</div>
                    @endif
                </div>
            </div>
            <div style="padding-bottom:4px; display:flex; gap:8px;">
                <button onclick="document.getElementById('editTeamModal').style.display='flex'" class="ptm-btn-ghost" style="font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit Team
                </button>
                <form method="POST" action="{{ route('company.teams.destroy', [$slug, $team]) }}" onsubmit="return confirm('Delete this team?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ptm-btn-danger" style="font-size:12px; padding:8px 14px;">Delete</button>
                </form>
            </div>
        </div>

        {{-- Tabs --}}
        <div style="display:flex; gap:0; margin-top:24px; align-items:center;">
            <button onclick="switchTab('overview')" id="tab-btn-overview" style="padding:10px 18px; background:none; border:none; border-bottom:2px solid #4ade80; color:#4ade80; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--font);">Overview</button>
            <button onclick="switchTab('members')" id="tab-btn-members" style="padding:10px 18px; background:none; border:none; border-bottom:2px solid transparent; color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:var(--font);">Members</button>
            <button onclick="switchTab('work')" id="tab-btn-work" style="padding:10px 18px; background:none; border:none; border-bottom:2px solid transparent; color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:var(--font);">All Work</button>
            <button onclick="switchTab('messages')" id="tab-btn-messages" style="padding:10px 18px; background:none; border:none; border-bottom:2px solid transparent; color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:var(--font);">Messages</button>
            <button onclick="switchTab('calendar')" id="tab-btn-calendar" style="padding:10px 18px; background:none; border:none; border-bottom:2px solid transparent; color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:var(--font);">Calendar</button>
            <button onclick="switchTab('knowledge')" id="tab-btn-knowledge" style="padding:10px 18px; background:none; border:none; border-bottom:2px solid transparent; color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:var(--font);">Knowledge</button>
            @foreach($notes as $note)
            <button onclick="switchTab('note-{{ $note->id }}')" id="tab-btn-note-{{ $note->id }}" style="padding:10px 14px; background:none; border:none; border-bottom:2px solid transparent; color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:var(--font); max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ \Illuminate\Support\Str::limit($note->title ?: 'Untitled Note', 16) }}
            </button>
            @endforeach
            <form method="POST" action="{{ route('company.teams.notes.store', [$slug, $team]) }}" style="display:inline; line-height:0;">
                @csrf
                <button type="submit" title="New note" style="padding:8px 10px; margin-left:4px; background:none; border:none; color:var(--muted); cursor:pointer; display:inline-flex; align-items:center; border-radius:6px; transition:color 0.12s, background 0.12s;" onmouseover="this.style.color='var(--text)'; this.style.background='rgba(255,255,255,0.05)';" onmouseout="this.style.color='var(--muted)'; this.style.background='transparent';">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>

    <div style="margin-top:28px;">

        {{-- OVERVIEW TAB --}}
        <div id="tab-overview">
            <div style="display:grid; grid-template-columns:1fr 320px; gap:20px;">

                {{-- Left --}}
                <div>
                    {{-- Curated Work --}}
                    <div class="ptm-card" style="margin-bottom:16px;">
                        <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:600; color:var(--text);">Curated Work</span>
                            <a href="{{ route('company.projects.index', $slug) }}" style="font-size:12px; color:var(--accent2); text-decoration:none;">View all work →</a>
                        </div>
                        <div style="padding:12px;">
                            @forelse($projects->take(5) as $project)
                            @php $progress = $project->progressPercentage(); @endphp
                            <a href="{{ route('company.projects.show', [$slug, $project]) }}" style="text-decoration:none;">
                                <div style="display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:8px; transition:background 0.15s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                    <div style="width:32px; height:32px; border-radius:8px; background:rgba(74,222,128,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                    </div>
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $project->name }}</div>
                                        <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                            <div style="flex:1; height:3px; background:var(--border); border-radius:2px; max-width:120px;">
                                                <div style="height:100%; background:#4ade80; border-radius:2px; width:{{ $progress }}%;"></div>
                                            </div>
                                            <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $progress }}%</span>
                                        </div>
                                    </div>
                                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:5px; border:1px solid; flex-shrink:0;
                                        {{ $project->status === 'completed' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                                           ($project->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                                           ($project->status === 'on_hold' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </div>
                            </a>
                            @empty
                            <div style="padding:40px; text-align:center; color:var(--muted); font-size:13px;">
                                No projects yet. <a href="{{ route('company.projects.create', $slug) }}" style="color:var(--accent2); text-decoration:none;">Create one →</a>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Recent Activity --}}
                    <div class="ptm-card">
                        <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
                            <span style="font-size:13px; font-weight:600; color:var(--text);">Recent Activity</span>
                        </div>
                        <div style="max-height:320px; overflow-y:auto;">
                            @forelse($recentActivities as $activity)
                            <div style="padding:12px 20px; border-bottom:1px solid var(--border); display:flex; gap:10px;">
                                <div style="width:28px; height:28px; border-radius:7px; background:rgba(74,222,128,0.15); color:#4ade80; font-size:11px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:13px; color:var(--text); line-height:1.5;">{{ $activity->description }}</div>
                                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $activity->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            @empty
                            <div style="padding:30px; text-align:center; color:var(--muted); font-size:13px;">No activity yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Sidebar --}}
                <div>
                    {{-- Members --}}
                    <div class="ptm-card" style="margin-bottom:16px;">
                        <div style="padding:16px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:600; color:var(--text);">Members</span>
                            <a href="#" onclick="switchTab('members'); return false;" style="font-size:12px; color:var(--accent2); text-decoration:none;">View all {{ $members->count() }} →</a>
                        </div>
                        <div style="padding:14px;">
                            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                @foreach($members->take(10) as $member)
                                <div title="{{ $member->name }}" style="position:relative;">
                                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(74,222,128,0.15); color:#4ade80; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:center; border:2px solid {{ $member->is_active ? 'rgba(74,222,128,0.3)' : 'var(--border)' }};">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    @if($member->is_active)
                                    <div style="position:absolute; bottom:-2px; right:-2px; width:10px; height:10px; border-radius:50%; background:#4ade80; border:2px solid var(--surface);"></div>
                                    @endif
                                </div>
                                @endforeach
                                @if($members->count() > 10)
                                <div style="width:36px; height:36px; border-radius:10px; background:var(--surface2); border:2px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:11px; color:var(--muted); font-weight:600;">
                                    +{{ $members->count() - 10 }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Team Stats --}}
                    <div class="ptm-card">
                        <div style="padding:16px 18px; border-bottom:1px solid var(--border);">
                            <span style="font-size:13px; font-weight:600; color:var(--text);">Team Stats</span>
                        </div>
                        <div style="padding:16px 18px; display:flex; flex-direction:column; gap:14px;">
                            @php
                                $totalTasks = $tasks->count();
                                $doneTasks = $tasks->where('status', 'done')->count();
                                $inProgressTasks = $tasks->where('status', 'in_progress')->count();
                                $overdueTasks = $tasks->filter(fn($t) => $t->due_date && $t->due_date->isPast() && $t->status !== 'done')->count();
                                $completionRate = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;
                            @endphp
                            <div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <span style="font-size:12px; color:var(--muted);">Overall Completion</span>
                                    <span style="font-size:12px; font-weight:600; color:#4ade80; font-family:var(--mono);">{{ $completionRate }}%</span>
                                </div>
                                <div style="height:6px; background:var(--border); border-radius:3px;">
                                    <div style="height:100%; background:#4ade80; border-radius:3px; width:{{ $completionRate }}%;"></div>
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <div style="padding:10px 12px; background:var(--surface2); border-radius:8px; border:1px solid var(--border);">
                                    <div style="font-size:20px; font-weight:700; color:#22d3ee;">{{ $totalTasks }}</div>
                                    <div style="font-size:11px; color:var(--muted); margin-top:2px;">Total Tasks</div>
                                </div>
                                <div style="padding:10px 12px; background:var(--surface2); border-radius:8px; border:1px solid var(--border);">
                                    <div style="font-size:20px; font-weight:700; color:#4ade80;">{{ $doneTasks }}</div>
                                    <div style="font-size:11px; color:var(--muted); margin-top:2px;">Completed</div>
                                </div>
                                <div style="padding:10px 12px; background:var(--surface2); border-radius:8px; border:1px solid var(--border);">
                                    <div style="font-size:20px; font-weight:700; color:#a78bfa;">{{ $inProgressTasks }}</div>
                                    <div style="font-size:11px; color:var(--muted); margin-top:2px;">In Progress</div>
                                </div>
                                <div style="padding:10px 12px; background:var(--surface2); border-radius:8px; border:1px solid var(--border);">
                                    <div style="font-size:20px; font-weight:700; color:{{ $overdueTasks > 0 ? '#f87171' : 'var(--muted)' }};">{{ $overdueTasks }}</div>
                                    <div style="font-size:11px; color:var(--muted); margin-top:2px;">Overdue</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MEMBERS TAB --}}
        <div id="tab-members" style="display:none;">
            @php
                $companyUsers = auth()->user()->company->users()->where('is_active', true)->orderBy('name')->get();
                $nonMembers = $companyUsers->whereNotIn('id', $members->pluck('id'))->values();
                $teamFields = $team->fields;
                $memberColspan = 3 + $teamFields->count();
                $fieldTypeMeta = [
                    'single_select' => ['label' => 'Single-select', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/>'],
                    'multi_select'  => ['label' => 'Multi-select',  'icon' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 12l2.5 2.5L16 9"/>'],
                    'date'          => ['label' => 'Date',          'icon' => '<rect x="3" y="4" width="18" height="17" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/>'],
                    'people'        => ['label' => 'People',        'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
                    'reference'     => ['label' => 'Reference',     'icon' => '<path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1"/><path d="M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/>'],
                    'text'          => ['label' => 'Text',          'icon' => '<path d="M4 6h16M4 6V4M4 6l4 0M12 6v14"/>'],
                    'number'        => ['label' => 'Number',        'icon' => '<line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>'],
                ];
            @endphp

            {{-- Toolbar --}}
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
                <button onclick="document.getElementById('addTeamMemberModal').style.display='flex'" class="ptm-btn-ghost" style="font-size:13px; display:inline-flex; align-items:center; gap:7px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add member
                </button>
                <div style="position:relative;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="memberSearch" onkeyup="filterMembers(this.value)" placeholder="Search members…" class="ptm-input" style="padding-left:32px; width:240px; font-size:13px;">
                </div>
            </div>

            {{-- Members table --}}
            <div class="ptm-card" style="overflow:hidden;">
                <table class="ptm-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="padding:11px 18px; text-align:left; width:280px;">Name</th>
                            <th style="padding:11px 18px; text-align:left;">Job title</th>
                            @foreach($teamFields as $field)
                            <th style="padding:11px 18px; text-align:left; white-space:nowrap;">
                                <div class="field-head" style="display:flex; align-items:center; gap:6px;" onmouseover="this.querySelector('.field-del').style.opacity='1'" onmouseout="this.querySelector('.field-del').style.opacity='0'">
                                    <span>{{ $field->name }}</span>
                                    <form method="POST" action="{{ route('company.teams.fields.destroy', [$slug, $team, $field]) }}" onsubmit="return confirm('Delete field “{{ addslashes($field->name) }}” and all its values?')" style="display:inline; line-height:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="field-del" title="Delete field" style="opacity:0; background:none; border:none; cursor:pointer; color:var(--muted); padding:2px; transition:opacity 0.12s, color 0.12s;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </th>
                            @endforeach
                            <th style="padding:11px 12px; text-align:left; width:52px; position:relative;">
                                <button type="button" onclick="toggleNewFieldPanel(event)" title="Add field" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; border-radius:6px; display:inline-flex; transition:color 0.12s, background 0.12s;" onmouseover="this.style.color='var(--text)'; this.style.background='var(--surface2)';" onmouseout="this.style.color='var(--muted)'; this.style.background='transparent';">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>

                                {{-- New field popover --}}
                                <div id="newFieldPanel" style="display:none; position:absolute; top:calc(100% + 6px); right:0; z-index:60; width:250px; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 12px 32px rgba(0,0,0,0.45); overflow:hidden;">
                                    <form method="POST" action="{{ route('company.teams.fields.store', [$slug, $team]) }}">
                                        @csrf
                                        <div style="padding:10px;">
                                            <input type="text" name="name" required maxlength="60" placeholder="New field" autocomplete="off" class="ptm-input" style="width:100%; font-size:13px;">
                                        </div>
                                        <div style="padding:4px 12px 6px; font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.05em;">Field types</div>
                                        <div style="max-height:260px; overflow-y:auto; padding:0 6px 8px;">
                                            @foreach($fieldTypeMeta as $type => $meta)
                                            <label class="field-type-opt" style="display:flex; align-items:center; gap:11px; padding:8px 10px; border-radius:7px; cursor:pointer; transition:background 0.1s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="if(!this.querySelector('input').checked)this.style.background='transparent'">
                                                <input type="radio" name="type" value="{{ $type }}" onchange="selectFieldType(this)" style="display:none;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.8" style="flex-shrink:0;">{!! $meta['icon'] !!}</svg>
                                                <span style="font-size:13px; color:var(--text);">{{ $meta['label'] }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                        <div id="fieldOptionsWrap" style="display:none; padding:2px 12px 10px;">
                                            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">Option labels</div>
                                            <div id="fieldOptionRows" style="display:flex; flex-direction:column; gap:6px;">
                                                <div class="field-option-row" style="display:flex; align-items:center; gap:6px;">
                                                    <input type="text" name="options[]" maxlength="60" placeholder="Option label" autocomplete="off" class="ptm-input" style="flex:1; font-size:12px;">
                                                    <button type="button" onclick="removeOptionRow(this)" title="Remove option" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; display:inline-flex;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="button" onclick="addOptionRow()" style="margin-top:8px; background:none; border:none; cursor:pointer; color:var(--accent2); font-size:12px; display:inline-flex; align-items:center; gap:5px; padding:2px 0;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                Add option
                                            </button>
                                        </div>
                                        <div style="padding:10px 12px; border-top:1px solid var(--border); display:flex; gap:8px;">
                                            <button type="submit" class="ptm-btn-primary" style="font-size:12px; padding:7px 12px;">Create field</button>
                                            <button type="button" onclick="toggleNewFieldPanel(event, false)" class="ptm-btn-ghost" style="font-size:12px; padding:7px 12px;">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="memberTableBody">
                        @forelse($members as $member)
                        <tr class="member-row" data-name="{{ strtolower($member->name) }}" style="border-bottom:1px solid var(--border); transition:background 0.1s;" onmouseover="this.style.background='var(--surface2)'; this.querySelector('.row-remove').style.opacity='1';" onmouseout="this.style.background='transparent'; this.querySelector('.row-remove').style.opacity='0';">
                            <td style="padding:11px 18px;">
                                <div style="display:flex; align-items:center; gap:11px;">
                                    <div style="position:relative; flex-shrink:0;">
                                        <div style="width:30px; height:30px; border-radius:50%; background:rgba(74,222,128,0.12); color:#4ade80; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center;">
                                            {{ strtoupper(substr($member->name,0,1)) }}
                                        </div>
                                        @if($member->is_active)
                                        <div style="position:absolute; bottom:-1px; right:-1px; width:9px; height:9px; border-radius:50%; background:#4ade80; border:2px solid var(--surface);"></div>
                                        @endif
                                    </div>
                                    <div style="min-width:0;">
                                        <div style="font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $member->name }}</div>
                                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:11px 18px;">
                                <input type="text" class="job-title-input"
                                       value="{{ $member->pivot->job_title }}"
                                       placeholder="Add job title…"
                                       data-url="{{ route('company.teams.members.title', [$slug, $team, $member]) }}"
                                       onblur="saveJobTitle(this)"
                                       onkeydown="if(event.key==='Enter'){this.blur();}"
                                       style="width:100%; background:transparent; border:1px solid transparent; border-radius:6px; padding:5px 8px; font-size:13px; color:var(--text); font-family:var(--font); transition:border 0.12s, background 0.12s;"
                                       onfocus="this.style.borderColor='var(--border2)'; this.style.background='var(--surface2)';"
                                       onmouseover="if(document.activeElement!==this)this.style.borderColor='var(--border)';"
                                       onmouseout="if(document.activeElement!==this)this.style.borderColor='transparent';">
                            </td>
                            @php
                                $mv = $member->pivot->field_values ? json_decode($member->pivot->field_values, true) : [];
                                if (!is_array($mv)) $mv = [];
                                $valueUrl = route('company.teams.members.field', [$slug, $team, $member]);
                            @endphp
                            @foreach($teamFields as $field)
                            @php $val = $mv[$field->id] ?? null; @endphp
                            <td style="padding:8px 12px;">
                                @switch($field->type)
                                    @case('number')
                                        <input type="number" value="{{ $val }}" data-url="{{ $valueUrl }}" data-field="{{ $field->id }}" onblur="saveFieldValue(this)" onkeydown="if(event.key==='Enter')this.blur()" class="ptm-cell-input" style="width:110px;" placeholder="—">
                                        @break
                                    @case('date')
                                        <input type="date" value="{{ $val }}" data-url="{{ $valueUrl }}" data-field="{{ $field->id }}" onchange="saveFieldValue(this)" class="ptm-cell-input" style="width:150px;">
                                        @break
                                    @case('single_select')
                                        <select data-url="{{ $valueUrl }}" data-field="{{ $field->id }}" onchange="saveFieldValue(this)" class="ptm-cell-input" style="min-width:130px;">
                                            <option value="">—</option>
                                            @foreach(($field->options ?? []) as $opt)
                                            <option value="{{ $opt }}" {{ $val === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @case('multi_select')
                                        @php $selected = is_array($val) ? $val : (array) $val; @endphp
                                        <select multiple data-url="{{ $valueUrl }}" data-field="{{ $field->id }}" onchange="saveFieldValue(this)" class="ptm-cell-input" style="min-width:150px; height:auto; min-height:34px;">
                                            @foreach(($field->options ?? []) as $opt)
                                            <option value="{{ $opt }}" {{ in_array($opt, $selected) ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @case('people')
                                        <select data-url="{{ $valueUrl }}" data-field="{{ $field->id }}" onchange="saveFieldValue(this)" class="ptm-cell-input" style="min-width:150px;">
                                            <option value="">—</option>
                                            @foreach($companyUsers as $cu)
                                            <option value="{{ $cu->id }}" {{ (string) $val === (string) $cu->id ? 'selected' : '' }}>{{ $cu->name }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @default
                                        {{-- text & reference --}}
                                        <input type="text" value="{{ is_array($val) ? implode(', ', $val) : $val }}" data-url="{{ $valueUrl }}" data-field="{{ $field->id }}" onblur="saveFieldValue(this)" onkeydown="if(event.key==='Enter')this.blur()" class="ptm-cell-input" style="width:100%; min-width:140px;" placeholder="—">
                                @endswitch
                            </td>
                            @endforeach
                            <td style="padding:11px 12px; text-align:right;">
                                <form method="POST" action="{{ route('company.teams.members.remove', [$slug, $team, $member]) }}" onsubmit="return confirm('Remove {{ addslashes($member->name) }} from this team?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="row-remove" title="Remove from team" style="opacity:0; background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; border-radius:6px; transition:opacity 0.12s, color 0.12s;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr id="memberEmptyRow"><td colspan="{{ $memberColspan }}" style="padding:48px; text-align:center; color:var(--muted); font-size:13px;">No members in this team yet.</td></tr>
                        @endforelse
                        <tr id="memberNoResults" style="display:none;"><td colspan="{{ $memberColspan }}" style="padding:40px; text-align:center; color:var(--muted); font-size:13px;">No members match your search.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add Team Member Modal --}}
        <div id="addTeamMemberModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:200; align-items:center; justify-content:center; padding:20px;">
            <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:460px;">
                <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:15px; font-weight:600; color:var(--text);">Add member to team</span>
                    <button onclick="document.getElementById('addTeamMemberModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px;">✕</button>
                </div>
                @if($nonMembers->isEmpty())
                <div style="padding:32px 22px; text-align:center; color:var(--muted); font-size:13px;">
                    Everyone in your company is already on this team.
                </div>
                @else
                <form method="POST" action="{{ route('company.teams.members.add', [$slug, $team]) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                    @csrf
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">SELECT MEMBERS</label>
                        <div style="background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:8px; max-height:280px; overflow-y:auto;">
                            @foreach($nonMembers as $u)
                            <label style="display:flex; align-items:center; gap:10px; padding:7px 8px; border-radius:6px; cursor:pointer;" onmouseover="this.style.background='var(--surface)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="members[]" value="{{ $u->id }}" style="width:16px; height:16px; cursor:pointer;">
                                <div style="width:28px; height:28px; border-radius:50%; background:rgba(74,222,128,0.15); color:#4ade80; font-size:11px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                                <div style="min-width:0;">
                                    <div style="font-size:13px; color:var(--text);">{{ $u->name }}</div>
                                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $u->email }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; padding-top:4px;">
                        <button type="submit" class="ptm-btn-primary">Add to team</button>
                        <button type="button" onclick="document.getElementById('addTeamMemberModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
                    </div>
                </form>
                @endif
            </div>
        </div>

        {{-- ALL WORK TAB --}}
        <div id="tab-work" style="display:none;">
            @php
                $iconPalette = ['#d946ef','#a855f7','#8b5cf6','#3b82f6','#0ea5e9','#14b8a6','#22c55e','#f472b6','#6366f1','#ec4899'];
                $avatarPalette = ['#4ade80','#22d3ee','#a78bfa','#fbbf24','#f472b6','#60a5fa','#f87171','#34d399'];
                $projectInitialLimit = 12;
            @endphp
            <div style="display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:20px; align-items:start;">

                {{-- Projects list --}}
                <div class="ptm-card" style="overflow:hidden;">
                    <div style="padding:16px 20px 12px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <span style="font-size:15px; font-weight:700; color:var(--text); letter-spacing:-0.3px;">Projects</span>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="position:relative;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" onkeyup="filterProjects(this.value)" placeholder="Search…" class="ptm-input" style="padding-left:29px; width:150px; font-size:12px; height:32px;">
                            </div>
                            <a href="{{ route('company.projects.create', $slug) }}" class="ptm-btn-ghost" style="font-size:12px; padding:7px 12px; text-decoration:none; white-space:nowrap;">New project</a>
                        </div>
                    </div>

                    {{-- Column header --}}
                    <div style="display:flex; align-items:center; padding:8px 20px; border-bottom:1px solid var(--border); font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.05em;">
                        <span style="flex:1;">Name</span>
                        <span style="width:150px;">Members</span>
                        <span style="width:70px; display:inline-flex; align-items:center; gap:4px; justify-content:flex-end;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h13M3 12h9M3 18h5M17 8l3-3 3 3M20 5v14"/></svg>
                            A–Z
                        </span>
                    </div>

                    <div id="projectList">
                        @forelse($projects as $i => $project)
                        @php
                            $isMain = \Illuminate\Support\Str::startsWith(strtolower($project->name), 'main');
                            $ic = $isMain ? '#6b7280' : $iconPalette[$i % count($iconPalette)];
                            $pm = $projectMembers[$project->id] ?? collect();
                        @endphp
                        <a href="{{ route('company.projects.show', [$slug, $project]) }}" class="project-row" data-name="{{ strtolower($project->name) }}" style="display:flex; align-items:center; padding:11px 20px; border-bottom:1px solid var(--border); text-decoration:none; transition:background 0.12s; {{ $i >= $projectInitialLimit ? 'display:none;' : '' }}" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                            {{-- Icon --}}
                            <div style="width:36px; height:36px; border-radius:9px; background:{{ $ic }}; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-right:14px;">
                                @if($isMain)
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                                @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3.5" cy="6" r="1.2" fill="#fff"/><circle cx="3.5" cy="12" r="1.2" fill="#fff"/><circle cx="3.5" cy="18" r="1.2" fill="#fff"/></svg>
                                @endif
                            </div>
                            {{-- Name --}}
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:14px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $project->name }}</div>
                                @if($pm->contains('id', auth()->id()))
                                <div style="font-size:11px; color:#4ade80; margin-top:1px;">Joined</div>
                                @endif
                            </div>
                            {{-- Members --}}
                            <div style="width:150px; display:flex; align-items:center;">
                                @if($pm->isEmpty())
                                <span style="font-size:12px; color:var(--muted);">—</span>
                                @else
                                <div style="display:flex; align-items:center;">
                                    @foreach($pm->take(4) as $j => $u)
                                    <div title="{{ $u->name }}" style="width:26px; height:26px; border-radius:50%; background:{{ $avatarPalette[$u->id % count($avatarPalette)] }}; color:#0b0e17; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; border:2px solid var(--surface); margin-left:{{ $j === 0 ? '0' : '-9px' }};">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    @endforeach
                                    @if($pm->count() > 4)
                                    <div style="width:26px; height:26px; border-radius:50%; background:var(--surface2); border:2px solid var(--surface); color:var(--muted); font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center; margin-left:-9px;">+{{ $pm->count() - 4 }}</div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            {{-- trailing dots --}}
                            <div style="width:70px; text-align:right; color:var(--muted);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>
                            </div>
                        </a>
                        @empty
                        <div id="projectEmpty" style="padding:44px; text-align:center; color:var(--muted); font-size:13px;">
                            No projects yet. <a href="{{ route('company.projects.create', $slug) }}" style="color:var(--accent2); text-decoration:none;">Create one →</a>
                        </div>
                        @endforelse
                        <div id="projectNoResults" style="display:none; padding:36px; text-align:center; color:var(--muted); font-size:13px;">No projects match your search.</div>
                    </div>

                    @if($projects->count() > $projectInitialLimit)
                    <div style="padding:12px 20px;">
                        <button type="button" id="projectShowMore" onclick="toggleShowMoreProjects(this)" style="background:none; border:none; cursor:pointer; color:var(--muted); font-size:13px; font-weight:500;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">Show more</button>
                    </div>
                    @endif
                </div>

                {{-- Templates --}}
                <div class="ptm-card" style="padding:16px;">
                    <div style="font-size:15px; font-weight:700; color:var(--text); letter-spacing:-0.3px; margin-bottom:14px;">Templates</div>

                    <a href="{{ route('company.projects.create', $slug) }}" style="display:flex; align-items:center; gap:12px; padding:12px; border:1.5px dashed var(--border2); border-radius:10px; text-decoration:none; margin-bottom:10px; transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--accent2)'" onmouseout="this.style.borderColor='var(--border2)'">
                        <div style="width:34px; height:34px; border-radius:9px; border:1.5px dashed var(--border2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <span style="font-size:13px; color:var(--muted);">New Template</span>
                    </a>

                    <a href="{{ route('company.projects.index', $slug) }}" style="display:flex; align-items:center; gap:12px; padding:12px; border:1.5px dashed var(--border2); border-radius:10px; text-decoration:none; margin-bottom:16px; transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--accent2)'" onmouseout="this.style.borderColor='var(--border2)'">
                        <div style="width:34px; height:34px; border-radius:9px; border:1.5px dashed var(--border2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h6"/></svg>
                        </div>
                        <span style="font-size:13px; color:var(--muted);">Explore all templates</span>
                    </a>

                    @foreach(['Project Timeline' => '#3b82f6', 'Client Technical Board' => '#14b8a6', 'Client Board' => '#a855f7'] as $tpl => $tc)
                    <a href="{{ route('company.projects.create', $slug) }}" style="display:flex; align-items:center; gap:12px; padding:10px 6px; border-radius:9px; text-decoration:none; transition:background 0.12s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                        <div style="width:34px; height:34px; border-radius:9px; background:{{ $tc }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="9" x2="9" y2="20"/></svg>
                        </div>
                        <span style="font-size:13px; font-weight:500; color:var(--text);">{{ $tpl }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- MESSAGES TAB --}}
        <div id="tab-messages" style="display:none;">
            <div style="max-width:720px; margin:0 auto;">
                {{-- Composer --}}
                <form method="POST" action="{{ route('company.teams.messages.store', [$slug, $team]) }}" class="ptm-card" style="padding:14px 16px; display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                    @csrf
                    <div style="width:32px; height:32px; border-radius:50%; background:rgba(74,222,128,0.15); color:#4ade80; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <input type="text" name="body" required maxlength="5000" autocomplete="off" placeholder="Send message to members" class="ptm-input" style="flex:1; font-size:13px;">
                    <button type="submit" class="ptm-btn-primary" style="font-size:12px; padding:8px 14px;">Send</button>
                </form>

                @if($messages->isEmpty())
                {{-- Empty state --}}
                <div class="ptm-card" style="padding:44px 24px; text-align:center;">
                    <div style="width:130px; height:100px; margin:0 auto 18px; position:relative;">
                        <div style="position:absolute; left:8px; top:6px; width:78px; height:52px; background:#f3d5e0; border-radius:16px;"></div>
                        <div style="position:absolute; right:6px; top:20px; width:52px; height:40px; background:#f87171; border-radius:14px;"></div>
                        <div style="position:absolute; left:22px; bottom:0; width:64px; height:44px; background:#f9c9d8; border-radius:14px;"></div>
                    </div>
                    <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:8px;">Connect your words to your work</div>
                    <div style="font-size:13px; color:var(--muted); line-height:1.6; max-width:440px; margin:0 auto;">
                        Send a message to kick off projects. Or discuss tasks. Or brainstorm ideas with your team.
                    </div>
                </div>
                @else
                {{-- Message list --}}
                <div id="messagesScroll" class="ptm-card" style="padding:8px; max-height:520px; overflow-y:auto;">
                    @foreach($messages as $msg)
                    <div style="display:flex; gap:12px; padding:11px 12px; border-radius:10px;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                        <div style="width:34px; height:34px; border-radius:50%; background:rgba(74,222,128,0.15); color:#4ade80; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            {{ strtoupper(substr($msg->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div style="min-width:0; flex:1;">
                            <div style="display:flex; align-items:baseline; gap:8px;">
                                <span style="font-size:13px; font-weight:600; color:var(--text);">{{ $msg->user->name ?? 'Unknown' }}</span>
                                <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="font-size:13px; color:var(--text); line-height:1.55; margin-top:2px; white-space:pre-wrap; word-break:break-word;">{{ $msg->body }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- CALENDAR TAB --}}
        <div id="tab-calendar" style="display:none;">
            @php
                $monthParam = request('month');
                try { $cursor = $monthParam ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth() : \Carbon\Carbon::now()->startOfMonth(); }
                catch (\Exception $e) { $cursor = \Carbon\Carbon::now()->startOfMonth(); }

                $weekendsOn = request('weekends') === 'on';
                $prevMonth  = $cursor->copy()->subMonth()->format('Y-m');
                $nextMonth  = $cursor->copy()->addMonth()->format('Y-m');
                $today      = \Carbon\Carbon::now()->toDateString();
                $monthNum   = $cursor->month;

                $gridStart = $cursor->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
                $gridEnd   = $cursor->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $days      = \Carbon\CarbonPeriod::create($gridStart, $gridEnd)->toArray();

                $tasksByDate = $tasks->filter(fn($t) => $t->due_date)
                    ->groupBy(fn($t) => $t->due_date->toDateString());

                // Consistent pill colour per project (fallback per task).
                $pillPalette = ['#4a8f6a','#9c6b4a','#c96b98','#4a7fc0','#8b6fc0','#c08348','#3f9a9a','#b39240','#a05a6f','#5f7d9c','#6a8f4a','#b3563c'];
                $pillColor = fn($t) => $pillPalette[(($t->project_id ?? $t->id)) % count($pillPalette)];
                $statusHex = ['done'=>'#4ade80','in_progress'=>'#22d3ee','in_review'=>'#a78bfa'];
                $prioHex   = ['urgent'=>'#f87171','high'=>'#fb923c','medium'=>'#fbbf24'];

                $gridCols = $weekendsOn
                    ? 'repeat(7, minmax(150px, 1fr))'
                    : 'repeat(5, minmax(150px, 1fr)) 46px 46px';
            @endphp

            <div style="margin:-4px -8px 0;">
                {{-- Toolbar --}}
                <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 8px 14px;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div style="display:flex; align-items:center; gap:2px;">
                            <a href="{{ route('company.team.overview', ['slug'=>$slug,'team'=>$team,'tab'=>'calendar','month'=>$prevMonth,'weekends'=>$weekendsOn?'on':null]) }}" class="cal-navbtn" title="Previous month">‹</a>
                            <a href="{{ route('company.team.overview', ['slug'=>$slug,'team'=>$team,'tab'=>'calendar','weekends'=>$weekendsOn?'on':null]) }}" class="cal-navbtn" style="width:auto; padding:0 12px; font-size:13px;">Today</a>
                            <a href="{{ route('company.team.overview', ['slug'=>$slug,'team'=>$team,'tab'=>'calendar','month'=>$nextMonth,'weekends'=>$weekendsOn?'on':null]) }}" class="cal-navbtn" title="Next month">›</a>
                        </div>
                        <span style="font-size:15px; font-weight:600; color:var(--text);">{{ $cursor->format('F Y') }}</span>
                    </div>
                    <a href="{{ route('company.team.overview', ['slug'=>$slug,'team'=>$team,'tab'=>'calendar','month'=>$cursor->format('Y-m'),'weekends'=>$weekendsOn?null:'on']) }}" style="font-size:13px; color:var(--muted); text-decoration:none;">
                        Weekends: <span style="color:{{ $weekendsOn ? '#4ade80' : 'var(--text)' }}; font-weight:600;">{{ $weekendsOn ? 'On' : 'Off' }}</span>
                    </a>
                </div>

                <div style="overflow-x:auto; border-top:1px solid var(--border); border-left:1px solid var(--border);">
                    {{-- Weekday header --}}
                    <div style="display:grid; grid-template-columns:{{ $gridCols }};">
                        @foreach(['MON','TUE','WED','THU','FRI','SAT','SUN'] as $dow)
                        <div style="padding:7px 10px; font-size:10px; color:var(--muted); font-family:var(--mono); letter-spacing:0.06em; border-right:1px solid var(--border); border-bottom:1px solid var(--border);">{{ $dow }}</div>
                        @endforeach
                    </div>

                    {{-- Day cells --}}
                    <div style="display:grid; grid-template-columns:{{ $gridCols }};">
                        @foreach($days as $date)
                        @php
                            $dateStr    = $date->toDateString();
                            $dayTasks   = $tasksByDate[$dateStr] ?? collect();
                            $isToday    = $dateStr === $today;
                            $inMonth    = $date->month === $monthNum;
                            $isWeekend  = $date->isWeekend();
                            $compact    = $isWeekend && ! $weekendsOn;
                        @endphp

                        @if($compact)
                        {{-- Narrow weekend column --}}
                        <div style="min-height:150px; padding:6px 4px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); background:rgba(255,255,255,0.015); {{ $inMonth ? '' : 'opacity:0.45;' }}">
                            <div style="font-size:12px; font-family:var(--mono); color:var(--muted); text-align:center; margin-bottom:6px;">{{ $date->day }}</div>
                            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                                @foreach($dayTasks->take(6) as $t)
                                <span title="{{ $t->title }}" style="width:7px; height:7px; border-radius:50%; background:{{ $pillColor($t) }};"></span>
                                @endforeach
                            </div>
                        </div>
                        @else
                        {{-- Full day cell --}}
                        <div style="min-height:150px; padding:5px 6px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); {{ $isToday ? 'background:rgba(74,127,192,0.06);' : '' }} {{ $inMonth ? '' : 'background:rgba(255,255,255,0.015);' }}">
                            <div style="margin-bottom:5px;">
                                @if($isToday)
                                <span style="display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:20px; padding:0 5px; border-radius:10px; background:#3b82f6; color:#fff; font-size:12px; font-weight:700;">{{ $date->day }}</span>
                                @else
                                <span style="font-size:12px; font-family:var(--mono); color:{{ $inMonth ? 'var(--muted)' : 'var(--border2)' }};">{{ $date->day }}</span>
                                @endif
                            </div>

                            @foreach($dayTasks->take(4) as $t)
                            @php
                                $assignee = $t->assignees->first() ?? $t->assignee;
                                $subtitle = $t->section->name ?? ($t->project->name ?? null);
                                $bg = $pillColor($t);
                            @endphp
                            <div onclick="openPanel({{ $t->id }})" title="{{ $t->title }}" style="cursor:pointer; display:flex; align-items:center; gap:5px; margin-bottom:3px; padding:3px 5px; border-radius:5px; background:{{ $bg }}; overflow:hidden;">
                                {{-- avatar --}}
                                @if($assignee)
                                <span title="{{ $assignee->name }}" style="width:16px; height:16px; border-radius:50%; background:rgba(0,0,0,0.25); color:#fff; font-size:9px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ strtoupper(substr($assignee->name,0,1)) }}</span>
                                @endif
                                {{-- check --}}
                                @if($t->status === 'done')
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="9"/></svg>
                                @endif
                                {{-- title + subtitle --}}
                                <span style="flex:1; min-width:0;">
                                    <span style="display:block; font-size:11px; color:#fff; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.3;">{{ $t->title }}</span>
                                    @if($subtitle)
                                    <span style="display:block; font-size:9px; color:rgba(255,255,255,0.72); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.2;">· {{ $subtitle }}</span>
                                    @endif
                                </span>
                                {{-- field squares --}}
                                <span style="display:flex; gap:2px; flex-shrink:0;">
                                    @if(isset($statusHex[$t->status]))
                                    <span style="width:9px; height:9px; border-radius:2px; background:{{ $statusHex[$t->status] }};"></span>
                                    @endif
                                    @if(isset($prioHex[$t->priority]))
                                    <span style="width:9px; height:9px; border-radius:2px; background:{{ $prioHex[$t->priority] }};"></span>
                                    @endif
                                </span>
                            </div>
                            @endforeach

                            @if($dayTasks->count() > 4)
                            @php $more = $dayTasks->count() - 4; @endphp
                            <div style="font-size:11px; color:var(--muted); padding:2px 5px; cursor:default;">{{ $more > 9 ? '9+' : $more }} more</div>
                            @endif
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- KNOWLEDGE TAB --}}
        <div id="tab-knowledge" style="display:none;">
            @if($docs->isEmpty())
            <div class="ptm-card" style="padding:56px 24px; text-align:center; max-width:560px; margin:24px auto 0;">
                <div style="width:56px; height:56px; border-radius:14px; background:rgba(167,139,250,0.12); display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                </div>
                <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:8px;">Build your team's knowledge base</div>
                <div style="font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:20px;">
                    Keep project docs, guidelines, and team resources in one place so everyone stays aligned.
                </div>
                <button onclick="document.getElementById('newDocModal').style.display='flex'" class="ptm-btn-primary" style="font-size:13px; display:inline-flex; align-items:center; gap:7px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create doc
                </button>
            </div>
            @else
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <span style="font-size:15px; font-weight:700; color:var(--text);">Knowledge <span style="color:var(--muted); font-family:var(--mono); font-size:13px;">{{ $docs->count() }}</span></span>
                <button onclick="document.getElementById('newDocModal').style.display='flex'" class="ptm-btn-primary" style="font-size:13px; display:inline-flex; align-items:center; gap:7px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New doc
                </button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:14px;">
                @foreach($docs as $doc)
                <div class="ptm-card" style="padding:16px; cursor:pointer; transition:border-color 0.12s;" onclick="openDoc({{ $doc->id }})" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:9px; background:rgba(167,139,250,0.12); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:14px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $doc->title }}</div>
                            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $doc->user->name ?? '—' }} · {{ $doc->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @if($doc->content)
                    <div style="font-size:12px; color:var(--muted); line-height:1.5; margin-top:10px; max-height:54px; overflow:hidden;">{{ \Illuminate\Support\Str::limit(strip_tags($doc->content), 120) }}</div>
                    @endif
                    {{-- hidden full content for the viewer --}}
                    <div id="doc-content-{{ $doc->id }}" data-title="{{ e($doc->title) }}" style="display:none;">{{ $doc->content }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- New Doc Modal --}}
        <div id="newDocModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:210; align-items:center; justify-content:center; padding:20px;">
            <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:560px;">
                <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:15px; font-weight:600; color:var(--text);">Create doc</span>
                    <button onclick="document.getElementById('newDocModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px;">✕</button>
                </div>
                <form method="POST" action="{{ route('company.teams.docs.store', [$slug, $team]) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                    @csrf
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TITLE *</label>
                        <input type="text" name="title" required maxlength="255" class="ptm-input" style="width:100%;" placeholder="e.g. Onboarding guide">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">CONTENT</label>
                        <textarea name="content" rows="8" class="ptm-input" style="width:100%; resize:vertical; line-height:1.6;" placeholder="Write your doc…"></textarea>
                    </div>
                    <div style="display:flex; gap:10px; padding-top:4px;">
                        <button type="submit" class="ptm-btn-primary">Create doc</button>
                        <button type="button" onclick="document.getElementById('newDocModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Doc Viewer Modal --}}
        <div id="docViewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:210; align-items:center; justify-content:center; padding:20px;">
            <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:640px; max-height:85vh; display:flex; flex-direction:column;">
                <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <span id="docViewTitle" style="font-size:16px; font-weight:700; color:var(--text); min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></span>
                    <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        <form id="docDeleteForm" method="POST" action="" onsubmit="return confirm('Delete this doc?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" title="Delete doc" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:5px; display:flex;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            </button>
                        </form>
                        <button onclick="document.getElementById('docViewModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px;">✕</button>
                    </div>
                </div>
                <div id="docViewBody" style="padding:22px; overflow-y:auto; font-size:14px; color:var(--text); line-height:1.7; white-space:pre-wrap; word-break:break-word;"></div>
            </div>
        </div>

        {{-- NOTE TABS --}}
        @foreach($notes as $note)
        <div id="tab-note-{{ $note->id }}" style="display:none;">
            <div class="note-wrap" data-url="{{ route('company.teams.notes.update', [$slug, $team, $note]) }}">
                {{-- Toolbar --}}
                <div class="note-toolbar">
                    <button type="button" title="Bold" onclick="noteCmd(this,'bold')"><b>B</b></button>
                    <button type="button" title="Italic" onclick="noteCmd(this,'italic')"><i>I</i></button>
                    <button type="button" title="Underline" onclick="noteCmd(this,'underline')"><u>U</u></button>
                    <button type="button" title="Strikethrough" onclick="noteCmd(this,'strikeThrough')"><s>S</s></button>
                    <span class="note-sep"></span>
                    <button type="button" title="Heading" onclick="noteCmd(this,'formatBlock','H2')">H</button>
                    <button type="button" title="Bulleted list" onclick="noteCmd(this,'insertUnorderedList')">•</button>
                    <button type="button" title="Numbered list" onclick="noteCmd(this,'insertOrderedList')">1.</button>
                    <button type="button" title="Quote" onclick="noteCmd(this,'formatBlock','BLOCKQUOTE')">❝</button>
                    <button type="button" title="Link" onclick="noteAddLink(this)">🔗</button>
                    <button type="button" title="Clear formatting" onclick="noteCmd(this,'removeFormat')">⌫</button>
                    <span class="note-sep"></span>
                    <span class="note-status" style="font-size:11px; color:var(--muted); font-family:var(--mono);">Saved</span>
                    <form method="POST" action="{{ route('company.teams.notes.destroy', [$slug, $team, $note]) }}" onsubmit="return confirm('Delete this note?')" style="margin-left:auto; display:inline; line-height:0;">
                        @csrf @method('DELETE')
                        <button type="submit" title="Delete note" style="color:var(--muted);" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                        </button>
                    </form>
                </div>

                <div style="max-width:760px; margin:0 auto; padding:40px 24px;">
                    <input type="text" class="note-title" value="{{ $note->title }}" placeholder="Untitled Note" maxlength="255" oninput="scheduleNoteSave(this)">
                    <div class="note-editor" contenteditable="true" data-placeholder="Start typing…" oninput="scheduleNoteSave(this)" onblur="saveNote(this)">{!! $note->content !!}</div>

                    @if(empty($note->content) && empty($note->title))
                    <div class="note-templates">
                        <button type="button" onclick="applyNoteTemplate(this,'meeting')">🗒️ Meeting notes</button>
                        <button type="button" onclick="applyNoteTemplate(this,'resources')">🔗 Key resources</button>
                        <button type="button" onclick="applyNoteTemplate(this,'planning')">📅 Weekly planning</button>
                        <button type="button" onclick="applyNoteTemplate(this,'blank')">📄 Blank note</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

    </div>

    <script>
    function switchTab(tab) {
        document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
            const t = btn.id.replace('tab-btn-', '');
            const panel = document.getElementById('tab-' + t);
            const active = t === tab;
            if (panel) panel.style.display = active ? 'block' : 'none';
            btn.style.borderBottomColor = active ? '#4ade80' : 'transparent';
            btn.style.color = active ? '#4ade80' : 'var(--muted)';
        });
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        history.replaceState(null, '', url);
        if (tab === 'messages') scrollMessagesToBottom();
    }
    function scrollMessagesToBottom() {
        const box = document.getElementById('messagesScroll');
        if (box) box.scrollTop = box.scrollHeight;
    }

    // "+" add-view menu
    function toggleAddTabMenu(e) {
        e.stopPropagation();
        const m = document.getElementById('addTabMenu');
        m.style.display = m.style.display === 'none' ? 'block' : 'none';
    }
    function closeAddTabMenu() {
        const m = document.getElementById('addTabMenu');
        if (m) m.style.display = 'none';
    }
    document.addEventListener('click', function (e) {
        const m = document.getElementById('addTabMenu');
        if (m && m.style.display !== 'none' && !e.target.closest('#addTabMenu') && !e.target.closest('[onclick*="toggleAddTabMenu"]')) {
            m.style.display = 'none';
        }
    });

    // New field popover
    function toggleNewFieldPanel(e, show) {
        e.stopPropagation();
        const panel = document.getElementById('newFieldPanel');
        panel.style.display = (show === false) ? 'none' : (panel.style.display === 'none' ? 'block' : 'none');
    }
    function selectFieldType(radio) {
        document.querySelectorAll('.field-type-opt').forEach(o => {
            o.style.background = o.querySelector('input').checked ? 'var(--surface2)' : 'transparent';
        });
        const needsOptions = ['single_select', 'multi_select'].includes(radio.value);
        document.getElementById('fieldOptionsWrap').style.display = needsOptions ? 'block' : 'none';
    }
    function addOptionRow() {
        const rows = document.getElementById('fieldOptionRows');
        const row = document.createElement('div');
        row.className = 'field-option-row';
        row.style.cssText = 'display:flex; align-items:center; gap:6px;';
        row.innerHTML = '<input type="text" name="options[]" maxlength="60" placeholder="Option label" autocomplete="off" class="ptm-input" style="flex:1; font-size:12px;">' +
            '<button type="button" onclick="removeOptionRow(this)" title="Remove option" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; display:inline-flex;" onmouseover="this.style.color=\'#f87171\'" onmouseout="this.style.color=\'var(--muted)\'"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
        rows.appendChild(row);
        row.querySelector('input').focus();
    }
    function removeOptionRow(btn) {
        const rows = document.getElementById('fieldOptionRows');
        if (rows.querySelectorAll('.field-option-row').length > 1) {
            btn.closest('.field-option-row').remove();
        } else {
            btn.closest('.field-option-row').querySelector('input').value = '';
        }
    }
    document.addEventListener('click', function (e) {
        const panel = document.getElementById('newFieldPanel');
        if (panel && panel.style.display !== 'none' && !panel.contains(e.target)) {
            panel.style.display = 'none';
        }
    });

    // Custom field value save (AJAX)
    function saveFieldValue(el) {
        let value;
        if (el.multiple) {
            value = Array.from(el.selectedOptions).map(o => o.value);
        } else {
            value = el.value;
        }
        el.style.borderColor = 'var(--border2)';
        fetch(el.dataset.url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ field_id: el.dataset.field, value: value }),
        })
        .then(r => { if (!r.ok) throw new Error('save failed'); return r.json(); })
        .then(() => { el.style.borderColor = 'transparent'; })
        .catch(() => { el.style.borderColor = '#f87171'; });
    }

    function filterMembers(query) {
        query = query.trim().toLowerCase();
        let visible = 0;
        document.querySelectorAll('#memberTableBody .member-row').forEach(row => {
            const match = row.dataset.name.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const noResults = document.getElementById('memberNoResults');
        if (noResults) noResults.style.display = (visible === 0 && query !== '') ? '' : 'none';
    }

    // Projects list (All Work tab)
    const PROJECT_LIMIT = {{ $projectInitialLimit ?? 12 }};
    let projectsExpanded = false;
    function applyProjectVisibility() {
        document.querySelectorAll('#projectList .project-row').forEach((row, idx) => {
            row.style.display = (projectsExpanded || idx < PROJECT_LIMIT) ? 'flex' : 'none';
        });
    }
    function toggleShowMoreProjects(btn) {
        projectsExpanded = !projectsExpanded;
        applyProjectVisibility();
        btn.textContent = projectsExpanded ? 'Show less' : 'Show more';
    }
    function filterProjects(q) {
        q = q.trim().toLowerCase();
        const noResults = document.getElementById('projectNoResults');
        if (q === '') {
            applyProjectVisibility();
            if (noResults) noResults.style.display = 'none';
            return;
        }
        let visible = 0;
        document.querySelectorAll('#projectList .project-row').forEach(row => {
            const match = row.dataset.name.includes(q);
            row.style.display = match ? 'flex' : 'none';
            if (match) visible++;
        });
        if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
    }

    // Knowledge docs
    const DOC_DESTROY_BASE = "{{ url("/{$slug}/admin/teams/{$team->id}/docs") }}";
    function openDoc(id) {
        const holder = document.getElementById('doc-content-' + id);
        document.getElementById('docViewTitle').textContent = holder.dataset.title;
        const content = holder.textContent.trim();
        document.getElementById('docViewBody').textContent = content || 'No content yet.';
        document.getElementById('docDeleteForm').action = DOC_DESTROY_BASE + '/' + id;
        document.getElementById('docViewModal').style.display = 'flex';
    }

    // Team notes rich editor
    const noteTimers = {};
    function noteWrap(el){ return el.closest('.note-wrap'); }
    function noteCmd(btn, cmd, val){
        const editor = noteWrap(btn).querySelector('.note-editor');
        editor.focus();
        document.execCommand(cmd, false, val || null);
        scheduleNoteSave(editor);
    }
    function noteAddLink(btn){
        const url = prompt('Link URL:');
        if (url) noteCmd(btn, 'createLink', url);
    }
    function scheduleNoteSave(el){
        const wrap = noteWrap(el);
        const status = wrap.querySelector('.note-status');
        if (status) status.textContent = 'Saving…';
        const key = wrap.dataset.url;
        clearTimeout(noteTimers[key]);
        noteTimers[key] = setTimeout(() => saveNote(el), 700);
    }
    function saveNote(el){
        const wrap = noteWrap(el);
        const key = wrap.dataset.url;
        clearTimeout(noteTimers[key]);
        const title = wrap.querySelector('.note-title').value;
        const content = wrap.querySelector('.note-editor').innerHTML;
        const status = wrap.querySelector('.note-status');
        fetch(key, {
            method: 'PATCH',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
            body: JSON.stringify({ title, content }),
        })
        .then(r => { if (!r.ok) throw new Error('save failed'); return r.json(); })
        .then(() => { if (status) status.textContent = 'Saved'; syncNoteTabLabel(wrap, title); })
        .catch(() => { if (status) status.textContent = 'Save failed'; });
    }
    function syncNoteTabLabel(wrap, title){
        const id = wrap.dataset.url.split('/').pop();
        const btn = document.getElementById('tab-btn-note-' + id);
        if (btn) btn.textContent = (title && title.trim()) ? title.trim().slice(0,16) : 'Untitled Note';
    }
    function applyNoteTemplate(btn, type){
        const wrap = noteWrap(btn);
        const editor = wrap.querySelector('.note-editor');
        const titleInput = wrap.querySelector('.note-title');
        const T = {
            meeting:   { title:'Meeting notes',   html:'<p><b>Date:</b> </p><p><b>Attendees:</b> </p><h2>Agenda</h2><ul><li>&nbsp;</li></ul><h2>Action items</h2><ul><li>&nbsp;</li></ul>' },
            resources: { title:'Key resources',   html:'<h2>Links</h2><ul><li>&nbsp;</li></ul><h2>Documents</h2><ul><li>&nbsp;</li></ul>' },
            planning:  { title:'Weekly planning', html:'<h2>Goals this week</h2><ul><li>&nbsp;</li></ul><h2>Priorities</h2><ol><li>&nbsp;</li></ol><h2>Notes</h2><p>&nbsp;</p>' },
            blank:     { title:'',                html:'<p>&nbsp;</p>' },
        };
        const tpl = T[type] || T.blank;
        if (!titleInput.value) titleInput.value = tpl.title;
        editor.innerHTML = tpl.html;
        const chips = wrap.querySelector('.note-templates');
        if (chips) chips.style.display = 'none';
        editor.focus();
        saveNote(editor);
    }

    function saveJobTitle(input) {
        const original = input.dataset.original ?? '';
        if (input.value === original) return;
        fetch(input.dataset.url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ job_title: input.value }),
        })
        .then(r => { if (!r.ok) throw new Error('save failed'); return r.json(); })
        .then(() => { input.dataset.original = input.value; })
        .catch(() => { input.style.borderColor = '#f87171'; });
    }
    document.querySelectorAll('#memberTableBody .job-title-input').forEach(i => i.dataset.original = i.value);

    // Open the tab requested via ?tab= (e.g. after adding a member/field)
    (function () {
        const requested = new URLSearchParams(window.location.search).get('tab');
        if (requested && document.getElementById('tab-btn-' + requested)) switchTab(requested);
    })();
    </script>

    <style>
        #tab-members .ptm-cell-input {
            background: transparent;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 13px;
            color: var(--text);
            font-family: var(--font);
            transition: border-color 0.12s, background 0.12s;
        }
        #tab-members .ptm-cell-input:hover { border-color: var(--border); }
        #tab-members .ptm-cell-input:focus {
            outline: none;
            border-color: var(--border2);
            background: var(--surface2);
        }
        #tab-members select.ptm-cell-input option { background: var(--surface); color: var(--text); }

        #tab-calendar .cal-navbtn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 7px;
            color: var(--muted);
            text-decoration: none;
            font-size: 16px;
            transition: background 0.12s, color 0.12s;
        }
        #tab-calendar .cal-navbtn:hover { background: var(--surface2); color: var(--text); }

        .note-wrap { margin: -4px -8px 0; }
        .note-toolbar { display:flex; align-items:center; gap:2px; padding:8px 14px; border-bottom:1px solid var(--border); flex-wrap:wrap; }
        .note-toolbar > button { min-width:30px; height:30px; padding:0 8px; background:none; border:none; border-radius:6px; color:var(--muted); cursor:pointer; font-size:14px; display:inline-flex; align-items:center; justify-content:center; font-family:var(--font); }
        .note-toolbar > button:hover { background:var(--surface2); color:var(--text); }
        .note-toolbar form button { background:none; border:none; cursor:pointer; padding:5px; display:inline-flex; }
        .note-sep { width:1px; height:18px; background:var(--border2); margin:0 6px; }
        .note-title { width:100%; background:transparent; border:none; outline:none; color:var(--text); font-size:30px; font-weight:700; font-family:var(--font); margin-bottom:16px; padding:0; letter-spacing:-0.5px; }
        .note-title::placeholder { color:var(--border2); }
        .note-editor { min-height:320px; outline:none; color:var(--text); font-size:15px; line-height:1.7; font-family:var(--font); }
        .note-editor:empty:before { content:attr(data-placeholder); color:var(--muted); pointer-events:none; }
        .note-editor h2 { font-size:20px; font-weight:600; margin:18px 0 8px; }
        .note-editor ul, .note-editor ol { padding-left:22px; margin:8px 0; }
        .note-editor blockquote { border-left:3px solid var(--border2); padding-left:12px; color:var(--muted); margin:10px 0; }
        .note-editor a { color:var(--accent2); }
        .note-templates { display:flex; flex-wrap:wrap; gap:8px; margin-top:22px; }
        .note-templates button { display:inline-flex; align-items:center; gap:6px; padding:7px 12px; background:var(--surface); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-size:12px; cursor:pointer; font-family:var(--font); transition:border-color 0.12s; }
        .note-templates button:hover { border-color:var(--accent2); }
    </style>

    {{-- Edit Team Modal --}}
    <div id="editTeamModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:200; align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:460px;">
            <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:15px; font-weight:600; color:var(--text);">Edit Team</span>
                <button onclick="document.getElementById('editTeamModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px;">✕</button>
            </div>
            <form method="POST" action="{{ route('company.teams.update', [$slug, $team]) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TEAM NAME *</label>
                    <input type="text" name="name" value="{{ $team->name }}" class="ptm-input" style="width:100%;" required>
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DESCRIPTION</label>
                    <textarea name="description" rows="2" class="ptm-input" style="width:100%; resize:vertical;">{{ $team->description }}</textarea>
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">MEMBERS</label>
                    <div style="background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:10px 12px; max-height:160px; overflow-y:auto;">
                        @foreach(auth()->user()->company->users()->where('is_active', true)->get() as $m)
                        <label style="display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:6px; cursor:pointer;" onmouseover="this.style.background='var(--surface)'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" name="members[]" value="{{ $m->id }}" {{ $members->contains('id', $m->id) ? 'checked' : '' }} style="width:16px; height:16px; cursor:pointer;">
                            <div style="width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:11px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($m->name,0,1)) }}</div>
                            <span style="font-size:13px; color:var(--text);">{{ $m->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex; gap:10px; padding-top:4px;">
                    <button type="submit" class="ptm-btn-primary">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editTeamModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Task detail slide-in drawer (opened from Calendar) --}}
    @include('company.tasks._drawer')

</x-company-layout>
