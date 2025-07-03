<?php

namespace Tests\Unit\Models;

use App\Models\Certification;
use App\Models\Division;
use App\Models\EducationHistory;
use App\Models\Employee;
use App\Models\FamilyDependent;
use App\Models\HealthRecord;
use App\Models\Insurance;
use App\Models\Position;
use App\Models\TrainingHistory;
use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class EmployeeTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated(): void
    {
        $employee = Employee::factory()->make([
            'user_id' => 1,
            'division_id' => 1,
            'position_id' => 1,
        ]);

        $this->assertInstanceOf(Employee::class, $employee);
    }

    #[Test]
    public function it_casts_date_attributes_correctly(): void
    {
        $employee = Employee::factory()->make([
            'user_id' => 1,
            'division_id' => 1,
            'position_id' => 1,
            'birth_date' => '2000-01-01',
            'hire_date' => '2022-01-01',
            'separation_date' => '2025-01-01',
        ]);

        $this->assertInstanceOf(Carbon::class, $employee->birth_date);
        $this->assertInstanceOf(Carbon::class, $employee->hire_date);
        $this->assertInstanceOf(Carbon::class, $employee->separation_date);
    }

    #[Test]
    public function it_returns_correct_cv_file_url(): void
    {
        $employeeWithCv = Employee::factory()->make([
            'user_id' => 1,
            'division_id' => 1,
            'position_id' => 1,
            'cv_file' => 'my-cv.pdf'
        ]);
        $this->assertEquals(asset('storage/cv/my-cv.pdf'), $employeeWithCv->cv_file_url);

        $employeeWithoutCv = Employee::factory()->make([
            'user_id' => 2,
            'division_id' => 1,
            'position_id' => 1,
            'cv_file' => null
        ]);
        $this->assertNull($employeeWithoutCv->cv_file_url);
    }

    #[Test]
    public function it_returns_correct_photo_url(): void
    {
        $employeeWithPhoto = Employee::factory()->make([
            'user_id' => 1,
            'division_id' => 1,
            'position_id' => 1,
            'photo' => 'profile.jpg'
        ]);
        $this->assertEquals(asset('storage/photo/profile.jpg'), $employeeWithPhoto->photo_url);

        $employeeWithoutPhoto = Employee::factory()->make([
            'user_id' => 2,
            'division_id' => 1,
            'position_id' => 1,
            'photo' => null
        ]);
        $this->assertNull($employeeWithoutPhoto->photo_url);
    }

    #[Test]
    public function it_has_correct_belongs_to_relationships(): void
    {
        $employee = new Employee();

        $this->assertInstanceOf(BelongsTo::class, $employee->user());
        $this->assertInstanceOf(User::class, $employee->user()->getRelated());
        $this->assertEquals('user_id', $employee->user()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $employee->division());
        $this->assertInstanceOf(Division::class, $employee->division()->getRelated());
        $this->assertEquals('division_id', $employee->division()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $employee->position());
        $this->assertInstanceOf(Position::class, $employee->position()->getRelated());
        $this->assertEquals('position_id', $employee->position()->getForeignKeyName());
    }

    #[Test]
    public function it_has_correct_has_many_relationships(): void
    {
        $employee = new Employee();

        $relations = [
            'educationHistory' => EducationHistory::class,
            'workExperience' => WorkExperience::class,
            'certification' => Certification::class,
            'trainingHistory' => TrainingHistory::class,
            'insurance' => Insurance::class,
            'familyDependent' => FamilyDependent::class,
        ];

        foreach ($relations as $method => $relatedClass) {
            $relation = $employee->$method();
            $this->assertInstanceOf(HasMany::class, $relation, "Gagal pada relasi: $method");
            $this->assertInstanceOf($relatedClass, $relation->getRelated(), "Gagal pada relasi: $method");
            $this->assertEquals('employee_id', $relation->getForeignKeyName(), "Gagal pada relasi: $method");
        }
    }

    #[Test]
    public function it_has_correct_has_one_relationship(): void
    {
        $employee = new Employee();

        $relation = $employee->healthRecord();
        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(HealthRecord::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }
}
