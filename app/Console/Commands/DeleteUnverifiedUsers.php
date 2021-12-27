<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all users who have not confirmed an account within a week.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rowsCount = User::where('updated_at', '<', now()->subWeek())
            ->unverified()
            ->delete();

        return $this->info($rowsCount . ' users were deleted.');
    }
}
