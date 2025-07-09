<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Applicant;
use App\Models\RecruitmentProgress;
use App\Models\UserTest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecruitmentProgressTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_recruitment_progress_has_fillable_attributes()
    {
        $this->assertEquals([
            'applicant_id',
            'stage',
            'offering_status',
            'status_date',
            'notes',
            'rejected_reason',
            'contract_type',
            'test_result',
            'result_file',
            'score',
            'slik_recap',
        ], (new RecruitmentProgress())->getFillable());
    }
    public function test_recruitment_progress_belongs_to_applicant()
    {
        $applicant = Applicant::factory()->create();
        $progress = RecruitmentProgress::factory()->create(['applicant_id' => $applicant->id]);
        $this->assertInstanceOf(Applicant::class, $progress->applicant);
    }

    public function test_recruitment_progress_has_many_user_tests()
    {
        $progress = RecruitmentProgress::factory()->create();
        UserTest::factory()->count(3)->create(['recruitment_progress_id' => $progress->id]);
        $this->assertCount(3, $progress->userTests);
    }
}
