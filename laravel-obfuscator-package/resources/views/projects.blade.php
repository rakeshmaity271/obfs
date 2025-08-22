<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - Laravel Obfuscator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div x-data="projectManager()" class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Project Management</h1>
                        <p class="text-gray-600">Organize and manage your obfuscation projects</p>
                    </div>
                    <button @click="showCreateModal = true" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                        Create Project
                    </button>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Projects</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.totalProjects">0</p>
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
                            <p class="text-sm font-medium text-gray-600">Active Projects</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.activeProjects">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v.01"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Files</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.totalFiles">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Storage Used</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="formatBytes(stats.storageUsed)">0 B</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               x-model="filters.search" 
                               @input="filterProjects()"
                               placeholder="Search projects..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <select x-model="filters.status" 
                            @change="filterProjects()"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                        <option value="deleted">Deleted</option>
                    </select>
                    <button @click="refreshProjects()" 
                            class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium">
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Projects List -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Projects</h3>
                </div>
                
                <div class="divide-y divide-gray-200">
                    <template x-for="project in filteredProjects" :key="project.id">
                        <div class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h4 class="text-lg font-medium text-gray-900" x-text="project.name"></h4>
                                        <span :class="getStatusBadgeClass(project.status)" 
                                              x-text="project.status" 
                                              class="px-2 py-1 text-xs font-medium rounded-full"></span>
                                    </div>
                                    <p class="text-gray-600 mt-1" x-text="project.description || 'No description'"></p>
                                    <div class="flex items-center space-x-6 mt-2 text-sm text-gray-500">
                                        <span x-text="`${project.file_count} files`"></span>
                                        <span x-text="`${formatBytes(project.storage_used)}`"></span>
                                        <span x-text="`Last activity: ${formatDate(project.last_activity_at)}`"></span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button @click="viewProject(project)" 
                                            class="px-3 py-1 text-blue-600 hover:text-blue-800 font-medium">
                                        View
                                    </button>
                                    <button @click="editProject(project)" 
                                            class="px-3 py-1 text-gray-600 hover:text-gray-800 font-medium">
                                        Edit
                                    </button>
                                    <button @click="deleteProject(project)" 
                                            class="px-3 py-1 text-red-600 hover:text-red-800 font-medium">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="filteredProjects.length === 0" class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No projects found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new project.</p>
                </div>
            </div>
        </main>

        <!-- Create Project Modal -->
        <div x-show="showCreateModal" 
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
                    <form @submit.prevent="createProject()">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Project</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                                <input type="text" 
                                       x-model="newProject.name" 
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea x-model="newProject.description" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Settings</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               x-model="newProject.settings.auto_obfuscate"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Auto-obfuscate files</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               x-model="newProject.settings.backup_files"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Backup original files</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Create Project
                            </button>
                            <button type="button" 
                                    @click="showCreateModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function projectManager() {
            return {
                projects: [],
                filteredProjects: [],
                stats: {
                    totalProjects: 0,
                    activeProjects: 0,
                    totalFiles: 0,
                    storageUsed: 0
                },
                filters: {
                    search: '',
                    status: ''
                },
                newProject: {
                    name: '',
                    description: '',
                    settings: {
                        auto_obfuscate: false,
                        backup_files: true
                    }
                },
                showCreateModal: false,
                loading: false,

                init() {
                    this.loadProjects();
                    this.loadStats();
                },

                async loadProjects() {
                    try {
                        this.loading = true;
                        const response = await fetch('/obfuscator/projects');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.projects = data.data.data || [];
                            this.filterProjects();
                        }
                    } catch (error) {
                        console.error('Error loading projects:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/obfuscator/stats');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.stats = {
                                totalProjects: data.data.total_projects || 0,
                                activeProjects: data.data.active_projects || 0,
                                totalFiles: data.data.total_files || 0,
                                storageUsed: data.data.storage_used || 0
                            };
                        }
                    } catch (error) {
                        console.error('Error loading stats:', error);
                    }
                },

                filterProjects() {
                    this.filteredProjects = this.projects.filter(project => {
                        const matchesSearch = !this.filters.search || 
                            project.name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                            (project.description && project.description.toLowerCase().includes(this.filters.search.toLowerCase()));
                        
                        const matchesStatus = !this.filters.status || project.status === this.filters.status;
                        
                        return matchesSearch && matchesStatus;
                    });
                },

                async createProject() {
                    try {
                        const response = await fetch('/obfuscator/projects', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(this.newProject)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showCreateModal = false;
                            this.resetNewProject();
                            this.loadProjects();
                            this.loadStats();
                        } else {
                            alert('Error creating project: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error creating project:', error);
                        alert('Error creating project');
                    }
                },

                resetNewProject() {
                    this.newProject = {
                        name: '',
                        description: '',
                        settings: {
                            auto_obfuscate: false,
                            backup_files: true
                        }
                    };
                },

                viewProject(project) {
                    // Navigate to project detail page
                    window.location.href = `/obfuscator/projects/${project.id}`;
                },

                editProject(project) {
                    // Implement edit functionality
                    console.log('Edit project:', project);
                },

                async deleteProject(project) {
                    if (!confirm(`Are you sure you want to delete project "${project.name}"?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/obfuscator/projects/${project.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.loadProjects();
                            this.loadStats();
                        } else {
                            alert('Error deleting project: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error deleting project:', error);
                        alert('Error deleting project');
                    }
                },

                refreshProjects() {
                    this.loadProjects();
                    this.loadStats();
                },

                getStatusBadgeClass(status) {
                    const classes = {
                        'active': 'bg-green-100 text-green-800',
                        'archived': 'bg-yellow-100 text-yellow-800',
                        'deleted': 'bg-red-100 text-red-800'
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800';
                },

                formatBytes(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                formatDate(dateString) {
                    if (!dateString) return 'Never';
                    return new Date(dateString).toLocaleDateString();
                }
            }
        }
    </script>
</body>
</html>
