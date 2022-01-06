<?php

namespace DrH\Tanda\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tanda:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all Tanda resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Installing Tanda Scaffolding...');

        if (File::exists(config_path('tanda.php'))) {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting configuration file...');
                $this->callSilent('vendor:publish', ['--tag' => 'tanda-config', '--force' => true]);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        } else {
            $this->info('Publishing Tanda Configuration...');
            $this->callSilent('vendor:publish', ['--tag' => 'tanda-config']);
            $this->comment('Published configuration.');
        }

        $this->comment('Tanda scaffolding installed successfully!');
    }

    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm('Config file already exists. Do you want to overwrite it?', false);
    }
}
