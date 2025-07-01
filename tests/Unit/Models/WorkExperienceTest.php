<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\WorkExperience;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkExperienceTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // Arrange & Act
        // Buat objek di memori menggunakan factory
        $workExperience = WorkExperience::factory()->make([
            'employee_id' => 1,
            'company_name' => 'PT Cipta Solusi Digital',
            'position_title' => 'Senior Backend Engineer',
        ]);

        // Assert
        $this->assertInstanceOf(WorkExperience::class, $workExperience);
        $this->assertEquals('PT Cipta Solusi Digital', $workExperience->company_name);
        $this->assertEquals('Senior Backend Engineer', $workExperience->position_title);
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        // Arrange
        $workExperience = WorkExperience::factory()->make([
            'employee_id' => 1,
            'start_date' => '2022-01-10',
            'end_date' => '2024-03-20',
            'last_salary' => '7500000.50'
        ]);

        // Assert
        // Pastikan atribut tanggal di-cast menjadi objek Carbon
        $this->assertInstanceOf(Carbon::class, $workExperience->start_date);
        $this->assertInstanceOf(Carbon::class, $workExperience->end_date);

        // Pastikan atribut desimal di-cast menjadi float/numeric
        $this->assertEquals(7500000.50, $workExperience->last_salary);
        $this->assertIsNumeric($workExperience->last_salary);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // Arrange
        $workExperience = new WorkExperience();

        // Act
        $relation = $workExperience->employee();

        // Assert
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }
}