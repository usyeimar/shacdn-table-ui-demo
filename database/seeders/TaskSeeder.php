<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Clear existing tasks
        Task::truncate();

        // Create 100 tasks with realistic distribution
        $this->command->info('Creating 100 tasks...');

        // Create 30 completed tasks
        Task::factory(30)
            ->completed()
            ->create();

        // Create 25 in-progress tasks
        Task::factory(25)
            ->inProgress()
            ->create();

        // Create 35 pending tasks
        Task::factory(35)
            ->create();

        // Create 5 cancelled tasks
        Task::factory(5)
            ->cancelled()
            ->create();

        // Create 5 high priority overdue tasks
        Task::factory(5)
            ->highPriority()
            ->overdue()
            ->create();

        // Create 5 tasks due soon
        Task::factory(5)
            ->dueSoon()
            ->create();

        // Create some deleted tasks for testing
        $deletedTasks = [
            [
                'title' => 'Tarea eliminada 1',
                'description' => 'Esta tarea fue eliminada para pruebas.',
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => now()->addDays(5),
                'created_by' => $users->random()->id,
                'updated_by' => $users->random()->id,
            ],
            [
                'title' => 'Tarea eliminada 2',
                'description' => 'Otra tarea eliminada para pruebas.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(10),
                'created_by' => $users->random()->id,
                'updated_by' => $users->random()->id,
            ],
            [
                'title' => 'Tarea eliminada 3',
                'description' => 'Tarea de alta prioridad eliminada.',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->subDays(5),
                'created_by' => $users->random()->id,
                'updated_by' => $users->random()->id,
            ],
        ];

        foreach ($deletedTasks as $taskData) {
            $task = Task::create($taskData);
            $task->delete(); // Soft delete
        }

        $this->command->info('✅ 100 tasks created successfully!');
        $this->command->info('📊 Distribution:');
        $this->command->info('   - 30 completed tasks');
        $this->command->info('   - 25 in-progress tasks');
        $this->command->info('   - 35 pending tasks');
        $this->command->info('   - 5 cancelled tasks');
        $this->command->info('   - 5 high priority overdue tasks');
        $this->command->info('   - 5 tasks due soon');
        $this->command->info('   - 3 deleted tasks (for testing)');
    }
}
