<?php

namespace Tagmood\LaravelDisposablePhone\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected $storagePath = __DIR__.'/number-list.json';

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('disposable-phone.storage', $this->storagePath);
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->disposable()->flushStorage();
        $this->disposable()->flushCache();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->disposable()->flushStorage();
        $this->disposable()->flushCache();

        parent::tearDown();
    }

    /**
     * Package Service Providers
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Tagmood\LaravelDisposablePhone\DisposablePhoneServiceProvider'];
    }

    /**
     * Package Aliases
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return ['Indisposable' => 'Tagmood\LaravelDisposablePhone\Facades\DisposableNumbers'];
    }

    /**
     * @return \Tagmood\LaravelDisposablePhone\DisposableNumbers
     */
    protected function disposable()
    {
        return $this->app['disposable_phone.numbers'];
    }
}