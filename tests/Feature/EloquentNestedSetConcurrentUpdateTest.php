<?php

namespace MediciVN\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MediciVN\Core\Tests\Models\Category;
use MediciVN\Core\Tests\TestCase;

class EloquentNestedSetConcurrentUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_calculate_rightly_lft_rgt_when_concurrent_update()
    {
        //
    }
}