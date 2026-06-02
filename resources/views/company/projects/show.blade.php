<x-company-layout :title="$project->name">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('company.projects.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Projects</a>
            <h2 class="text-xl font-bold text-gray-800 mt-1">{{ $project->name }}</h2>
            @if($project->description)
            <p class="text-sm text-gray-500 mt-0.5">{{ $project->description }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm px-3 py-1 rounded-full font-medium
                {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700' :
                   ($project->status === 'completed'  ? 'bg-green-100 text-green-700' :
                   ($project->status === 'on_hold'    ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                {{ ucfirst(str_replace('_',' ',$project->status)) }}
            </span>
            <a href="{{ route('company.projects.edit', $project) }}"
               class="text-sm text-gray-500 hover:text-indigo-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:border-indigo-300 transition">
                Edit Project
            </a>
        </div>
    </div>

    {{-- Progress --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Overall Progress</span>
            <span class="text-sm font-bold text-indigo-600">{{ $project->progressPercentage() }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3">
            <div class="bg-indigo-500 h-3 rounded-full transition-all" style="width: {{ $project->progressPercentage() }}%"></div>
        </div>
        <div class="flex gap-6 mt-3 text-sm text-gray-500">
            <span>{{ $tasks->count() }} total</span>
            <span>{{ $tasks->where('status','in_progress')->count() }} in progress</span>
            <span>{{ $tasks->where('status','done')->count() }} done</span>
            @if($project->due_date)<span class="{{ $project->due_date->isPast() && $project->status !== 'completed' ? 'text-red-500' : '' }}">Due {{ $project->due_date->format('d M Y') }}</span>@endif
        </div>
    </div>

    {{-- Kanban Board --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        @foreach(['todo'=>['label'=>'To Do','color'=>'gray'],'in_progress'=>['label'=>'In Progress','color'=>'blue'],'in_review'=>['label'=>'In Review','color'=>'purple'],'done'=>['label'=>'Done','color'=>'green']] as $status => $cfg)
        <div class="bg-gray-50 rounded-xl p-3">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-2 h-2 rounded-full bg-{{ $cfg['color'] }}-400"></div>
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ $cfg['label'] }}</span>
                <span class="ml-auto text-xs text-gray-400 bg-white px-1.5 py-0.5 rounded-full">
                    {{ $tasks->where('status',$status)->count() }}
                </span>
            </div>
            <div class="space-y-2">
                @foreach($tasks->where('status',$status) as $task)
                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 group">
                    <div class="flex items-start justify-between gap-1">
                        <p class="text-sm font-medium text-gray-800 leading-tight">{{ $task->title }}</p>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                            <button onclick="openEditTask({{ $task->id }}, '{{ addslashes($task->title) }}', '{{ addslashes($task->description) }}', '{{ $task->status }}', '{{ $task->priority }}', '{{ $task->assigned_to }}', '{{ $task->due_date?->format('Y-m-d') }}')"
                                    class="text-gray-400 hover:text-indigo-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('company.tasks.destroy', $task) }}">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('Delete task?')" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @if($task->description)
                    <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $task->description }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium bg-{{ $task->priorityColor() }}-100 text-{{ $task->priorityColor() }}-700">
                            {{ ucfirst($task->priority) }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if($task->due_date)
                            <span class="text-xs {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-500' : 'text-gray-400' }}">
                                {{ $task->due_date->format('d M') }}
                            </span>
                            @endif
                            @if($task->assignee)
                            <div class="w-5 h-5 rounded-full bg-indigo-400 flex items-center justify-center text-white text-xs font-bold" title="{{ $task->assignee->name }}">
                                {{ strtoupper(substr($task->assignee->name,0,1)) }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add Task Button --}}
    <button onclick="document.getElementById('addTaskModal').classList.remove('hidden')"
            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Task
    </button>

    {{-- Add Task Modal --}}
    <div id="addTaskModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Add Task</h3>
                <button onclick="document.getElementById('addTaskModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('company.tasks.store', $project) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="in_review">In Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                        <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Unassigned</option>
                            @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">Add Task</button>
                    <button type="button" onclick="document.getElementById('addTaskModal').classList.add('hidden')" class="text-sm text-gray-500 px-4 py-2 rounded-lg border border-gray-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Task Modal --}}
    <div id="editTaskModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Edit Task</h3>
                <button onclick="document.getElementById('editTaskModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="editTaskForm" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" id="editTitle" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="editDescription" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="editStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="in_review">In Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" id="editPriority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                        <select name="assigned_to" id="editAssignee" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Unassigned</option>
                            @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" id="editDueDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editTaskModal').classList.add('hidden')" class="text-sm text-gray-500 px-4 py-2 rounded-lg border border-gray-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditTask(id, title, description, status, priority, assignedTo, dueDate) {
        document.getElementById('editTaskForm').action = '/company/tasks/' + id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editDescription').value = description;
        document.getElementById('editStatus').value = status;
        document.getElementById('editPriority').value = priority;
        document.getElementById('editAssignee').value = assignedTo || '';
        document.getElementById('editDueDate').value = dueDate || '';
        document.getElementById('editTaskModal').classList.remove('hidden');
    }
    </script>

</x-company-layout>
