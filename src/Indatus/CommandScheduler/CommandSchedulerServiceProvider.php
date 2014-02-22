<?php

namespace Indatus\CommandScheduler;

use Illuminate\Support\ServiceProvider;
use Indatus\CommandScheduler\Commands\Make;
use Indatus\CommandScheduler\Commands\ScheduleSummary;
use Indatus\CommandScheduler\Drivers\Cron\Scheduler;
use Indatus\CommandScheduler\Services\ScheduleService;
use App;
use Config;

class CommandSchedulerServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('indatus/command-scheduler');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        App::bind('Indatus\CommandScheduler\Services\SchedulerService', function ($app) {
                return new ScheduleService(new Table());
            });

        //load the scheduler of the appropriate driver
        App::bind('Indatus\CommandScheduler\Schedulable', function () {
                /*$schedulerClass = Config::get('indatus/command-scheduler::driver');
                var_dump($schedulerClass);
                exit;
                return new $schedulerClass();*/
                return new Scheduler();
            });

        //App::make('Indatus\CommandScheduler\Schedulable');

        $this->registerCommands();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
        return [
            'command.scheduled.summary',
            'command.scheduled.make'
        ];
	}

    /**
     * Register artisan commands
     */
    private function registerCommands()
    {
        $this->app['command.scheduled.summary'] = $this->app->share(function($app)
            {
                return new ScheduleSummary(App::make('Indatus\CommandScheduler\Services\SchedulerService'));
            });
        $this->commands('command.scheduled.summary');

        $this->app['command.scheduled.make'] = $this->app->share(function($app)
            {
                return App::make('Indatus\CommandScheduler\Commands\Make');
            });
        $this->commands('command.scheduled.make');
    }

}