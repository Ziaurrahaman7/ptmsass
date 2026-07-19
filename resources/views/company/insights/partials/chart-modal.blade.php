{{--
    Shared "Add chart" gallery + config modal.
    Required variables: $slug, $chartData, $projects, $completionRate, $overdueCount,
                         $noDueDateCount, $unassignedCount, $totalTasks, $submitUrl
    $submitUrl is POSTed the chart payload; the response is either {url:...} (redirect,
    used when creating a brand new dashboard) or {success:true,...} (reload in place,
    used when adding a widget to the current dashboard).
--}}

{{-- Add Chart Modal --}}
<div id="addChartModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:500; align-items:flex-start; justify-content:center; padding-top:60px;">
    <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:900px; max-height:80vh; display:flex; flex-direction:column; overflow:hidden;">

        {{-- Modal Header --}}
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <span style="font-size:18px; font-weight:700; color:var(--text);">Add chart</span>
            <button onclick="document.getElementById('addChartModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:20px; line-height:1; padding:4px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">✕</button>
        </div>

        {{-- Modal Body --}}
        <div style="display:flex; flex:1; overflow:hidden;">

            {{-- Left Sidebar --}}
            <div style="width:180px; flex-shrink:0; padding:16px 12px; border-right:1px solid var(--border); display:flex; flex-direction:column; gap:2px; overflow-y:auto;">
                @php
                $cats = [
                    ['id'=>'recommended', 'label'=>'Recommended'],
                    ['id'=>'resourcing',  'label'=>'Resourcing'],
                    ['id'=>'workhealth', 'label'=>'Work health'],
                    ['id'=>'progress',   'label'=>'Progress'],
                ];
                @endphp
                @foreach($cats as $cat)
                <button class="chart-cat-btn {{ $loop->first ? 'active' : '' }}" data-cat="{{ $cat['id'] }}"
                    onclick="switchCategory('{{ $cat['id'] }}')"
                    style="background:none; border:none; text-align:left; padding:9px 12px; border-radius:8px; font-size:13px; font-family:var(--font); cursor:pointer; color:var(--muted); transition:all .15s; width:100%;">
                    {{ $cat['label'] }}
                </button>
                @endforeach

                {{-- Promo box --}}
                <div style="margin-top:auto; padding-top:20px;">
                    <div style="background:var(--surface2); border-radius:10px; padding:14px 12px; text-align:center;">
                        <div style="display:flex; justify-content:center; gap:6px; margin-bottom:10px;">
                            <div style="width:28px; height:28px; border-radius:6px; background:rgba(74,222,128,.15); display:flex; align-items:center; justify-content:center;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                            </div>
                            <div style="width:28px; height:28px; border-radius:50%; border:2px dashed var(--border2); display:flex; align-items:center; justify-content:center;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2"><circle cx="12" cy="12" r="8"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <div style="width:28px; height:28px; border-radius:6px; background:rgba(74,222,128,.15); display:flex; align-items:center; justify-content:center;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                            </div>
                        </div>
                        <div style="font-size:12px; font-weight:600; color:var(--text); margin-bottom:4px;">Get more insight in your work</div>
                        <div style="font-size:11px; color:var(--muted); line-height:1.5; margin-bottom:10px;">Track by project status, owner, and more with advanced charts</div>
                    </div>
                </div>
            </div>

            {{-- Right Content --}}
            <div style="flex:1; overflow-y:auto; padding:20px 24px;">

                {{-- RECOMMENDED --}}
                <div id="section-recommended" class="chart-section">
                    <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px;">Recommended</div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                        {{-- Add custom --}}
                        <div onclick="openChartConfig('Custom Chart','bar','assignee')" style="border:2px dashed var(--border2); border-radius:12px; padding:20px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; cursor:pointer; min-height:140px;" onmouseover="this.style.borderColor='var(--muted)'" onmouseout="this.style.borderColor='var(--border2)'">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span style="font-size:13px; color:var(--muted);">Add custom chart</span>
                        </div>
                        {{-- Incomplete tasks by project --}}
                        <div onclick="openChartConfig('Incomplete tasks by project','bar','project',{status_filter:'incomplete'})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Incomplete tasks<br>by project</div>
                            <div style="display:flex; align-items:flex-end; gap:4px; height:50px;">
                                @foreach([60,90,40,75,55,80] as $h)
                                <div style="flex:1; height:{{ $h }}%; border-radius:3px 3px 0 0; background:#4ade80;"></div>
                                @endforeach
                            </div>
                            <div style="height:1px; background:var(--border); margin-top:4px;"></div>
                        </div>
                        {{-- Projects by status --}}
                        <div onclick="openChartConfig('Projects by status','donut','project_status',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Projects by status</div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="position:relative; width:54px; height:54px; flex-shrink:0;">
                                    <svg width="54" height="54" viewBox="0 0 54 54">
                                        <circle cx="27" cy="27" r="20" fill="none" stroke="#fbbf24" stroke-width="10" stroke-dasharray="75 125" stroke-dashoffset="-31" />
                                        <circle cx="27" cy="27" r="20" fill="none" stroke="#4ade80" stroke-width="10" stroke-dasharray="45 125" stroke-dashoffset="44" />
                                        <circle cx="27" cy="27" r="20" fill="none" stroke="#6b7385" stroke-width="10" stroke-dasharray="30 125" stroke-dashoffset="89" />
                                        <circle cx="27" cy="27" r="8" fill="var(--surface2)" />
                                    </svg>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:5px;">
                                    <div style="display:flex; align-items:center; gap:6px;"><div style="width:8px;height:8px;border-radius:2px;background:#4ade80;"></div><span style="font-size:11px;color:var(--muted);">Done</span></div>
                                    <div style="display:flex; align-items:center; gap:6px;"><div style="width:8px;height:8px;border-radius:2px;background:#fbbf24;"></div><span style="font-size:11px;color:var(--muted);">Active</span></div>
                                    <div style="display:flex; align-items:center; gap:6px;"><div style="width:8px;height:8px;border-radius:2px;background:#6b7385;"></div><span style="font-size:11px;color:var(--muted);">On hold</span></div>
                                </div>
                            </div>
                        </div>
                        {{-- Tasks by priority --}}
                        <div onclick="openChartConfig('Tasks by priority','bar','priority',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Tasks by priority</div>
                            <div style="display:flex; align-items:flex-end; gap:4px; height:50px;">
                                @foreach([['#f87171',90],['#fbbf24',65],['#22d3ee',80],['#6b7385',40]] as $b)
                                <div style="flex:1; height:{{ $b[1] }}%; border-radius:3px 3px 0 0; background:{{ $b[0] }};"></div>
                                @endforeach
                            </div>
                            <div style="height:1px; background:var(--border); margin-top:4px;"></div>
                        </div>
                        {{-- Completion rate --}}
                        <div onclick="openChartConfig('Completion rate','number','completion_rate',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Completion rate</div>
                            <div style="font-size:28px; font-weight:700; color:#4ade80;">{{ $completionRate ?? 0 }}%</div>
                            <div style="height:5px; background:var(--border); border-radius:3px; margin-top:8px;"><div style="height:100%; width:{{ $completionRate ?? 0 }}%; background:#4ade80; border-radius:3px;"></div></div>
                        </div>
                        {{-- Overdue tasks --}}
                        <div onclick="openChartConfig('Overdue tasks','number','overdue_count',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer; transition:border-color .15s;" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Overdue tasks</div>
                            <div style="font-size:28px; font-weight:700; color:#f87171;">{{ $overdueCount ?? 0 }}</div>
                            <div style="font-size:11px; color:var(--muted); margin-top:4px;">tasks past due date</div>
                        </div>
                    </div>
                </div>

                {{-- RESOURCING --}}
                <div id="section-resourcing" class="chart-section" style="display:none;">
                    <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px;">Resourcing</div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                        <div onclick="openChartConfig('Upcoming tasks by assignee this week','bar','upcoming_assignee',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Upcoming tasks by<br>assignee this week</div>
                            <div style="display:flex; align-items:flex-end; gap:6px; height:50px;">
                                @foreach([70,50,85,45] as $h)
                                <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                                    <div style="width:3px; height:{{ $h }}%; background:#22d3ee; border-radius:2px;"></div>
                                    <div style="width:18px; height:18px; border-radius:50%; background:rgba(34,211,238,.2); display:flex; align-items:center; justify-content:center; font-size:8px; color:#22d3ee; font-weight:700;">U</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div onclick="openChartConfig('This month\'s tasks by project','bar','project',{date_range:'30'})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">This month's tasks<br>by project</div>
                            <div style="display:flex; align-items:flex-end; gap:4px; height:50px;">
                                @foreach([['#f87171',55],['#fbbf24',80],['#4ade80',65],['#22d3ee',90]] as $b)
                                <div style="flex:1; height:{{ $b[1] }}%; border-radius:3px 3px 0 0; background:{{ $b[0] }};"></div>
                                @endforeach
                            </div>
                            <div style="height:1px; background:var(--border); margin-top:4px;"></div>
                        </div>
                        <div onclick="openChartConfig('Total tasks','number','total_tasks',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Total tasks</div>
                            <div style="font-size:28px; font-weight:700; color:var(--text);">{{ $totalTasks ?? 0 }}</div>
                            <div style="font-size:11px; color:var(--muted); margin-top:4px;">across all projects</div>
                        </div>
                        <div onclick="openChartConfig('Tasks by creator','bar','created_by',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Tasks by creator</div>
                            <div style="display:flex; align-items:flex-end; gap:4px; height:50px;">
                                @foreach([50,75,40,90,60] as $h)
                                <div style="flex:1; height:{{ $h }}%; border-radius:3px 3px 0 0; background:#a78bfa;"></div>
                                @endforeach
                            </div>
                            <div style="height:1px; background:var(--border); margin-top:4px;"></div>
                        </div>
                    </div>
                </div>

                {{-- WORK HEALTH --}}
                <div id="section-workhealth" class="chart-section" style="display:none;">
                    <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px;">Work health</div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                        <div onclick="openChartConfig('Overdue tasks by project','bar','project',{status_filter:'overdue'})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Overdue tasks<br>by project</div>
                            <div style="display:flex; align-items:flex-end; gap:4px; height:50px;">
                                @foreach([30,60,20,80,45] as $h)
                                <div style="flex:1; height:{{ $h }}%; border-radius:3px 3px 0 0; background:#f87171;"></div>
                                @endforeach
                            </div>
                            <div style="height:1px; background:var(--border); margin-top:4px;"></div>
                        </div>
                        <div onclick="openChartConfig('Tasks without due date','number','no_due_date',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Tasks without<br>due date</div>
                            <div style="font-size:28px; font-weight:700; color:var(--warn);">{{ $noDueDateCount ?? 0 }}</div>
                            <div style="font-size:11px; color:var(--muted); margin-top:4px;">tasks missing deadline</div>
                        </div>
                        <div onclick="openChartConfig('Unassigned tasks','number','unassigned',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Unassigned tasks</div>
                            <div style="font-size:28px; font-weight:700; color:var(--purple);">{{ $unassignedCount ?? 0 }}</div>
                            <div style="font-size:11px; color:var(--muted); margin-top:4px;">tasks without assignee</div>
                        </div>
                    </div>
                </div>

                {{-- PROGRESS --}}
                <div id="section-progress" class="chart-section" style="display:none;">
                    <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px;">Progress</div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                        <div onclick="openChartConfig('Tasks completed over time','bar','completed_over_time',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Tasks completed<br>over time</div>
                            <div style="display:flex; align-items:flex-end; gap:3px; height:50px;">
                                @foreach([20,35,25,50,40,65,55,80,70,90] as $h)
                                <div style="flex:1; height:{{ $h }}%; border-radius:2px 2px 0 0; background:rgba(74,222,128,{{ $h/100 + 0.2 }});"></div>
                                @endforeach
                            </div>
                            <div style="height:1px; background:var(--border); margin-top:4px;"></div>
                        </div>
                        <div onclick="openChartConfig('Project completion rate','number','completion_rate',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Project completion<br>rate</div>
                            <div style="font-size:28px; font-weight:700; color:#4ade80;">{{ $completionRate ?? 0 }}%</div>
                            <div style="height:5px; background:var(--border); border-radius:3px; margin-top:8px;"><div style="height:100%; width:{{ $completionRate ?? 0 }}%; background:#4ade80; border-radius:3px;"></div></div>
                        </div>
                        <div onclick="openChartConfig('Done tasks this week','bar','done_this_week',{})" style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px; cursor:pointer;">
                            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:12px;">Done tasks<br>this week</div>
                            <div style="display:flex; align-items:flex-end; gap:4px; height:50px;">
                                @foreach([40,60,30,80,50,70,90] as $h)
                                <div style="flex:1; height:{{ $h }}%; border-radius:3px 3px 0 0; background:#22d3ee;"></div>
                                @endforeach
                            </div>
                            <div style="height:1px; background:var(--border); margin-top:4px;"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.chart-cat-btn:hover { background: var(--surface2) !important; color: var(--text) !important; }
.chart-cat-btn.active { background: var(--surface2) !important; color: var(--text) !important; font-weight: 600; }

/* Config modal */
.cfg-label { font-size: 11px; color: var(--muted); font-family: var(--mono); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; display: block; }
.cfg-select {
    width: 100%; background: var(--surface); border: 1px solid var(--border2);
    border-radius: 8px; color: var(--text); font-family: var(--font); font-size: 13px;
    padding: 10px 12px; cursor: pointer; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7385' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
    padding-right: 32px;
}
.cfg-select:focus { outline: none; border-color: var(--accent2); }
.cfg-input {
    width: 100%; background: var(--surface); border: 1px solid var(--border2);
    border-radius: 8px; color: var(--text); font-family: var(--font); font-size: 13px;
    padding: 10px 12px; box-sizing: border-box;
}
.cfg-input:focus { outline: none; border-color: var(--accent2); }
.cfg-section-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 16px; }
.cfg-divider { border: none; border-top: 1px solid var(--border); margin: 18px 0; }
.cfg-add-btn {
    background: #4573d2; color: #fff; border: none; border-radius: 8px;
    padding: 9px 20px; font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: var(--font); transition: background .15s;
}
.cfg-add-btn:hover { background: #3a62bb; }
.cfg-cancel-btn {
    background: none; border: 1px solid var(--border2); color: var(--text);
    border-radius: 8px; padding: 9px 20px; font-size: 13px; font-weight: 500;
    cursor: pointer; font-family: var(--font); transition: all .15s;
}
.cfg-cancel-btn:hover { background: var(--surface2); }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- Chart Config Modal --}}
<div id="chartConfigModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.8); z-index:600; align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:960px; height:85vh; display:flex; flex-direction:column; overflow:hidden;">

        {{-- Header --}}
        <div style="padding:18px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <span style="font-size:17px; font-weight:700; color:var(--text);">Add chart</span>
            <button onclick="closeConfigModal()" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:20px; padding:4px; line-height:1;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">✕</button>
        </div>

        {{-- Body: left preview + right config --}}
        <div style="display:flex; flex:1; overflow:hidden;">

            {{-- LEFT: Chart Preview --}}
            <div style="flex:1; padding:28px 32px; overflow-y:auto; border-right:1px solid var(--border); display:flex; flex-direction:column;">
                <div id="cfgChartTitle" style="font-size:20px; font-weight:600; color:var(--muted); margin-bottom:24px;">Chart title</div>
                <div id="previewNumberWrap" style="display:none; flex:1; align-items:center; justify-content:center;">
                    <span id="previewNumberVal" style="font-size:72px; font-weight:700; color:#4ade80;">0</span>
                </div>
                <div id="previewChartWrap" style="flex:1; min-height:280px; position:relative;">
                    <canvas id="previewCanvas"></canvas>
                </div>
            </div>

            {{-- RIGHT: Config Panel --}}
            <div style="width:300px; flex-shrink:0; overflow-y:auto; padding:24px 20px; display:flex; flex-direction:column; gap:0;">

                {{-- Chart details --}}
                <div class="cfg-section-title">Chart details</div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">Chart title</label>
                    <input type="text" id="cfgTitleInput" class="cfg-input" placeholder="e.g. Tasks by assignee" oninput="document.getElementById('cfgChartTitle').textContent = this.value || 'Chart title'">
                </div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">Report on</label>
                    <select id="cfgReportOn" class="cfg-select" onchange="updatePreview()">
                        <option value="tasks">✓ Tasks</option>
                        <option value="projects">Projects</option>
                    </select>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">Include tasks from</label>
                    <select id="cfgProject" class="cfg-select" onchange="updatePreview()">
                        <option value="all">All projects</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">Chart style</label>
                    <select id="cfgStyle" class="cfg-select" onchange="updatePreview()">
                        <option value="bar">▐▐ Bar</option>
                        <option value="column">▬▬ Column</option>
                        <option value="donut">◎ Donut</option>
                        <option value="number">123 Number</option>
                        <option value="line">∿ Line</option>
                    </select>
                </div>

                <hr class="cfg-divider">

                {{-- Chart data --}}
                <div class="cfg-section-title">Chart data</div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">X-axis (Group by)</label>
                    <select id="cfgXAxis" class="cfg-select" onchange="updatePreview()">
                        <option value="assignee">Assignee</option>
                        <option value="project">Project</option>
                        <option value="status">Status</option>
                        <option value="priority">Priority</option>
                        <option value="due_date">Due date</option>
                        <option value="created_by">Created by</option>
                        <option value="project_status">Project status</option>
                        <option value="upcoming_assignee">Upcoming (assignee)</option>
                        <option value="no_due_date">No due date</option>
                        <option value="unassigned">Unassigned</option>
                        <option value="total_tasks">Total tasks</option>
                        <option value="overdue_count">Overdue count</option>
                        <option value="completion_rate">Completion rate</option>
                        <option value="completed_over_time">Completed over time</option>
                        <option value="done_this_week">Done this week</option>
                    </select>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">Filter by status</label>
                    <select id="cfgStatus" class="cfg-select" onchange="updatePreview()">
                        <option value="all">All statuses</option>
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="in_review">In Review</option>
                        <option value="done">Done</option>
                        <option value="incomplete">Incomplete (not done)</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">Filter by priority</label>
                    <select id="cfgPriority" class="cfg-select" onchange="updatePreview()">
                        <option value="all">All priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="cfg-label">Date range</label>
                    <select id="cfgDateRange" class="cfg-select" onchange="updatePreview()">
                        <option value="all">All time</option>
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 3 months</option>
                        <option value="365">Last year</option>
                    </select>
                </div>

                <hr class="cfg-divider">

                {{-- Actions --}}
                <div style="display:flex; gap:10px; margin-top:4px;">
                    <button class="cfg-cancel-btn" onclick="closeConfigModal()">Cancel</button>
                    <button class="cfg-add-btn" onclick="addChart()">Add chart</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CHART_SUBMIT_URL  = '{{ $submitUrl }}';
