<?php

namespace Tagmood\LaravelDisposablePhone\Tests\Validation;

use Tagmood\LaravelDisposablePhone\Tests\TestCase;
use Tagmood\LaravelDisposablePhone\Validation\Indisposable;

class IndisposableTest extends TestCase
{
    /** @test */
    public function it_should_pass_for_indisposable_phones()
    {
        $validator = new Indisposable;
        $phone = '393491234567';

        $this->assertTrue($validator->validate(null, $phone, null, null));
    }

    /** @test */
    public function it_should_fail_for_disposable_phones()
    {
        $validator = new Indisposable;
        $phone = '393399957039';

        $this->assertFalse($validator->validate(null, $phone, null, null));
    }

    /** @test */
    public function it_is_usable_through_the_validator()
    {
        $passingValidation = $this->app['validator']->make(['phone' => '393491234567'], ['phone' => 'indisposablephone']);
        $failingValidation = $this->app['validator']->make(['phone' => '393399957039'], ['phone' => 'indisposablephone']);

        $this->assertTrue($passingValidation->passes());
        $this->assertTrue($failingValidation->fails());
    }
}
