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

class EmployeeTest extends TestCase
{
    /**
     * @test
     * Memastikan model Employee dapat dibuat menggunakan factory.
     */
    #[Test]
    public function it_can_be_instantiated(): void
    {
        // PERBAIKAN: Berikan ID palsu untuk relasi agar factory tidak mencoba
        // membuat model terkait di database.
        $employee = Employee::factory()->make([
            'user_id' => 1,
            'division_id' => 1,
            'position_id' => 1,
        ]);

        $this->assertInstanceOf(Employee::class, $employee);
    }

    /**
     * @test
     * Memastikan semua atribut tanggal di-cast menjadi objek Carbon.
     */
    #[Test]
    public function it_casts_date_attributes_correctly(): void
    {
        // PERBAIKAN: Berikan juga ID palsu untuk relasi di sini.
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

    /**
     * @test
     * Menguji logika accessor getAgeAttribute dengan benar.
     */
    #[Test]
    public function it_calculates_age_correctly(): void
    {
        Carbon::setTestNow('2025-06-24');

        // PERBAIKAN: Berikan juga ID palsu untuk relasi di sini.
        $employee = Employee::factory()->make([
            'user_id' => 1,
            'division_id' => 1,
            'position_id' => 1,
            'birth_date' => '2000-01-15'
        ]);

        $this->assertEquals(25, $employee->age);
    }

    /**
     * @test
     * Memastikan semua relasi BelongsTo dideklarasikan dengan benar.
     * Tes ini tidak perlu diubah karena menggunakan `new Employee()`.
     */
    #[Test]
    public function it_has_correct_belongs_to_relationships(): void
    {
        $employee = new Employee();

        // Tes relasi user()
        $this->assertInstanceOf(BelongsTo::class, $employee->user());
        $this->assertInstanceOf(User::class, $employee->user()->getRelated());
        $this->assertEquals('user_id', $employee->user()->getForeignKeyName());

        // Tes relasi division()
        $this->assertInstanceOf(BelongsTo::class, $employee->division());
        $this->assertInstanceOf(Division::class, $employee->division()->getRelated());
        $this->assertEquals('division_id', $employee->division()->getForeignKeyName());

        // Tes relasi position()
        $this->assertInstanceOf(BelongsTo::class, $employee->position());
        $this->assertInstanceOf(Position::class, $employee->position()->getRelated());
        $this->assertEquals('position_id', $employee->position()->getForeignKeyName());
    }
    
    /**
     * @test
     * Memastikan semua relasi HasMany dideklarasikan dengan benar.
     * Tes ini tidak perlu diubah karena menggunakan `new Employee()`.
     */
    #[Test]
    public function it_has_correct_has_many_relationships(): void
    {
        $employee = new Employee();

        // PERBAIKAN: Mengganti nama relasi yang salah dan menghapus yang tidak ada.
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

    /**
     * @test
     * Memastikan relasi HasOne dideklarasikan dengan benar.
     * Tes ini tidak perlu diubah karena menggunakan `new Employee()`.
     */
    #[Test]
    public function it_has_correct_has_one_relationship(): void
    {
        $employee = new Employee();

        // Tes relasi healthRecord()
        $relation = $employee->healthRecord();
        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(HealthRecord::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }
}
