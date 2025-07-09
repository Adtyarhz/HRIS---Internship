<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\RecruitmentProgress;
use App\Models\UserTest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTestTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_test_has_fillable_attributes()
    {
        $this->assertEquals([
            'recruitment_progress_id', 
            'test_name', 
            'score', 
            'status', 
            'notes', 
            'test_date',
        ], (new UserTest())->getFillable());
    }
    public function test_user_test_belongs_to_recruitment_progress()
    {
        $progress = RecruitmentProgress::factory()->create();
        $test = UserTest::factory()->create(['recruitment_progress_id' => $progress->id]);
        $this->assertInstanceOf(RecruitmentProgress::class, $test->recruitmentProgress);
    }
}
