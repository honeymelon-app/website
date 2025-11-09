<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_objects_page_loads(): void
    {
        $this->markTestSkipped('Requires R2/S3 configuration for testing');
    }

    public function test_bulk_delete_requires_paths(): void
    {
        $this->markTestSkipped('Requires R2/S3 configuration for testing');
    }

    public function test_bulk_delete_handles_empty_paths(): void
    {
        $this->markTestSkipped('Requires R2/S3 configuration for testing');
    }
}
