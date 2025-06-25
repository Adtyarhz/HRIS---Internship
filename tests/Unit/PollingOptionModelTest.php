<?php

namespace Tests\Unit;

use App\Models\PollingOption;
use App\Models\Polling;
use App\Models\PollingVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollingOptionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_polling_option_belongs_to_polling()
    {
        $option = PollingOption::factory()->create();
        $this->assertInstanceOf(Polling::class, $option->polling);
    }

    public function test_polling_option_has_many_votes()
    {
        $option = PollingOption::factory()->create();
        PollingVote::factory()->count(3)->create(['polling_option_id' => $option->id]);
        $this->assertCount(3, $option->votes);
    }

    public function test_fillable_fields()
    {
        $option = new PollingOption();
        $this->assertEquals(['polling_id', 'option_text'], $option->getFillable());
    }
}
