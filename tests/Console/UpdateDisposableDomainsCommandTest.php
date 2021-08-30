<?php

namespace Tagmood\LaravelDisposablePhone\Tests\Console;

use InvalidArgumentException;
use Tagmood\LaravelDisposablePhone\Contracts\Fetcher;
use Tagmood\LaravelDisposablePhone\Tests\TestCase;

class UpdateDisposableNumbersCommandTest extends TestCase
{
    /** @test */
    public function it_creates_the_file()
    {
        $this->assertFileDoesNotExist($this->storagePath);

        $this->artisan('disposablephone:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $numbers = $this->disposable()->getNumbers();

        $this->assertIsArray($numbers);
        $this->assertContains('393399957039', $numbers);
    }

    /** @test */
    public function it_overwrites_the_file()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->artisan('disposablephone:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $numbers = $this->disposable()->getNumbers();

        $this->assertIsArray($numbers);
        $this->assertContains('393399957039', $numbers);
        $this->assertNotContains('foo', $numbers);
    }

    /** @test */
    public function it_doesnt_overwrite_on_fetch_failure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source URL is null');

        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-phone.source'] = null;

        $this->artisan('disposablephone:update')
            ->assertExitCode(1);

        $this->assertFileExists($this->storagePath);

        $numbers = $this->disposable()->getNumbers();
        $this->assertEquals(['foo'], $numbers);
    }

    /** @test */
    public function custom_fetchers_need_fetcher_contract()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-phone.source'] = 'bar';
        $this->app['config']['disposable-phone.fetcher'] = InvalidFetcher::class;

        $this->artisan('disposablephone:update')
            ->assertExitCode(1);

        $this->assertFileExists($this->storagePath);

        $numbers = $this->disposable()->getNumbers();
        $this->assertNotEquals(['foo'], $numbers);
    }

    /** @test */
    public function it_can_use_a_custom_fetcher()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-phone.source'] = 'bar';
        $this->app['config']['disposable-phone.fetcher'] = CustomFetcher::class;

        $this->artisan('disposablephone:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $numbers = $this->disposable()->getNumbers();
        $this->assertEquals(['bar'], $numbers);
    }
}

class CustomFetcher implements Fetcher
{
    public function handle($url): array
    {
        return [$url];
    }
}

class InvalidFetcher
{
    public function handle($url)
    {
        return $url;
    }
}
