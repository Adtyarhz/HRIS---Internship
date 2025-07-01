<?php

namespace Tests\Unit\Model;

use App\Models\PollingVote;
use App\Models\PollingOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollingVoteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_belongs_to_option()
    {
        $vote = PollingVote::factory()->create();
        $this->assertInstanceOf(PollingOption::class, $vote->pollingOption);
    }

    public function test_vote_belongs_to_user()
    {
        $vote = PollingVote::factory()->create();
        $this->assertInstanceOf(User::class, $vote->creator);
    }

    public function test_fillable_fields()
    {
        $vote = new PollingVote();
        $this->assertEquals(['polling_option_id', 'created_by'], $vote->getFillable());
    }
}
