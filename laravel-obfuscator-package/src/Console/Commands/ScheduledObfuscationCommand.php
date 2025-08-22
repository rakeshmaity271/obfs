<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Models\Project;
use LaravelObfuscator\LaravelObfuscator\Models\AuditLog;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ScheduledObfuscationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:scheduled 
                            {--project= : Specific project ID to process}
                            {--force : Force obfuscation even if not scheduled}
                            {--dry-run : Show what would be processed without executing}';

    /**
     * The console command description.
     */
    protected $description = 'Run scheduled obfuscation tasks for projects';

    protected $obfuscatorService;

    public function __construct(ObfuscatorService $obfuscatorService)
    {
        parent::__construct();
        $this->obfuscatorService = $obfuscatorService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting scheduled obfuscation process...');

        $projectId = $this->option('project');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No actual obfuscation will be performed');
        }

        // Get projects to process
        $query = Project::with(['files' => function ($query) {
            $query->where('status', 'pending')
                  ->orWhere('status', 'failed');
        }]);

        if ($projectId) {
            $query->where('id', $projectId);
        }

        $projects = $query->get();

        if ($projects->isEmpty()) {
            $this->info('No projects found for scheduled obfuscation.');
            return 0;
        }

        $this->info("Found {$projects->count()} project(s) to process.");

        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalErrors = 0;

        foreach ($projects as $project) {
            $this->info("\nProcessing project: {$project->name} (ID: {$project->id})");

            $filesToProcess = $project->files->filter(function ($file) use ($force) {
                // Check if file should be processed based on schedule
                if ($force) {
                    return true;
                }

                // Check project settings for scheduling
                $settings = $project->settings ?? [];
                $scheduleEnabled = $settings['auto_obfuscate'] ?? false;
                
                if (!$scheduleEnabled) {
                    return false;
                }

                // Check if enough time has passed since last attempt
                $lastAttempt = $file->updated_at;
                $minInterval = $settings['min_interval_hours'] ?? 24;
                
                return $lastAttempt->diffInHours(now()) >= $minInterval;
            });

            if ($filesToProcess->isEmpty()) {
                $this->info("  No files to process for this project.");
                continue;
            }

            $this->info("  Found {$filesToProcess->count()} file(s) to process.");

            foreach ($filesToProcess as $file) {
                $this->line("    Processing: {$file->filename}");

                if ($dryRun) {
                    $this->line("      [DRY RUN] Would obfuscate file");
                    $totalProcessed++;
                    continue;
                }

                try {
                    $result = $this->processFile($file, $project);
                    
                    if ($result) {
                        $this->info("      ✓ Successfully obfuscated");
                        $totalSuccess++;
                    } else {
                        $this->error("      ✗ Failed to obfuscate");
                        $totalErrors++;
                    }

                    $totalProcessed++;

                } catch (\Exception $e) {
                    $this->error("      ✗ Error: " . $e->getMessage());
                    $totalErrors++;
                    $totalProcessed++;

                    // Log the error
                    AuditLog::logAction(
                        null, // System action
                        'scheduled_obfuscation_failed',
                        'project_file',
                        $file->id,
                        [
                            'project_id' => $project->id,
                            'filename' => $file->filename,
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }

            // Update project last activity
            if (!$dryRun) {
                $project->update(['last_activity_at' => now()]);
            }
        }

        // Summary
        $this->newLine();
        $this->info('Scheduled obfuscation process completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Files Processed', $totalProcessed],
                ['Successful', $totalSuccess],
                ['Errors', $totalErrors],
            ]
        );

        // Log the scheduled run
        if (!$dryRun) {
            AuditLog::logAction(
                null, // System action
                'scheduled_obfuscation_completed',
                'system',
                null,
                [
                    'total_processed' => $totalProcessed,
                    'total_success' => $totalSuccess,
                    'total_errors' => $totalErrors,
                    'projects_processed' => $projects->count()
                ]
            );
        }

        return $totalErrors === 0 ? 0 : 1;
    }

    /**
     * Process a single file for obfuscation.
     */
    protected function processFile($file, $project): bool
    {
        try {
            // Get obfuscation settings from project
            $settings = $project->settings ?? [];
            $level = $settings['obfuscation_level'] ?? 'basic';
            $options = $settings['obfuscation_options'] ?? [];

            // Read original file
            $originalContent = File::get(Storage::path($file->original_path));
            
            // Generate obfuscated code
            $obfuscatedCode = $this->obfuscatorService->generateAdvancedObfuscatedCode(
                $originalContent,
                $level,
                $options
            );

            // Save obfuscated file
            $outputPath = 'projects/' . $project->id . '/obfuscated/' . $file->filename;
            Storage::put($outputPath, $obfuscatedCode);

            // Update file record
            $file->update([
                'obfuscated_path' => $outputPath,
                'status' => 'obfuscated',
                'obfuscation_settings' => [
                    'level' => $level,
                    'options' => $options,
                    'scheduled_at' => now()->toISOString()
                ],
                'obfuscated_at' => now()
            ]);

            // Log the successful obfuscation
            AuditLog::logAction(
                null, // System action
                'scheduled_file_obfuscated',
                'project_file',
                $file->id,
                [
                    'project_id' => $project->id,
                    'filename' => $file->filename,
                    'level' => $level,
                    'options' => $options
                ]
            );

            return true;

        } catch (\Exception $e) {
            // Update file status to failed
            $file->update([
                'status' => 'failed',
                'obfuscation_settings' => [
                    'error' => $e->getMessage(),
                    'scheduled_at' => now()->toISOString()
                ]
            ]);

            throw $e;
        }
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['project', 'p', InputOption::VALUE_OPTIONAL, 'Specific project ID to process'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force obfuscation even if not scheduled'],
            ['dry-run', 'd', InputOption::VALUE_NONE, 'Show what would be processed without executing'],
        ];
    }
}
