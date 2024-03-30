<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected bool $is_passport_install = false;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->is_passport_install === false) {
            Artisan::call('passport:install --env=testing');
            $this->is_passport_install = true;
        }
    }
}
