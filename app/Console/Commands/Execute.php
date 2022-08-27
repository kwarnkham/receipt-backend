<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;

class Execute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exec';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo "Executing" . PHP_EOL;
        $users = User::whereDoesntHave('subscriptions')->get();
        foreach ($users as $user) {
            Subscription::create([
                'day' => 30,
                'duration' => 30,
                'user_id' => $user->id,
                'price' => 7000
            ]);
        }
        echo "Finished" . PHP_EOL;
    }
}
