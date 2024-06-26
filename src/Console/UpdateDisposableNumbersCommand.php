<?php

namespace Tagmood\LaravelDisposablePhone\Console;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Console\Command;
use Tagmood\LaravelDisposablePhone\Contracts\Fetcher;
use Tagmood\LaravelDisposablePhone\DisposableNumbers;

class UpdateDisposableNumbersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disposablephone:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates to the latest disposable phone numbers list';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @param  \Tagmood\LaravelDisposablePhone\DisposableNumbers  $disposable
     * @return  void
     */
    public function handle(Config $config, DisposableNumbers $disposable)
    {
        $this->line('Fetching from source...');

        $fetcher = $this->laravel->make(
            $fetcherClass = $config->get('disposable-phone.fetcher')
        );

        if (! $fetcher instanceof Fetcher) {
            $this->error($fetcherClass . ' should implement ' . Fetcher::class);
            return 1;
        }

        $data = $this->laravel->call([$fetcher, 'handle'], [
            'url' => $config->get('disposable-phone.source'),
        ]);

        $this->line('Saving response to storage...');

        if ($disposable->saveToStorage($data)) {
            $this->info('Disposable numbers list updated successfully.');
            $disposable->bootstrap();
            return 0;
        } else {
            $this->error('Could not write to storage ('.$disposable->getStoragePath().')!');
            return 1;
        }
    }
}
