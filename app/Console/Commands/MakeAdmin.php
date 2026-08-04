<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {email} {--revoke : Remove admin access instead of granting it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant (or revoke with --revoke) admin access for the user with the given email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $user->is_admin = !$this->option('revoke');
        $user->save();

        $this->info(
            $user->is_admin
                ? "{$user->email} is now an admin."
                : "{$user->email} is no longer an admin."
        );

        return self::SUCCESS;
    }
}
