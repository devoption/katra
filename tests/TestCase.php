<?php

namespace Katra\Katra\Tests;

use Katra\Katra\KatraServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
  
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            KatraServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}