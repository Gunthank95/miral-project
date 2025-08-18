<?php
// GANTI: app/Console/Commands/SyncUsersToPersonnel.php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncUsersToPersonnel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:users-personnel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing users to the personnel table if they are missing.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting user to personnel synchronization...');

        // Ambil semua user yang TIDAK memiliki relasi 'personnel'
        $usersToSync = User::whereDoesntHave('personnel')->get();

        if ($usersToSync->isEmpty()) {
            $this->info('All users are already synchronized. No action needed.');
            return 0;
        }

        $this->info("Found {$usersToSync->count()} user(s) to sync.");
        $bar = $this->output->createProgressBar($usersToSync->count());
        $bar->start();

        foreach ($usersToSync as $user) {
            $user->personnel()->create([
                'company_id' => $user->company_id,
                'name' => $user->name,
                'position' => $user->position ?? 'N/A', // Beri nilai default jika 'position' null
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nSynchronization complete. All users now have a corresponding personnel record.");
        return 0;
    }
}