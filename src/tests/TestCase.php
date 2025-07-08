<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void{
        parent::setUp();

        // テスト実行時に毎回マイグレーションを実行
        $this->artisan('migrate:fresh');

        
    }
}