const CHART_PREVIEW_URL = '{{ route("company.insights.chart-preview", $slug) }}';
const chartData = @json($chartData);

const BAR_COLORS = ['#a78bfa','#4ade80','#22d3ee','#fbbf24','#f87171','#fb923c','#60a5fa','#34d399','#e879f9','#38bdf8'];
let previewChart = null;

function switchCategory(cat) {
    document.querySelectorAll('.chart-cat-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.chart-section').forEach(s => s.style.display = 'none');
    document.querySelector('[data-cat="'+cat+'"]').classList.add('active');
    document.getElementById('section-'+cat).style.display = 'block';
}

function openAddChartModal() {
    switchCategory('recommended');
    document.getElementById('addChartModal').style.display = 'flex';
}

function openChartConfig(title, style, xaxis, opts) {
    opts = opts || {};
    document.getElementById('addChartModal').style.display = 'none';
    document.getElementById('chartConfigModal').style.display = 'flex';
    document.getElementById('cfgTitleInput').value = title;
    document.getElementById('cfgChartTitle').textContent = title;
    document.getElementById('cfgStyle').value = style;
    document.getElementById('cfgXAxis').value = xaxis;
    document.getElementById('cfgProject').value  = opts.project_filter  || 'all';
    document.getElementById('cfgStatus').value   = opts.status_filter   || 'all';
    document.getElementById('cfgPriority').value = opts.priority_filter || 'all';
    document.getElementById('cfgDateRange').value = opts.date_range     || 'all';
    updatePreview();
}

