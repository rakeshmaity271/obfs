<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Obfuscator Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        .drag-area {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .drag-area.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
        .file-card {
            transition: all 0.3s ease;
        }
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div x-data="obfuscatorApp()" class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt text-3xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">Laravel Obfuscator</h1>
                            <p class="text-sm text-gray-500">Professional PHP Code Obfuscation</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button @click="refreshStats()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <div class="text-sm text-gray-500">
                            <span x-text="stats.last_activity ? formatDate(stats.last_activity) : 'No activity'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-code text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Backups</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.total_backups || 0"></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lock text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Obfuscated Files</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.total_obfuscated || 0"></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hdd text-2xl text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Storage Used</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.storage_used || '0 B'"></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-2xl text-orange-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Last Activity</p>
                            <p class="text-lg font-semibold text-gray-900" x-text="stats.last_activity ? formatRelativeTime(stats.last_activity) : 'Never'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Upload Section -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Upload PHP Files</h2>
                    <p class="text-sm text-gray-500">Drag and drop PHP files or click to browse</p>
                </div>
                
                <div class="p-6">
                    <!-- Single File Upload -->
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Single File Upload</h3>
                        <div class="drag-area border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 transition-colors"
                             :class="{ 'dragover': isDragging }"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="handleSingleFileDrop($event)"
                             @click="$refs.singleFileInput.click()">
                            
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-lg text-gray-600 mb-2">Drop your PHP file here</p>
                            <p class="text-sm text-gray-500">or click to browse</p>
                            
                            <input type="file" x-ref="singleFileInput" @change="handleSingleFileSelect($event)" 
                                   accept=".php" class="hidden">
                        </div>
                        
                        <!-- Upload Options -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Obfuscation Level</label>
                                <select x-model="singleFileOptions.obfuscation_level" 
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="basic">Basic</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="enterprise">Enterprise</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" x-model="singleFileOptions.create_backup" 
                                       id="single-backup" class="mr-2">
                                <label for="single-backup" class="text-sm text-gray-700">Create Backup</label>
                            </div>
                            
                            <div>
                                <button @click="uploadSingleFile()" 
                                        :disabled="!selectedSingleFile || isUploading"
                                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!isUploading">Upload & Obfuscate</span>
                                    <span x-show="isUploading">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Batch File Upload -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Batch File Upload</h3>
                        <div class="drag-area border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 transition-colors"
                             :class="{ 'dragover': isBatchDragging }"
                             @dragover.prevent="isBatchDragging = true"
                             @dragleave.prevent="isBatchDragging = false"
                             @drop.prevent="handleBatchFileDrop($event)"
                             @click="$refs.batchFileInput.click()">
                            
                            <i class="fas fa-files-o text-4xl text-gray-400 mb-4"></i>
                            <p class="text-lg text-gray-600 mb-2">Drop multiple PHP files here</p>
                            <p class="text-sm text-gray-500">or click to browse (max 10 files)</p>
                            
                            <input type="file" x-ref="batchFileInput" @change="handleBatchFileSelect($event)" 
                                   accept=".php" multiple class="hidden">
                        </div>
                        
                        <!-- Batch Upload Options -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Obfuscation Level</label>
                                <select x-model="batchFileOptions.obfuscation_level" 
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="basic">Basic</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="enterprise">Enterprise</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" x-model="batchFileOptions.create_backup" 
                                       id="batch-backup" class="mr-2">
                                <label for="batch-backup" class="text-sm text-gray-700">Create Backup</label>
                            </div>
                            
                            <div>
                                <button @click="uploadBatchFiles()" 
                                        :disabled="selectedBatchFiles.length === 0 || isBatchUploading"
                                        class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!isBatchUploading">Upload & Obfuscate All</span>
                                    <span x-show="isBatchUploading">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Section -->
            <div x-show="uploadProgress.length > 0" class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Upload Progress</h2>
                </div>
                
                <div class="p-6">
                    <template x-for="(progress, index) in uploadProgress" :key="index">
                        <div class="mb-4 last:mb-0">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700" x-text="progress.filename"></span>
                                <span class="text-sm text-gray-500" x-text="progress.status"></span>
                            </div>
                            
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="progress-bar bg-blue-600 h-2 rounded-full" 
                                     :style="`width: ${progress.percentage}%`"></div>
                            </div>
                            
                            <div x-show="progress.status === 'completed'" class="mt-2 flex items-center space-x-2">
                                <a :href="`/obfuscator/download/${progress.download_token}`" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                                <span class="text-sm text-gray-500" x-text="`Size: ${progress.file_size}`"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Recent Backups -->
            <div x-show="backups.length > 0" class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Recent Backups</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="backup in backups" :key="backup.name">
                            <div class="file-card bg-gray-50 rounded-lg p-4 hover:bg-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-archive text-blue-600 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900" x-text="backup.name"></p>
                                            <p class="text-xs text-gray-500" x-text="formatBytes(backup.size)"></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500" x-text="formatDate(backup.created)"></p>
                                        <button @click="restoreBackup(backup)" 
                                                class="text-blue-600 hover:text-blue-800 text-xs">
                                            <i class="fas fa-undo mr-1"></i>Restore
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Success/Error Toast -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-4 right-4 z-50">
        
        <div :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'"
             class="text-white px-6 py-3 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"
                   class="mr-2"></i>
                <span x-text="toast.message"></span>
            </div>
        </div>
    </div>

    <script>
        function obfuscatorApp() {
            return {
                // State
                isDragging: false,
                isBatchDragging: false,
                isUploading: false,
                isBatchUploading: false,
                selectedSingleFile: null,
                selectedBatchFiles: [],
                uploadProgress: [],
                backups: @json($backups ?? []),
                stats: @json($stats ?? []),
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                // Options
                singleFileOptions: {
                    obfuscation_level: 'basic',
                    create_backup: true
                },
                batchFileOptions: {
                    obfuscation_level: 'basic',
                    create_backup: true
                },

                // Methods
                handleSingleFileDrop(e) {
                    this.isDragging = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.selectedSingleFile = files[0];
                    }
                },

                handleSingleFileSelect(e) {
                    this.selectedSingleFile = e.target.files[0];
                },

                handleBatchFileDrop(e) {
                    this.isBatchDragging = false;
                    const files = Array.from(e.dataTransfer.files);
                    this.selectedBatchFiles = files.slice(0, 10); // Max 10 files
                },

                handleBatchFileSelect(e) {
                    const files = Array.from(e.target.files);
                    this.selectedBatchFiles = files.slice(0, 10); // Max 10 files
                },

                async uploadSingleFile() {
                    if (!this.selectedSingleFile) return;

                    this.isUploading = true;
                    const formData = new FormData();
                    formData.append('php_file', this.selectedSingleFile);
                    formData.append('create_backup', this.singleFileOptions.create_backup);
                    formData.append('obfuscation_level', this.singleFileOptions.obfuscation_level);

                    try {
                        const response = await fetch('/obfuscator/upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.showToast('File obfuscated successfully!', 'success');
                            this.uploadProgress.push({
                                filename: result.data.original_name,
                                status: 'completed',
                                percentage: 100,
                                download_token: result.data.download_token,
                                file_size: this.formatBytes(result.data.file_size)
                            });
                            this.refreshStats();
                        } else {
                            this.showToast(result.message || 'Upload failed', 'error');
                        }
                    } catch (error) {
                        this.showToast('Upload failed: ' + error.message, 'error');
                    } finally {
                        this.isUploading = false;
                        this.selectedSingleFile = null;
                    }
                },

                async uploadBatchFiles() {
                    if (this.selectedBatchFiles.length === 0) return;

                    this.isBatchUploading = true;
                    const formData = new FormData();
                    
                    this.selectedBatchFiles.forEach(file => {
                        formData.append('php_files[]', file);
                    });
                    
                    formData.append('create_backup', this.batchFileOptions.create_backup);
                    formData.append('obfuscation_level', this.batchFileOptions.obfuscation_level);

                    try {
                        const response = await fetch('/obfuscator/batch-upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.showToast(`Batch processing completed: ${result.data.successful} successful, ${result.data.failed} failed`, 'success');
                            
                            result.data.results.forEach(fileResult => {
                                if (fileResult.status === 'success') {
                                    this.uploadProgress.push({
                                        filename: fileResult.file,
                                        status: 'completed',
                                        percentage: 100,
                                        download_token: fileResult.download_token,
                                        file_size: this.formatBytes(fileResult.file_size)
                                    });
                                }
                            });
                            
                            this.refreshStats();
                        } else {
                            this.showToast(result.message || 'Batch upload failed', 'error');
                        }
                    } catch (error) {
                        this.showToast('Batch upload failed: ' + error.message, 'error');
                    } finally {
                        this.isBatchUploading = false;
                        this.selectedBatchFiles = [];
                    }
                },

                async refreshStats() {
                    try {
                        const response = await fetch('/obfuscator/stats');
                        const result = await response.json();
                        if (result.success) {
                            this.stats = result.data;
                        }
                    } catch (error) {
                        console.error('Failed to refresh stats:', error);
                    }
                },

                async restoreBackup(backup) {
                    try {
                        const response = await fetch('/obfuscator/restore', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ backup_file: backup.name })
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            this.showToast('Backup restored successfully!', 'success');
                            this.refreshStats();
                        } else {
                            this.showToast(result.message || 'Restore failed', 'error');
                        }
                    } catch (error) {
                        this.showToast('Restore failed: ' + error.message, 'error');
                    }
                },

                showToast(message, type = 'success') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 5000);
                },

                formatBytes(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                formatDate(timestamp) {
                    if (!timestamp) return 'Never';
                    const date = new Date(timestamp * 1000);
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                },

                formatRelativeTime(timestamp) {
                    if (!timestamp) return 'Never';
                    const now = Math.floor(Date.now() / 1000);
                    const diff = now - timestamp;
                    
                    if (diff < 60) return 'Just now';
                    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
                    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
                    return Math.floor(diff / 86400) + ' days ago';
                }
            }
        }
    </script>
</body>
</html>
