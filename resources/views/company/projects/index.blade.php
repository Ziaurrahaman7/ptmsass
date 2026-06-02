<x-company-layout title="Projects">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">All Projects</h2>
            <p class="text-sm text-gray-500">Manage your company's projects</p>
        </div>
        <a href="{{ route('company.projects.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Project
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($projects as $project)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('company.projects.show', $project) }}"
                       class="font-semibold text-gray-800 hover:text-indigo-600 block truncate">
                        {{ $project->name }}
                    </a>
                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ $project->description ?? 'No description' }}</p>
                </div>
                <span class="ml-2 flex-shrink-0 text-xs px-2 py-1 rounded-full font-medium
                    {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700' :
                       ($project->status === 'completed'  ? 'bg-green-100 text-green-700' :
                       ($project->status === 'on_hold'    ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                    {{ ucfirst(str_replace('_',' ',$project->status)) }}
                </span>
            </div>

            <div class="mb-3">
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>Progress</span>
                    <span>{{ $project->progressPercentage() }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $project->progressPercentage() }}%"></div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-400">
                <span>{{ $project->tasks_count }} tasks · {{ $project->done_tasks_count }} done</span>
                @if($project->due_date)
                <span class="{{ $project->due_date->isPast() && $project->status !== 'completed' ? 'text-red-500' : '' }}">
                    Due {{ $project->due_date->format('d M') }}
                </span>
                @endif
            </div>

            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-50">
                <a href="{{ route('company.projects.show', $project) }}"
                   class="flex-1 text-center text-xs font-medium text-indigo-600 hover:text-indigo-800 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                    Open
                </a>
                <a href="{{ route('company.projects.edit', $project) }}"
                   class="flex-1 text-center text-xs font-medium text-gray-500 hover:text-gray-700 py-1.5 rounded-lg hover:bg-gray-50 transition">
                    Edit
                </a>
                <form method="POST" action="{{ route('company.projects.destroy', $project) }}"
                      onsubmit="return confirm('Delete this project and all its tasks?')">
                    @csrf @method('DELETE')
                    <button class="text-xs font-medium text-red-400 hover:text-red-600 py-1.5 px-3 rounded-lg hover:bg-red-50 transition">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-xl border border-dashed border-gray-200 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-gray-400 mb-3">No projects yet</p>
            <a href="{{ route('company.projects.create') }}" class="text-sm text-indigo-600 hover:underline">Create your first project</a>
        </div>
        @endforelse
    </div>

    @if($projects->hasPages())
    <div class="mt-6">{{ $projects->links() }}</div>
    @endif

</x-company-layout>
