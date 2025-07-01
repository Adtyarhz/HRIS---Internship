<?php

namespace Tests\Unit;

use App\Models\Polling;
use App\Models\Announcement;
use App\Models\User;
use App\Models\PollingOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_polling_belongs_to_announcement()
    {
        $polling = Polling::factory()->create();
        $this->assertInstanceOf(Announcement::class, $polling->announcement);
    }

    public function test_polling_has_many_options()
    {
        $polling = Polling::factory()->create();
        PollingOption::factory()->count(2)->create(['polling_id' => $polling->id]);
        $this->assertCount(2, $polling->options);
    }

    public function test_fillable_fields()
    {
        $polling = new Polling();
        $this->assertEquals([
            'announcement_id',
            'deadline',
            'created_by',
        ], $polling->getFillable());
    }
}
