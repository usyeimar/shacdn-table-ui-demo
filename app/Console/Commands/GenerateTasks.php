<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:generate 
                            {--count=100 : Number of tasks to generate}
                            {--clear : Clear existing tasks before generating}
                            {--completed=30 : Percentage of completed tasks}
                            {--in-progress=25 : Percentage of in-progress tasks}
                            {--pending=35 : Percentage of pending tasks}
                            {--cancelled=5 : Percentage of cancelled tasks}
                            {--overdue=5 : Number of overdue tasks}
                            {--due-soon=5 : Number of tasks due soon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample tasks using the factory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->error('No users found. Please create users first.');
            return 1;
        }

        $count = (int) $this->option('count');
        $clear = $this->option('clear');

        if ($clear) {
            $this->info('Clearing existing tasks...');
            Task::truncate();
        }

        $this->info("Generating {$count} tasks...");

        // Calculate distribution
        $completedCount = (int) ($count * ($this->option('completed') / 100));
        $inProgressCount = (int) ($count * ($this->option('in-progress') / 100));
        $pendingCount = (int) ($count * ($this->option('pending') / 100));
        $cancelledCount = (int) ($count * ($this->option('cancelled') / 100));
        $overdueCount = (int) $this->option('overdue');
        $dueSoonCount = (int) $this->option('due-soon');

        // Adjust pending count to account for overdue and due soon tasks
        $pendingCount = $pendingCount - $overdueCount - $dueSoonCount;

        // Create tasks with progress bar
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        // Create completed tasks
        if ($completedCount > 0) {
            Task::factory($completedCount)->completed()->create();
            $progressBar->advance($completedCount);
        }

        // Create in-progress tasks
        if ($inProgressCount > 0) {
            Task::factory($inProgressCount)->inProgress()->create();
            $progressBar->advance($inProgressCount);
        }

        // Create pending tasks
        if ($pendingCount > 0) {
            Task::factory($pendingCount)->create();
            $progressBar->advance($pendingCount);
        }

        // Create cancelled tasks
        if ($cancelledCount > 0) {
            Task::factory($cancelledCount)->cancelled()->create();
            $progressBar->advance($cancelledCount);
        }

        // Create overdue tasks
        if ($overdueCount > 0) {
            Task::factory($overdueCount)->overdue()->create();
            $progressBar->advance($overdueCount);
        }

        // Create tasks due soon
        if ($dueSoonCount > 0) {
            Task::factory($dueSoonCount)->dueSoon()->create();
            $progressBar->advance($dueSoonCount);
        }

        $progressBar->finish();
        $this->newLine(2);

        // Show statistics
        $this->info('✅ Tasks generated successfully!');
        $this->info('📊 Distribution:');
        $this->info("   - {$completedCount} completed tasks");
        $this->info("   - {$inProgressCount} in-progress tasks");
        $this->info("   - {$pendingCount} pending tasks");
        $this->info("   - {$cancelledCount} cancelled tasks");
        $this->info("   - {$overdueCount} overdue tasks");
        $this->info("   - {$dueSoonCount} tasks due soon");

        $this->newLine();
        $this->info('Total tasks in database: ' . Task::count());

        return 0;
    }
}
