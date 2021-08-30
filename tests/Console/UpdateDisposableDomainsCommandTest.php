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
        $this->assertFileNotExists($this->storagePath);

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
    }

    /** @test */
    public function it_overwrites_the_file()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
        $this->assertNotContains('foo', $domains);
    }

    /** @test */
    public function it_doesnt_overwrite_on_fetch_failure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source URL is null');

        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-email.source'] = null;

        $this->artisan('disposable:update')
            ->assertExitCode(1);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertEquals(['foo'], $domains);
    }

    /** @test */
    public function custom_fetchers_need_fetcher_contract()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-email.source'] = 'bar';
        $this->app['config']['disposable-email.fetcher'] = InvalidFetcher::class;

        $this->artisan('disposable:update')
            ->assertExitCode(1);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertNotEquals(['foo'], $domains);
    }

    /** @test */
    public function it_can_use_a_custom_fetcher()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-email.source'] = 'bar';
        $this->app['config']['disposable-email.fetcher'] = CustomFetcher::class;

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertEquals(['bar'], $domains);
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
