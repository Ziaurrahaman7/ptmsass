<x-superadmin-layout title="Edit Company">

    <div class="max-w-2xl">
        <a href="{{ route('superadmin.companies.index') }}" class="text-sm text-gray-500 hover:text-purple-600 mb-4 inline-block">
            ← Back to Companies
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Edit: {{ $company->name }}</h2>

            <form method="POST" action="{{ route('superadmin.companies.update', $company) }}" class="space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('name') border-red-400 @enderror">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Email *</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @foreach(['active','inactive','suspended'] as $s)
                            <option value="{{ $s }}" {{ old('status', $company->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trial Ends At</label>
                        <input type="date" name="trial_ends_at"
                               value="{{ old('trial_ends_at', $company->trial_ends_at?->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
                        Save Changes
                    </button>
                    <a href="{{ route('superadmin.companies.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg border border-gray-200 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Company Users --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mt-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Users in this Company</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Role</th>
                        <th class="px-6 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($company->users as $user)
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $user->role === 'company_admin' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-4 text-gray-400 text-center">No users.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-superadmin-layout>
