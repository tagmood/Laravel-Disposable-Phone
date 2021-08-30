<?php

namespace Tagmood\LaravelDisposablePhone\Contracts;

interface Fetcher
{
    public function handle($url): array;
}