function closeConfigModal() {
    document.getElementById('chartConfigModal').style.display = 'none';
    if (previewChart) { previewChart.destroy(); previewChart = null; }
}

// inline datalabels plugin (no external dep)
const topLabelsPlugin = {
    id: 'topLabels',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;
        const isHoriz = chart.options.indexAxis === 'y';
        chart.getDatasetMeta(0).data.forEach((bar, i) => {
            const val = chart.data.datasets[0].data[i];
            if (!val) return;
            ctx.save();
            ctx.font = '600 11px Inter, sans-serif';
            ctx.fillStyle = '#cbd5e1';
            ctx.textAlign = 'center';
            if (isHoriz) {
                ctx.textBaseline = 'middle';
                ctx.fillText(val, bar.x + 20, bar.y);
            } else {
                ctx.textBaseline = 'bottom';
                ctx.fillText(val, bar.x, bar.y - 5);
            }
            ctx.restore();
        });
    }
};

let previewToken = null;

function updatePreview() {
    const xaxis = document.getElementById('cfgXAxis').value;
    const style = document.getElementById('cfgStyle').value;

    // Render instantly from the unfiltered snapshot so the modal never feels dead,
    // then refine with the real filtered numbers from the server.
    renderPreview(style, chartData[xaxis] || []);

    const token = {};
    previewToken = token;

    fetch(CHART_PREVIEW_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            x_axis:          xaxis,
            project_filter:  document.getElementById('cfgProject').value,
            status_filter:   document.getElementById('cfgStatus').value,
            priority_filter: document.getElementById('cfgPriority').value,
            date_range:      document.getElementById('cfgDateRange').value,
        })
    })
    .then(r => r.json())
    .then(json => { if (previewToken === token) renderPreview(style, json.data || []); })
    .catch(err => console.error(err));
}

