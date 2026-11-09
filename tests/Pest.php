<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

// Bootstrap Laravel for both Feature and Unit suites
uses(Tests\TestCase::class, WithFaker::class, RefreshDatabase::class)->in('Feature', 'Unit');
