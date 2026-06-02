<x-company-layout title="All Tasks">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">All Tasks</h2>
            <p class="text-sm text-gray-500">Tasks across all projects</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Task</th>
                    <th class="px-5 py-3 text-left">Project</th>
                    <th class="px-5 py-3 text-left">Assignee</th>
                    <th class="px-5 py-3 text-left">Priority</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Due Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $task->title }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('company.projects.show', $task->project) }}"
                           class="text-indigo-600 hover:underline">{{ $task->project->name }}</a>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $task->assignee?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-{{ $task->priorityColor() }}-100 text-{{ $task->priorityColor() }}-700">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-{{ $task->statusColor() }}-100 text-{{ $task->statusColor() }}-700">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'text-red-500' : 'text-gray-400' }}">
                        {{ $task->due_date?->format('d M Y') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">No tasks found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
    <div class="mt-4">{{ $tasks->links() }}</div>
    @endif

</x-company-layout>
