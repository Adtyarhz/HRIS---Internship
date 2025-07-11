<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Applicant;
use App\Models\InterviewSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InterviewScheduleTest extends TestCase
{
    use RefreshDatabase;
    public function test_interview_schedule_has_fillable_attributes()
    {
        $this->assertEquals([
            'applicant_id',
            'interview_type',
            'interview_date',
            'interviewer',
            'location',
            'result',
        ], (new InterviewSchedule())->getFillable());
    }
    public function test_interview_schedule_belongs_to_applicant()
    {
        $applicant = Applicant::factory()->create();
        $interview = InterviewSchedule::factory()->create(['applicant_id' => $applicant->id]);
        $this->assertInstanceOf(Applicant::class, $interview->applicant);
    }
}
