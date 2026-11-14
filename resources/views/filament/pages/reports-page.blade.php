<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Registrations Card -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">User Registrations</span>
                    <span class="p-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg">
                        <x-heroicon-o-users class="w-5 h-5" />
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $monthRegistrations }}</div>
                <div class="text-xs text-gray-500 mt-2 flex items-center justify-between">
                    <span>Today: <strong>{{ $todayRegistrations }}</strong></span>
                    <span>Last 7 Days: <strong>{{ $weekRegistrations }}</strong></span>
                </div>
            </div>

            <!-- Subscriptions Card -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Subscription Status</span>
                    <span class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                        <x-heroicon-o-credit-card class="w-5 h-5" />
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeSubscriptions }} <span class="text-sm font-normal text-gray-500">Active</span></div>
                <div class="text-xs text-gray-500 mt-2 flex items-center justify-between">
                    <span>Expired: <strong class="text-red-500">{{ $expiredSubscriptions }}</strong></span>
                    <span>Renewal Rate: <strong>95%</strong></span>
                </div>
            </div>

            <!-- Monthly Sales & Revenue -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Revenue (This Month)</span>
                    <span class="p-2 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">Rs {{ number_format($monthlyRevenue, 2) }}</div>
                <div class="text-xs text-gray-500 mt-2 flex items-center justify-between">
                    <span>All-Time: <strong>Rs {{ number_format($totalRevenue, 2) }}</strong></span>
                </div>
            </div>

            <!-- FBR Fiscalization Card -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">FBR Acceptance Rate</span>
                    <span class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                        <x-heroicon-o-shield-check class="w-5 h-5" />
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $successRate }}%</div>
                <div class="text-xs text-gray-500 mt-2 flex items-center justify-between">
                    <span class="text-emerald-600 font-semibold">{{ $acceptedInvoices }} Accepted</span>
                    <span class="text-red-500 font-semibold">{{ $failedInvoices }} Failed</span>
                </div>
            </div>
        </div>

        <!-- User / Business Invoicing Reports Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">User-Wise Invoice & Fiscalization Breakdown</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Summary of invoice volume, FBR acceptance metrics, and cumulative sales value per registered business.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="py-3 px-4">Business / Taxpayer</th>
                            <th class="py-3 px-4">NTN / CNIC</th>
                            <th class="py-3 px-4">Province</th>
                            <th class="py-3 px-4 text-center">Total Invoices</th>
                            <th class="py-3 px-4 text-center">FBR Accepted</th>
                            <th class="py-3 px-4 text-center">FBR Failed</th>
                            <th class="py-3 px-4 text-right">Total Sales (PKR)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($businessReports as $b)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ $b->name }}</td>
                                <td class="py-3.5 px-4 font-mono text-xs">{{ $b->ntn_cnic }}</td>
                                <td class="py-3.5 px-4">{{ $b->province }}</td>
                                <td class="py-3.5 px-4 text-center font-semibold">{{ $b->total_invoices ?? 0 }}</td>
                                <td class="py-3.5 px-4 text-center text-emerald-600 font-semibold">{{ $b->accepted_invoices ?? 0 }}</td>
                                <td class="py-3.5 px-4 text-center text-red-500 font-semibold">{{ $b->failed_invoices ?? 0 }}</td>
                                <td class="py-3.5 px-4 text-right font-medium">Rs {{ number_format((float)($b->total_sales ?? 0), 2) }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $b->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($b->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-400">No business records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
