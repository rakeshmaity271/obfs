<?php

namespace LaravelObfuscator\LaravelObfuscator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelObfuscator\LaravelObfuscator\Models\Project;
use LaravelObfuscator\LaravelObfuscator\Models\AuditLog;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use LaravelObfuscator\LaravelObfuscator\Services\DeobfuscatorService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProjectManagementController extends Controller
{
    protected $obfuscatorService;
    protected $deobfuscatorService;

    public function __construct(ObfuscatorService $obfuscatorService, DeobfuscatorService $deobfuscatorService)
    {
        $this->obfuscatorService = $obfuscatorService;
        $this->deobfuscatorService = $deobfuscatorService;
    }

    /**
     * Display a listing of projects.
     */
    public function index(Request $request): JsonResponse
    {
        $projects = Project::with(['user', 'files'])
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('last_activity_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $projects,
            'message' => 'Projects retrieved successfully'
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:obfuscator_users,id',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => $request->user_id,
            'status' => 'active',
            'settings' => $request->settings ?? [],
            'storage_used' => 0,
            'file_count' => 0,
            'last_activity_at' => now()
        ]);

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'project_created',
            'project',
            $project->id,
            ['name' => $project->name, 'user_id' => $project->user_id]
        );

        return response()->json([
            'success' => true,
            'data' => $project->load('user'),
            'message' => 'Project created successfully'
        ], 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): JsonResponse
    {
        $project->load(['user', 'files', 'auditLogs']);

        return response()->json([
            'success' => true,
            'data' => $project,
            'message' => 'Project retrieved successfully'
        ]);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,archived,deleted',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $oldData = $project->toArray();
        
        $project->update($request->only([
            'name', 'description', 'status', 'settings'
        ]));

        if ($request->has('name') || $request->has('description')) {
            $project->update(['last_activity_at' => now()]);
        }

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'project_updated',
            'project',
            $project->id,
            [
                'old_data' => $oldData,
                'new_data' => $project->toArray()
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $project->fresh()->load('user', 'files'),
            'message' => 'Project updated successfully'
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        // Log the action before deletion
        AuditLog::logAction(
            $request->user()?->id,
            'project_deleted',
            'project',
            $project->id,
            ['name' => $project->name, 'user_id' => $project->user_id]
        );

        // Clean up project files
        $this->cleanupProjectFiles($project);

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully'
        ]);
    }

    /**
     * Add files to project.
     */
    public function addFiles(Request $request, Project $project): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:php|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $addedFiles = [];
        $totalSize = 0;

        foreach ($request->file('files') as $file) {
            $filePath = $file->store('projects/' . $project->id . '/original', 'local');
            $fileSize = $file->getSize();

            $projectFile = $project->files()->create([
                'original_path' => $filePath,
                'filename' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $fileSize,
                'status' => 'pending'
            ]);

            $addedFiles[] = $projectFile;
            $totalSize += $fileSize;
        }

        // Update project statistics
        $project->increment('file_count', count($addedFiles));
        $project->increment('storage_used', $totalSize);
        $project->update(['last_activity_at' => now()]);

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'files_added_to_project',
            'project',
            $project->id,
            ['files_count' => count($addedFiles), 'total_size' => $totalSize]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'project' => $project->fresh()->load('files'),
                'added_files' => $addedFiles
            ],
            'message' => 'Files added to project successfully'
        ]);
    }

    /**
     * Obfuscate project files.
     */
    public function obfuscateFiles(Request $request, Project $project): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:obfuscator_project_files,id',
            'level' => 'sometimes|in:basic,advanced,enterprise',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $files = $project->files()->whereIn('id', $request->file_ids)->get();
        $obfuscatedFiles = [];
        $errors = [];

        foreach ($files as $file) {
            try {
                $outputPath = 'projects/' . $project->id . '/obfuscated/' . $file->filename;
                
                $obfuscatedCode = $this->obfuscatorService->generateAdvancedObfuscatedCode(
                    File::get(Storage::path($file->original_path)),
                    $request->level ?? 'basic',
                    $request->options ?? []
                );

                Storage::put($outputPath, $obfuscatedCode);
                
                $file->update([
                    'obfuscated_path' => $outputPath,
                    'status' => 'obfuscated',
                    'obfuscation_settings' => [
                        'level' => $request->level ?? 'basic',
                        'options' => $request->options ?? []
                    ],
                    'obfuscated_at' => now()
                ]);

                $obfuscatedFiles[] = $file;

            } catch (\Exception $e) {
                $errors[] = [
                    'file_id' => $file->id,
                    'filename' => $file->filename,
                    'error' => $e->getMessage()
                ];

                $file->update([
                    'status' => 'failed',
                    'obfuscation_settings' => [
                        'error' => $e->getMessage()
                    ]
                ]);
            }
        }

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'project_files_obfuscated',
            'project',
            $project->id,
            [
                'files_count' => count($obfuscatedFiles),
                'errors_count' => count($errors),
                'level' => $request->level ?? 'basic'
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'obfuscated_files' => $obfuscatedFiles,
                'errors' => $errors
            ],
            'message' => 'Files obfuscated successfully'
        ]);
    }

    /**
     * Deobfuscate project files.
     */
    public function deobfuscateFiles(Request $request, Project $project): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:obfuscator_project_files,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $files = $project->files()->whereIn('id', $request->file_ids)->get();
        $deobfuscatedFiles = [];
        $errors = [];

        foreach ($files as $file) {
            try {
                if (!$file->obfuscated_path) {
                    $errors[] = [
                        'file_id' => $file->id,
                        'filename' => $file->filename,
                        'error' => 'File not obfuscated'
                    ];
                    continue;
                }

                $obfuscatedCode = File::get(Storage::path($file->obfuscated_path));
                $deobfuscatedCode = $this->deobfuscatorService->deobfuscateString($obfuscatedCode);

                $deobfuscatedPath = 'projects/' . $project->id . '/deobfuscated/' . $file->filename;
                Storage::put($deobfuscatedPath, $deobfuscatedCode);

                $file->update([
                    'status' => 'restored',
                    'backup_path' => $deobfuscatedPath
                ]);

                $deobfuscatedFiles[] = $file;

            } catch (\Exception $e) {
                $errors[] = [
                    'file_id' => $file->id,
                    'filename' => $file->filename,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Log the action
        AuditLog::logAction(
            $request->user()?->id,
            'project_files_deobfuscated',
            'project',
            $project->id,
            [
                'files_count' => count($deobfuscatedFiles),
                'errors_count' => count($errors)
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'deobfuscated_files' => $deobfuscatedFiles,
                'errors' => $errors
            ],
            'message' => 'Files deobfuscated successfully'
        ]);
    }

    /**
     * Get project statistics.
     */
    public function statistics(Project $project): JsonResponse
    {
        $stats = [
            'total_files' => $project->files()->count(),
            'obfuscated_files' => $project->files()->where('status', 'obfuscated')->count(),
            'failed_files' => $project->files()->where('status', 'failed')->count(),
            'restored_files' => $project->files()->where('status', 'restored')->count(),
            'storage_used' => $project->storage_used,
            'last_activity' => $project->last_activity_at,
            'recent_actions' => $project->auditLogs()->latest()->take(10)->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Project statistics retrieved successfully'
        ]);
    }

    /**
     * Clean up project files from storage.
     */
    protected function cleanupProjectFiles(Project $project): void
    {
        $files = $project->files;
        
        foreach ($files as $file) {
            if ($file->original_path && Storage::exists($file->original_path)) {
                Storage::delete($file->original_path);
            }
            
            if ($file->obfuscated_path && Storage::exists($file->obfuscated_path)) {
                Storage::delete($file->obfuscated_path);
            }
            
            if ($file->backup_path && Storage::exists($file->backup_path)) {
                Storage::delete($file->backup_path);
            }
        }

        // Delete project directories
        $projectDirs = [
            'projects/' . $project->id . '/original',
            'projects/' . $project->id . '/obfuscated',
            'projects/' . $project->id . '/deobfuscated'
        ];

        foreach ($projectDirs as $dir) {
            if (Storage::exists($dir)) {
                Storage::deleteDirectory($dir);
            }
        }
    }
}
