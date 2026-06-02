<x-superadmin-layout title="Dashboard">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm text-gray-500 mb-1">Total Companies</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalCompanies }}</p>
            <p class="text-xs text-green-500 mt-1">{{ $activeCompanies }} active</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm text-gray-500 mb-1">Total Users</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalUsers }}</p>
            <p class="text-xs text-gray-400 mt-1">Across all companies</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm text-gray-500 mb-1">Suspended</p>
            <p class="text-3xl font-bold text-red-500">{{ $suspendedCompanies }}</p>
            <p class="text-xs text-gray-400 mt-1">Need attention</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Recent Companies</h2>
            <a href="{{ route('superadmin.companies.index') }}" class="text-sm text-purple-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentCompanies as $company)
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">{{ $company->name }}</p>
                    <p class="text-sm text-gray-400">{{ $company->email }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full font-medium
                    {{ $company->status === 'active' ? 'bg-green-100 text-green-700' : ($company->status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                    {{ ucfirst($company->status) }}
                </span>
            </div>
            @empty
            <p class="px-6 py-4 text-sm text-gray-400">No companies yet.</p>
            @endforelse
        </div>
    </div>

</x-superadmin-layout>
