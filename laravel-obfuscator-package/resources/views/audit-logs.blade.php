<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Laravel Obfuscator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div x-data="auditLogManager()" class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Audit Logs</h1>
                        <p class="text-gray-600">Monitor and analyze system activities</p>
                    </div>
                    <div class="flex space-x-3">
                        <button @click="exportLogs()" 
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                            Export CSV
                        </button>
                        <button @click="showCleanupModal = true" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                            Cleanup
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Logs</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.totalLogs">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Success</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.successLogs">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Failed</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.failedLogs">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Warnings</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.warningLogs">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Actions Breakdown</h3>
                    <canvas id="actionsChart" width="400" height="200"></canvas>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Activity</h3>
                    <canvas id="activityChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" 
                               x-model="filters.search" 
                               @input="filterLogs()"
                               placeholder="Search logs..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select x-model="filters.status" 
                                @change="filterLogs()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Statuses</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                            <option value="warning">Warning</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
                        <select x-model="filters.action" 
                                @change="filterLogs()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Actions</option>
                            <option value="user_created">User Created</option>
                            <option value="user_updated">User Updated</option>
                            <option value="user_deleted">User Deleted</option>
                            <option value="project_created">Project Created</option>
                            <option value="project_updated">Project Updated</option>
                            <option value="project_deleted">Project Deleted</option>
                            <option value="file_obfuscated">File Obfuscated</option>
                            <option value="file_deobfuscated">File Deobfuscated</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <div class="flex space-x-2">
                            <input type="date" 
                                   x-model="filters.dateFrom" 
                                   @change="filterLogs()"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <input type="date" 
                                   x-model="filters.dateTo" 
                                   @change="filterLogs()"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center mt-4">
                    <button @click="refreshLogs()" 
                            class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium">
                        Refresh
                    </button>
                    <div class="text-sm text-gray-600">
                        Showing <span x-text="filteredLogs.length"></span> of <span x-text="logs.length"></span> logs
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Activity Logs</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resource</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="log in paginatedLogs" :key="log.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900" x-text="log.action"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div x-show="log.user">
                                            <div class="text-sm text-gray-900" x-text="log.user.name"></div>
                                            <div class="text-sm text-gray-500" x-text="log.user.email"></div>
                                        </div>
                                        <span x-show="!log.user" class="text-sm text-gray-500">System</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div x-show="log.resource_type">
                                            <div class="text-sm text-gray-900" x-text="log.resource_type"></div>
                                            <div class="text-sm text-gray-500" x-text="log.resource_id || 'N/A'"></div>
                                        </div>
                                        <span x-show="!log.resource_type" class="text-sm text-gray-500">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusBadgeClass(log.status)" 
                                              x-text="log.status" 
                                              class="px-2 py-1 text-xs font-medium rounded-full"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="log.ip_address || 'N/A'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(log.created_at)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="viewLogDetails(log)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button @click="previousPage()" 
                                    :disabled="currentPage === 1"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                Previous
                            </button>
                            <button @click="nextPage()" 
                                    :disabled="currentPage >= totalPages"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                Next
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span x-text="((currentPage - 1) * pageSize) + 1"></span> to 
                                    <span x-text="Math.min(currentPage * pageSize, filteredLogs.length)"></span> of 
                                    <span x-text="filteredLogs.length"></span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <button @click="previousPage()" 
                                            :disabled="currentPage === 1"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                        Previous
                                    </button>
                                    <template x-for="page in visiblePages" :key="page">
                                        <button @click="goToPage(page)" 
                                                :class="page === currentPage ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                            <span x-text="page"></span>
                                        </button>
                                    </template>
                                    <button @click="nextPage()" 
                                            :disabled="currentPage >= totalPages"
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                        Next
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Log Details Modal -->
        <div x-show="showDetailsModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Log Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Action</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedLog?.action"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span :class="getStatusBadgeClass(selectedLog?.status)" 
                                      x-text="selectedLog?.status" 
                                      class="mt-1 inline-block px-2 py-1 text-xs font-medium rounded-full"></span>
                            </div>
                            
                            <div x-show="selectedLog?.user">
                                <label class="block text-sm font-medium text-gray-700">User</label>
                                <div class="mt-1 text-sm text-gray-900">
                                    <p x-text="selectedLog?.user?.name"></p>
                                    <p x-text="selectedLog?.user?.email"></p>
                                </div>
                            </div>
                            
                            <div x-show="selectedLog?.resource_type">
                                <label class="block text-sm font-medium text-gray-700">Resource</label>
                                <div class="mt-1 text-sm text-gray-900">
                                    <p>Type: <span x-text="selectedLog?.resource_type"></span></p>
                                    <p>ID: <span x-text="selectedLog?.resource_id || 'N/A'"></span></p>
                                </div>
                            </div>
                            
                            <div x-show="selectedLog?.details">
                                <label class="block text-sm font-medium text-gray-700">Details</label>
                                <pre class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded overflow-auto" x-text="JSON.stringify(selectedLog?.details, null, 2)"></pre>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IP Address</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedLog?.ip_address || 'N/A'"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">User Agent</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedLog?.user_agent || 'N/A'"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Timestamp</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="formatDate(selectedLog?.created_at)"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                @click="showDetailsModal = false"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cleanup Modal -->
        <div x-show="showCleanupModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Cleanup Old Logs</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keep logs for (days)</label>
                            <input type="number" 
                                   x-model="cleanupDays" 
                                   min="30" 
                                   max="1095"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Minimum: 30 days, Maximum: 3 years (1095 days)</p>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>This action will permanently delete old audit logs. This cannot be undone.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                @click="performCleanup()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Cleanup Logs
                        </button>
                        <button type="button" 
                                @click="showCleanupModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function auditLogManager() {
            return {
                logs: [],
                filteredLogs: [],
                paginatedLogs: [],
                stats: {
                    totalLogs: 0,
                    successLogs: 0,
                    failedLogs: 0,
                    warningLogs: 0
                },
                filters: {
                    search: '',
                    status: '',
                    action: '',
                    dateFrom: '',
                    dateTo: ''
                },
                selectedLog: null,
                showDetailsModal: false,
                showCleanupModal: false,
                cleanupDays: 365,
                currentPage: 1,
                pageSize: 20,
                loading: false,

                init() {
                    this.loadLogs();
                    this.loadStats();
                    this.loadAnalytics();
                },

                async loadLogs() {
                    try {
                        this.loading = true;
                        const response = await fetch('/obfuscator/audit-logs');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.logs = data.data.data || [];
                            this.filterLogs();
                        }
                    } catch (error) {
                        console.error('Error loading logs:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/obfuscator/audit-logs/analytics/statistics');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.stats = {
                                totalLogs: data.data.total_logs || 0,
                                successLogs: data.data.success_logs || 0,
                                failedLogs: data.data.failed_logs || 0,
                                warningLogs: data.data.warning_logs || 0
                            };
                        }
                    } catch (error) {
                        console.error('Error loading stats:', error);
                    }
                },

                async loadAnalytics() {
                    try {
                        const response = await fetch('/obfuscator/audit-logs/analytics/statistics');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.renderCharts(data.data);
                        }
                    } catch (error) {
                        console.error('Error loading analytics:', error);
                    }
                },

                renderCharts(data) {
                    // Actions Breakdown Chart
                    const actionsCtx = document.getElementById('actionsChart');
                    if (actionsCtx && data.actions_breakdown) {
                        new Chart(actionsCtx, {
                            type: 'doughnut',
                            data: {
                                labels: data.actions_breakdown.map(item => item.action),
                                datasets: [{
                                    data: data.actions_breakdown.map(item => item.count),
                                    backgroundColor: [
                                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
                                        '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    }

                    // Daily Activity Chart
                    const activityCtx = document.getElementById('activityChart');
                    if (activityCtx && data.daily_activity) {
                        new Chart(activityCtx, {
                            type: 'line',
                            data: {
                                labels: data.daily_activity.map(item => item.date),
                                datasets: [{
                                    label: 'Logs per Day',
                                    data: data.daily_activity.map(item => item.count),
                                    borderColor: '#3B82F6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                },

                filterLogs() {
                    this.filteredLogs = this.logs.filter(log => {
                        const matchesSearch = !this.filters.search || 
                            log.action.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                            (log.details && JSON.stringify(log.details).toLowerCase().includes(this.filters.search.toLowerCase()));
                        
                        const matchesStatus = !this.filters.status || log.status === this.filters.status;
                        const matchesAction = !this.filters.action || log.action === this.filters.action;
                        
                        let matchesDate = true;
                        if (this.filters.dateFrom) {
                            matchesDate = matchesDate && new Date(log.created_at) >= new Date(this.filters.dateFrom);
                        }
                        if (this.filters.dateTo) {
                            matchesDate = matchesDate && new Date(log.created_at) <= new Date(this.filters.dateTo);
                        }
                        
                        return matchesSearch && matchesStatus && matchesAction && matchesDate;
                    });

                    this.currentPage = 1;
                    this.updatePagination();
                },

                updatePagination() {
                    const start = (this.currentPage - 1) * this.pageSize;
                    const end = start + this.pageSize;
                    this.paginatedLogs = this.filteredLogs.slice(start, end);
                },

                get totalPages() {
                    return Math.ceil(this.filteredLogs.length / this.pageSize);
                },

                get visiblePages() {
                    const pages = [];
                    const start = Math.max(1, this.currentPage - 2);
                    const end = Math.min(this.totalPages, this.currentPage + 2);
                    
                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    
                    return pages;
                },

                goToPage(page) {
                    this.currentPage = page;
                    this.updatePagination();
                },

                previousPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.updatePagination();
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.updatePagination();
                    }
                },

                viewLogDetails(log) {
                    this.selectedLog = log;
                    this.showDetailsModal = true;
                },

                async exportLogs() {
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.search) params.append('search', this.filters.search);
                        if (this.filters.status) params.append('status', this.filters.status);
                        if (this.filters.action) params.append('action', this.filters.action);
                        if (this.filters.dateFrom) params.append('date_from', this.filters.dateFrom);
                        if (this.filters.dateTo) params.append('date_to', this.filters.dateTo);

                        const response = await fetch(`/obfuscator/audit-logs/export/csv?${params.toString()}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            // Create download link
                            const link = document.createElement('a');
                            link.href = data.data.download_url;
                            link.download = data.data.filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            alert('Error exporting logs: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error exporting logs:', error);
                        alert('Error exporting logs');
                    }
                },

                async performCleanup() {
                    if (!confirm(`Are you sure you want to delete logs older than ${this.cleanupDays} days?`)) {
                        return;
                    }

                    try {
                        const response = await fetch('/obfuscator/audit-logs/cleanup/old', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ days: this.cleanupDays })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showCleanupModal = false;
                            this.loadLogs();
                            this.loadStats();
                            alert(`Successfully deleted ${data.data.deleted_count} old logs.`);
                        } else {
                            alert('Error cleaning up logs: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error cleaning up logs:', error);
                        alert('Error cleaning up logs');
                    }
                },

                refreshLogs() {
                    this.loadLogs();
                    this.loadStats();
                    this.loadAnalytics();
                },

                getStatusBadgeClass(status) {
                    const classes = {
                        'success': 'bg-green-100 text-green-800',
                        'failed': 'bg-red-100 text-red-800',
                        'warning': 'bg-yellow-100 text-yellow-800'
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800';
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleString();
                }
            }
        }
    </script>
</body>
</html>
