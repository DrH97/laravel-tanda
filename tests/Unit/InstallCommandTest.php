<?php

namespace DrH\Tanda\Tests\Unit;

use DrH\Tanda\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallCommandTest extends TestCase
{

    /** @test */
    function the_install_command_copies_the_configuration()
    {
        // Make sure we're starting from a clean state
        if (File::exists(config_path('tanda.php'))) {
            File::delete(config_path('tanda.php'));
        }

        $this->assertFalse(File::exists(config_path('tanda.php')));

        Artisan::call('tanda:install');

        $this->assertTrue(File::exists(config_path('tanda.php')));

        File::delete(config_path('tanda.php'));
    }

    /** @test */
    public function when_a_config_file_is_present_users_can_choose_to_not_overwrite_it()
    {
        // Given we already have an existing config file
        File::put(config_path('tanda.php'), 'test contents');
        $this->assertTrue(File::exists(config_path('tanda.php')));

        // When we run the installation command
        $command = $this->artisan('tanda:install');

        // We expect a warning that our configuration file exists
        $command->expectsConfirmation('Config file already exists. Do you want to overwrite it?');

        // When answered with "no", We should see a message that our file was not overwritten
        $command->expectsOutput('Existing configuration was not overwritten');

        $command->execute();

        // Assert that the original contents of the config file remain
        $this->assertEquals('test contents', file_get_contents(config_path('tanda.php')));

        // Clean up
        File::delete(config_path('tanda.php'));
    }

    /** @test */
    public function when_a_config_file_is_present_users_can_choose_to_overwrite_it()
    {
        // Given we already have an existing config file
        File::put(config_path('tanda.php'), 'test contents');
        $this->assertTrue(File::exists(config_path('tanda.php')));

        // When we run the installation command
        $command = $this->artisan('tanda:install');

        // We expect a warning that our configuration file exists
        $command->expectsConfirmation('Config file already exists. Do you want to overwrite it?', 'yes');

        $command->expectsOutput('Overwriting configuration file...');

        // When answered with "yes", execute the command to force override
        $command->execute();

        // Assert that the original contents are overwritten
        $this->assertEquals(file_get_contents(__DIR__ . '/../../config/tanda.php'), file_get_contents(config_path('tanda.php')));

        // Clean up
        File::delete(config_path('tanda.php'));
    }
}
