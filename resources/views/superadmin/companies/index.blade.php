<x-superadmin-layout title="Companies">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">All Companies</h2>
            <p class="text-sm text-gray-500">Manage all registered companies</p>
        </div>
        <a href="{{ route('superadmin.companies.create') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + New Company
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Company</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Users</th>
                    <th class="px-6 py-3 text-left">Trial Ends</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($companies as $company)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-gray-400">{{ $company->id }}</td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-800">{{ $company->name }}</p>
                        <p class="text-xs text-gray-400">{{ $company->phone }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $company->email }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $company->users_count }}</td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $company->trial_ends_at ? $company->trial_ends_at->format('d M Y') : '—' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full font-medium
                            {{ $company->status === 'active' ? 'bg-green-100 text-green-700' : ($company->status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('superadmin.companies.edit', $company) }}"
                               class="text-blue-500 hover:text-blue-700 font-medium">Edit</a>

                            <form method="POST" action="{{ route('superadmin.companies.toggle', $company) }}">
                                @csrf @method('PATCH')
                                <button class="text-{{ $company->status === 'active' ? 'yellow' : 'green' }}-500 hover:underline font-medium">
                                    {{ $company->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('superadmin.companies.destroy', $company) }}"
                                  onsubmit="return confirm('Delete {{ $company->name }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-400">No companies found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($companies->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $companies->links() }}
        </div>
        @endif
    </div>

</x-superadmin-layout>
