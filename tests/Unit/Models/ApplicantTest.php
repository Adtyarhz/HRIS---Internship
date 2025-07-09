<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Applicant;
use App\Models\RecruitmentProgress;
use App\Models\InterviewSchedule;
use App\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicantTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_has_fillable_attributes()
    {
        $this->assertEquals([
            'full_name',
            'email',
            'phone',
            'address',
            'resume_file',
            'applied_position',
            'last_education',
            'origin',
            'gpa_score',
            'division_id',
        ], (new Applicant())->getFillable());
    }

    public function test_applicant_belongs_to_division()
    {
        $division = Division::factory()->create();
        $applicant = Applicant::factory()->create(['division_id' => $division->id]);
        $this->assertInstanceOf(Division::class, $applicant->division);
    }

    public function test_applicant_has_one_recruitment_progress()
    {
        $applicant = Applicant::factory()->create();
        $progress = RecruitmentProgress::factory()->create(['applicant_id' => $applicant->id]);
        $this->assertInstanceOf(RecruitmentProgress::class, $applicant->recruitmentProgresses);
    }

    public function test_applicant_has_many_interview_schedules()
    {
        $applicant = Applicant::factory()->create();
        InterviewSchedule::factory()->count(2)->create(['applicant_id' => $applicant->id]);
        $this->assertCount(2, $applicant->interviewSchedules);
    }
}
