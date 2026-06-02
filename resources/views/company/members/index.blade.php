<x-company-layout title="Members">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Team Members</h2>
            <p class="text-sm text-gray-500">Manage your company's members</p>
        </div>
        <button onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Member
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-left">Role</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($members as $member)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-400 flex items-center justify-center text-white text-xs font-bold">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-800">{{ $member->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $member->email }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $member->role === 'company_admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $member->role === 'company_admin' ? 'Admin' : 'Employee' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $member->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($member->id !== auth()->id())
                        <form method="POST" action="{{ route('company.members.toggle', $member) }}">
                            @csrf @method('PATCH')
                            <button class="text-xs text-gray-500 hover:text-indigo-600 underline">
                                {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-gray-300">You</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-400">No members yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add Member Modal --}}
    <div id="addMemberModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Add Member</h3>
                <button onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('company.members.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">Add Member</button>
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="text-sm text-gray-500 px-4 py-2 rounded-lg border border-gray-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</x-company-layout>
