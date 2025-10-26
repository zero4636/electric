<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'electric:install-demo {--force : Force run in production} {--yes : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare environment, run migrations and seed demo data (non-invasive)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run demo installer in production. Use --force to override.');
            return self::FAILURE;
        }

        if (! $this->option('yes')) {
            if (! $this->confirm('This will run migrations and seed demo data. Continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        // copy .env.example to .env if missing
        if (! file_exists(base_path('.env')) && file_exists(base_path('.env.example'))) {
            copy(base_path('.env.example'), base_path('.env'));
            $this->info('Copied .env.example to .env');
        }

        // generate app key if missing
        if (empty(config('app.key'))) {
            $this->callSilent('key:generate');
            $this->info('Generated APP_KEY');
        }

        // create sqlite file if not exists
        if (! file_exists(database_path('database.sqlite'))) {
            if (! is_dir(database_path())) {
                mkdir(database_path(), 0755, true);
            }
            touch(database_path('database.sqlite'));
            $this->info('Created database/database.sqlite');
        }

        // run migrations and seeders
        $this->info('Running migrations...');
        $this->call('migrate', ['--force' => true]);

        $this->info('Seeding database...');
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder', '--force' => true]);

        $this->info('Demo installation complete. You can run: php artisan serve');

        return self::SUCCESS;
    }
}
