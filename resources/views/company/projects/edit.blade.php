<x-company-layout title="Edit Project">

    <div class="max-w-2xl">
        <a href="{{ route('company.projects.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 mb-4 inline-block">← Back to Projects</a>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Edit: {{ $project->name }}</h2>

            <form method="POST" action="{{ route('company.projects.update', $project) }}" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Name *</label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach(['planning'=>'Planning','in_progress'=>'In Progress','on_hold'=>'On Hold','completed'=>'Completed'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $project->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('due_date') border-red-400 @enderror">
                        @error('due_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
                        Save Changes
                    </button>
                    <a href="{{ route('company.projects.index') }}" class="text-sm text-gray-500 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-company-layout>
