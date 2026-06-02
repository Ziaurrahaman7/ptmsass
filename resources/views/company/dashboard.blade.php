<x-company-layout title="Dashboard">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label'=>'Total Projects','value'=>$totalProjects,'color'=>'indigo','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['label'=>'Active Projects','value'=>$activeProjects,'color'=>'blue','icon'=>'M13 10V3L4 14h7v7l9-11h-7z'],
            ['label'=>'Total Tasks','value'=>$totalTasks,'color'=>'violet','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['label'=>'Overdue Tasks','value'=>$overdueTasks,'color'=>'red','icon'=>'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as $stat)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
                <div class="w-8 h-8 bg-{{ $stat['color'] }}-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-{{ $stat['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Projects --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-700">Recent Projects</h2>
                <a href="{{ route('company.projects.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentProjects as $project)
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <a href="{{ route('company.projects.show', $project) }}"
                           class="font-medium text-gray-800 hover:text-indigo-600 text-sm">{{ $project->name }}</a>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700' :
                               ($project->status === 'completed' ? 'bg-green-100 text-green-700' :
                               ($project->status === 'on_hold' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                            {{ ucfirst(str_replace('_',' ',$project->status)) }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $project->progressPercentage() }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $project->progressPercentage() }}% complete · {{ $project->tasks_count }} tasks</p>
                </div>
                @empty
                <p class="px-5 py-6 text-sm text-gray-400 text-center">No projects yet. <a href="{{ route('company.projects.create') }}" class="text-indigo-600 hover:underline">Create one</a></p>
                @endforelse
            </div>
        </div>

        {{-- Recent Tasks --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Recent Tasks</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentTasks as $task)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $task->title }}</p>
                        <p class="text-xs text-gray-400">{{ $task->project->name }} · {{ $task->assignee?->name ?? 'Unassigned' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-{{ $task->priorityColor() }}-100 text-{{ $task->priorityColor() }}-700">
                            {{ ucfirst($task->priority) }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-{{ $task->statusColor() }}-100 text-{{ $task->statusColor() }}-700">
                            {{ ucfirst(str_replace('_',' ',$task->status)) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="px-5 py-6 text-sm text-gray-400 text-center">No tasks yet.</p>
                @endforelse
            </div>
        </div>
    </div>

</x-company-layout>
