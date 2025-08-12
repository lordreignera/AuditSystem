<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Only refresh database if the test uses it
        if (in_array(RefreshDatabase::class, class_uses_recursive($this))) {
            $this->refreshDatabase();
        }
    }
}
