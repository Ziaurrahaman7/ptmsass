<x-company-layout :title="$dashboard->title">

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.db-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:24px; }
.back-btn { display:inline-flex; align-items:center; gap:6px; color:var(--muted); font-size:13px; text-decoration:none; margin-bottom:18px; transition:color .15s; }
.back-btn:hover { color:var(--text); }
.db-meta-badge { font-size:11px; background:var(--surface2); border:1px solid var(--border); border-radius:6px; padding:3px 10px; color:var(--muted); font-family:var(--mono); }
</style>

<a href="{{ route('company.insights.index', $slug) }}" class="back-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Reporting
</a>

{{-- Header --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg,#4f46e5,#2563eb); display:flex; align-items:center; justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        </div>
        <div>
            <h1 style="font-size:20px; font-weight:700; color:var(--text); margin:0 0 4px;">{{ $dashboard->title }}</h1>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <span class="db-meta-badge">{{ ucfirst($dashboard->chart_style) }}</span>
                <span class="db-meta-badge">{{ ucfirst(str_replace('_',' ',$dashboard->x_axis)) }}</span>
                @if($dashboard->project_filter)
                <span class="db-meta-badge">{{ \App\Models\Project::find($dashboard->project_filter)?->name }}</span>
                @endif
                @if($dashboard->status_filter)
                <span class="db-meta-badge">{{ ucfirst(str_replace('_',' ',$dashboard->status_filter)) }}</span>
                @endif
            </div>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $dashboard->created_at->format('d M Y') }}</span>
        <form method="POST" action="{{ route('company.insights.dashboards.destroy', [$slug, $dashboard->id]) }}"
              onsubmit="return confirm('Delete this dashboard?')" style="margin:0;">
            @csrf @method('DELETE')
            <button type="submit" style="background:none; border:1px solid var(--border2); color:var(--muted); border-radius:8px; padding:6px 12px; font-size:12px; cursor:pointer; font-family:var(--font);">
                Delete
            </button>
        </form>
    </div>
</div>

{{-- Chart --}}
<div class="db-card">
    @if($dashboard->chart_style === 'number')
        @php $total = collect($chartData)->sum('value'); @endphp
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 0;">
            <div style="font-size:80px; font-weight:700; color:#4ade80; line-height:1;">{{ $total }}</div>
            <div style="font-size:14px; color:var(--muted); margin-top:8px;">{{ $dashboard->title }}</div>
        </div>
    @else
        <div style="position:relative; height:400px;">
            <canvas id="mainChart"></canvas>
        </div>
    @endif
</div>

{{-- Data table --}}
@if(count($chartData) && $dashboard->chart_style !== 'number')
<div class="db-card" style="margin-top:16px;">
    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:.07em; margin-bottom:14px;">Data</div>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left; font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; padding:0 0 10px; font-weight:500;">{{ ucfirst(str_replace('_',' ',$dashboard->x_axis)) }}</th>
                <th style="text-align:right; font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; padding:0 0 10px; font-weight:500;">Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chartData as $row)
            <tr style="border-top:1px solid var(--border);">
                <td style="padding:10px 0; font-size:13px; color:var(--text);">{{ $row['label'] }}</td>
                <td style="padding:10px 0; font-size:13px; color:var(--muted); text-align:right; font-family:var(--mono);">{{ $row['value'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($dashboard->chart_style !== 'number')
<script>
const chartData = @json($chartData);
const BAR_COLORS = ['#a78bfa','#4ade80','#22d3ee','#fbbf24','#f87171','#fb923c','#60a5fa','#34d399','#e879f9','#38bdf8'];
const labels = chartData.map(d => d.label);
const values = chartData.map(d => d.value);
const style  = '{{ $dashboard->chart_style }}';
const gridColor = 'rgba(255,255,255,0.07)';
const tickColor = '#6b7385';

const topLabelsPlugin = {
    id: 'topLabels',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;
        const isHoriz = chart.options.indexAxis === 'y';
        chart.getDatasetMeta(0).data.forEach((bar, i) => {
            const val = chart.data.datasets[0].data[i];
            if (!val) return;
            ctx.save();
            ctx.font = '600 12px Inter, sans-serif';
            ctx.fillStyle = '#cbd5e1';
            ctx.textAlign = 'center';
            if (isHoriz) {
                ctx.textBaseline = 'middle';
                ctx.fillText(val, bar.x + 22, bar.y);
            } else {
                ctx.textBaseline = 'bottom';
                ctx.fillText(val, bar.x, bar.y - 6);
            }
            ctx.restore();
        });
    }
};

const ctx = document.getElementById('mainChart').getContext('2d');

if (style === 'donut') {
    new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets: [{ data: values, backgroundColor: BAR_COLORS.slice(0, chartData.length), borderWidth: 0, hoverOffset: 10 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { color: tickColor, font: { size: 12 }, boxWidth: 12, padding: 16 } },
                tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed}` } },
            }
        }
    });
} else {
    const isHoriz  = style === 'column';
    const chartType = style === 'line' ? 'line' : 'bar';
    new Chart(ctx, {
        type: chartType,
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: style === 'line' ? 'rgba(167,139,250,0.15)' : BAR_COLORS.slice(0, chartData.length),
                borderColor:     style === 'line' ? '#a78bfa' : 'transparent',
                borderWidth:     style === 'line' ? 2 : 0,
                borderRadius:    style === 'line' ? 0 : 6,
                fill:            style === 'line',
                tension:         style === 'line' ? 0.4 : 0,
                pointBackgroundColor: style === 'line' ? '#a78bfa' : undefined,
            }]
        },
        options: {
            indexAxis: isHoriz ? 'y' : 'x',
            responsive: true, maintainAspectRatio: false,
            layout: { padding: { top: isHoriz ? 4 : 28, right: isHoriz ? 50 : 10 } },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => ` ${c.parsed[isHoriz ? 'x' : 'y']}` } },
            },
            scales: {
                x: {
                    grid: { color: isHoriz ? gridColor : 'transparent', drawBorder: false },
                    border: { color: 'rgba(255,255,255,0.1)' },
                    ticks: {
                        color: tickColor, font: { size: 11 }, maxRotation: isHoriz ? 0 : 35,
                        callback(val, i) {
                            const lbl = this.getLabelForValue(isHoriz ? val : i);
                            return lbl && lbl.length > 12 ? lbl.slice(0,12)+'…' : lbl;
                        }
                    },
                },
                y: {
                    grid: { color: isHoriz ? 'transparent' : gridColor, drawBorder: false },
                    border: { color: 'rgba(255,255,255,0.1)' },
                    beginAtZero: true,
                    ticks: {
                        color: tickColor, font: { size: 11 },
                        callback(val) {
                            if (isHoriz) {
                                const lbl = this.getLabelForValue(val);
                                return lbl && lbl.length > 16 ? lbl.slice(0,16)+'…' : lbl;
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
</script>
@endif

</x-company-layout>