function renderPreview(style, data) {
    const labels = data.map(d => d.label);
    const values = data.map(d => d.value);

    const numWrap   = document.getElementById('previewNumberWrap');
    const chartWrap = document.getElementById('previewChartWrap');

    if (previewChart) { previewChart.destroy(); previewChart = null; }

    if (style === 'number') {
        numWrap.style.display   = 'flex';
        chartWrap.style.display = 'none';
        document.getElementById('previewNumberVal').textContent = values.reduce((a,b)=>a+b,0);
        return;
    }
    numWrap.style.display   = 'none';
    chartWrap.style.display = 'block';

    const ctx       = document.getElementById('previewCanvas').getContext('2d');
    const gridColor = 'rgba(255,255,255,0.07)';
    const tickColor = '#6b7385';

    if (style === 'donut') {
        previewChart = new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: BAR_COLORS.slice(0, data.length), borderWidth: 0, hoverOffset: 8 }] },
            options: {
                responsive: true, maintainAspectRatio: true, cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: tickColor, font: { size: 11 }, boxWidth: 10, padding: 14 } },
                    tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed}` } },
                }
            }
        });
        return;
    }

    const isHoriz  = style === 'column';
    const chartType = style === 'line' ? 'line' : 'bar';

    previewChart = new Chart(ctx, {
        type: chartType,
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: style === 'line' ? 'rgba(167,139,250,0.15)' : BAR_COLORS.slice(0, data.length),
                borderColor:     style === 'line' ? '#a78bfa' : 'transparent',
                borderWidth:     style === 'line' ? 2 : 0,
                borderRadius:    style === 'line' ? 0 : 5,
                fill:            style === 'line',
                tension:         style === 'line' ? 0.4 : 0,
                pointBackgroundColor: style === 'line' ? '#a78bfa' : undefined,
            }]
        },
        options: {
            indexAxis: isHoriz ? 'y' : 'x',
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: isHoriz ? 4 : 22, right: isHoriz ? 44 : 8 } },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => ` ${c.parsed[isHoriz ? 'x' : 'y']}` } },
            },
            scales: {
                x: {
                    grid: { color: isHoriz ? gridColor : 'transparent', drawBorder: false },
                    border: { color: 'rgba(255,255,255,0.1)' },
                    ticks: {
                        color: tickColor, font: { size: 10 }, maxRotation: isHoriz ? 0 : 35,
                        callback(val, i) {
                            const lbl = this.getLabelForValue(isHoriz ? val : i);
                            return lbl && lbl.length > 10 ? lbl.slice(0,10)+'…' : lbl;
                        }
                    },
                },
                y: {
                    grid: { color: isHoriz ? 'transparent' : gridColor, drawBorder: false },
                    border: { color: 'rgba(255,255,255,0.1)' },
                    beginAtZero: true,
                    ticks: {
                        color: tickColor, font: { size: 10 },
                        callback(val) {
                            if (isHoriz) {
                                const lbl = this.getLabelForValue(val);
                                return lbl && lbl.length > 14 ? lbl.slice(0,14)+'…' : lbl;
                            }
                            return val;
                        }
                    },
                }
            }
        },
        plugins: [topLabelsPlugin]
    });
}

function addChart() {
    const titleInput = document.getElementById('cfgTitleInput');
    const title = titleInput.value.trim() || document.getElementById('cfgChartTitle').textContent || 'New chart';
    titleInput.style.borderColor = '';

    const btn = document.querySelector('.cfg-add-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    fetch(CHART_SUBMIT_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            title:           title,
            chart_style:     document.getElementById('cfgStyle').value,
            x_axis:          document.getElementById('cfgXAxis').value,
            report_on:       document.getElementById('cfgReportOn').value,
            project_filter:  document.getElementById('cfgProject').value,
            status_filter:   document.getElementById('cfgStatus').value,
            priority_filter: document.getElementById('cfgPriority').value,
            date_range:      document.getElementById('cfgDateRange').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.url) { window.location.href = data.url; return; }
        if (data.success) { window.location.reload(); return; }
        btn.disabled = false; btn.textContent = 'Add chart';
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.textContent = 'Add chart';
    });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeConfigModal();
        document.getElementById('addChartModal').style.display = 'none';
    }
});
</script>
