<x-layouts.dashboard>
    <x-slot name="title">Dashboard</x-slot>

    @section('page-title', 'Dashboard')

    <div class="space-y-6 page-enter">

        {{-- Welcome Banner --}}
        <div class="rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 p-6 text-white shadow-lg shadow-orange-500/20">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-display font-bold text-xl mb-1">Welcome back! 👋</h2>
                    <p class="text-orange-100 text-sm">Here's what's happening with your properties today.</p>
                </div>
                <a href="/dashboard/listings/create" class="btn bg-white text-orange-600 hover:bg-orange-50 border-transparent btn-sm flex-shrink-0 shadow-sm" id="dash-create-listing">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    New Listing
                </a>
            </div>
        </div>

        {{-- Key Metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Key metrics">

            <div class="card p-5" role="region" aria-label="Active listings">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    </div>
                    <span class="badge badge-success text-xs">Active</span>
                </div>
                <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mb-0.5">0</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Listings</p>
            </div>

            <div class="card p-5" role="region" aria-label="Pending inquiries">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                    </div>
                    <span class="badge badge-warning text-xs">Pending</span>
                </div>
                <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mb-0.5">0</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">New Inquiries</p>
            </div>

            <div class="card p-5" role="region" aria-label="Bookings">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                    </div>
                </div>
                <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mb-0.5">0</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Bookings</p>
            </div>

            <div class="card p-5" role="region" aria-label="Saved properties">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                    </div>
                </div>
                <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mb-0.5">0</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Saved Properties</p>
            </div>
        </div>

        {{-- Empty State: My Listings --}}
        <div class="card">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-[#273244]">
                <h3 class="font-display font-semibold text-gray-900 dark:text-white">My Listings</h3>
                <a href="/dashboard/listings" class="btn btn-ghost btn-sm text-orange-500 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-500/10">
                    View all
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
            <div class="empty-state py-14">
                <svg class="w-12 h-12 text-gray-200 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                <h4 class="font-display font-semibold text-gray-900 dark:text-white text-lg mb-2">No listings yet</h4>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6 max-w-xs">Create your first property listing to start reaching potential buyers and renters.</p>
                <a href="/dashboard/listings/create" class="btn btn-primary btn-sm" id="dash-empty-create-listing">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Create First Listing
                </a>
            </div>
        </div>

        {{-- Recent Activity (placeholder) --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-[#273244]">
                <h3 class="font-display font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
            </div>
            <div class="empty-state py-12">
                <svg class="w-10 h-10 text-gray-200 dark:text-gray-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Your activity will appear here</p>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
