<x-superadmin-layout title="New Company">

    <div class="max-w-2xl">
        <a href="{{ route('superadmin.companies.index') }}" class="text-sm text-gray-500 hover:text-purple-600 mb-4 inline-block">
            ← Back to Companies
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Create New Company</h2>

            <form method="POST" action="{{ route('superadmin.companies.store') }}" class="space-y-5">
                @csrf

                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Company Info</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('name') border-red-400 @enderror">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trial Ends At</label>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <hr class="border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Company Admin Account</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Name *</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('admin_name') border-red-400 @enderror">
                        @error('admin_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email *</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('admin_email') border-red-400 @enderror">
                        @error('admin_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Password *</label>
                        <input type="password" name="admin_password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 @error('admin_password') border-red-400 @enderror">
                        @error('admin_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
                        Create Company
                    </button>
                    <a href="{{ route('superadmin.companies.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg border border-gray-200 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-superadmin-layout>
