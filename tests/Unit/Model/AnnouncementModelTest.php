<?php

namespace Tests\Unit\Model;

use App\Models\Announcement;
use App\Models\Polling;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcement_has_creator()
    {
        $announcement = Announcement::factory()->create();
        $this->assertInstanceOf(User::class, $announcement->creator);
    }

    public function test_announcement_has_polling()
    {
        $announcement = Announcement::factory()->create();
        Polling::factory()->create(['announcement_id' => $announcement->id]);
        $this->assertInstanceOf(Polling::class, $announcement->polling);
    }

    public function test_fillable_fields()
    {
        $announcement = new Announcement();
        $this->assertEquals([
            'created_by',
            'title',
            'announcement_type',
            'label',
            'content',
            'attachment_file',
            'external_link',
        ], $announcement->getFillable());
    }
}
