<?php

namespace Tagmood\LaravelDisposablePhone\Tests;

use Tagmood\LaravelDisposablePhone\DisposableNumbers;

class DisposableNumbersTest extends TestCase
{
    /** @test */
    public function it_can_be_resolved_using_alias()
    {
        $this->assertEquals(DisposableNumbers::class, get_class($this->app->make('disposable_phone.numbers')));
    }

    /** @test */
    public function it_can_be_resolved_using_class()
    {
        $this->assertEquals(DisposableNumbers::class, get_class($this->app->make(DisposableNumbers::class)));
    }

    /** @test */
    public function it_can_get_storage_path()
    {
        $this->assertEquals(
            $this->app['config']['disposable-phone.storage'],
            $this->disposable()->getStoragePath()
        );
    }

    /** @test */
    public function it_can_set_storage_path()
    {
        $this->disposable()->setStoragePath('foo');

        $this->assertEquals('foo', $this->disposable()->getStoragePath());
    }

    /** @test */
    public function it_can_get_cache_key()
    {
        $this->assertEquals(
            $this->app['config']['disposable-phone.cache.key'],
            $this->disposable()->getCacheKey()
        );
    }

    /** @test */
    public function it_can_set_cache_key()
    {
        $this->disposable()->setCacheKey('foo');

        $this->assertEquals('foo', $this->disposable()->getCacheKey());
    }

    /** @test */
    public function it_takes_cached_numbers_if_available()
    {
        $this->app['cache.store'][$this->disposable()->getCacheKey()] = ['foo'];

        $this->disposable()->bootstrap();

        $numbers = $this->disposable()->getNumbers();

        $this->assertEquals(['foo'], $numbers);
    }
    
    /** @test */
    public function it_flushes_invalid_cache_values()
    {
        $this->app['cache.store'][$this->disposable()->getCacheKey()] = 'foo';

        $this->disposable()->bootstrap();

        $this->assertNotEquals('foo', $this->app['cache.store'][$this->disposable()->getCacheKey()]);
    }

    /** @test */
    public function it_skips_cache_when_configured()
    {
        $this->app['config']['disposable-phone.cache.enabled'] = false;

        $numbers = $this->disposable()->getNumbers();

        $this->assertIsArray($numbers);
        $this->assertNull($this->app['cache.store'][$this->disposable()->getCacheKey()]);
        $this->assertArrayHasKey('393399957039',  $numbers);
    }

    /** @test */
    public function it_takes_storage_numbers_when_cache_is_not_available()
    {
        $this->app['config']['disposable-phone.cache.enabled'] = false;

        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->disposable()->bootstrap();

        $numbers = $this->disposable()->getNumbers();

        $this->assertEquals(['foo'], $numbers);
    }

    /** @test */
    public function it_takes_package_numbers_when_storage_is_not_available()
    {
        $this->app['config']['disposable-phone.cache.enabled'] = false;

        $numbers = $this->disposable()->getNumbers();

        $this->assertIsArray($numbers);
        $this->assertArrayHasKey('393399957039', $numbers);
    }

    /** @test */
    public function it_can_flush_storage()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->disposable()->flushStorage();

        $this->assertFileNotExists($this->storagePath);
    }

    /** @test */
    public function it_doesnt_throw_exceptions_for_flush_storage_when_file_doesnt_exist()
    {
        $this->disposable()->flushStorage();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_flush_cache()
    {
        $this->app['cache.store'][$this->disposable()->getCacheKey()] = 'foo';

        $this->assertEquals('foo', $this->app['cache']->get($this->disposable()->getCacheKey()));

        $this->disposable()->flushCache();

        $this->assertNull($this->app['cache']->get($this->disposable()->getCacheKey()));
    }

    /** @test */
    public function it_can_verify_disposability()
    {
        $this->assertTrue($this->disposable()->isDisposable('393399957039'));
        $this->assertFalse($this->disposable()->isNotDisposable('393399957039'));
        $this->assertFalse($this->disposable()->isIndisposable('393399957039'));

        $this->assertFalse($this->disposable()->isDisposable('393491234567'));
        $this->assertTrue($this->disposable()->isNotDisposable('393491234567'));
        $this->assertTrue($this->disposable()->isIndisposable('393491234567'));
    }

    /** @test */
    public function it_checks_the_full_phone_number()
    {
        $this->assertTrue($this->disposable()->isDisposable('393399957039'));
        $this->assertTrue($this->disposable()->isDisposable('393399957039'));
        $this->assertTrue($this->disposable()->isNotDisposable('393491234567'));
    }
}
