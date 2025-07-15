<?php

namespace Tests\Unit\Models;

use App\Models\CareerProjection;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CareerProjectionTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // Membuat instance dengan atribut spesifik
        $careerProjection = CareerProjection::factory()->make([
            'employee_id' => 1,
            'projected_position_id' => 2,
            'created_by' => 3,
            'projection_date' => '2025-12-01',
            'notes' => 'Projected for promotion to senior role',
        ]);

        // Assert
        $this->assertInstanceOf(CareerProjection::class, $careerProjection);
        $this->assertEquals(1, $careerProjection->employee_id);
        $this->assertEquals(2, $careerProjection->projected_position_id);
        $this->assertEquals(3, $careerProjection->created_by);
        $this->assertEquals('2025-12-01', $careerProjection->projection_date); // Periksa sebagai string
        $this->assertEquals('Projected for promotion to senior role', $careerProjection->notes);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // Membuat instance kosong untuk menguji relasi
        $careerProjection = new CareerProjection();
        $relation = $careerProjection->employee();

        // Assert
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_belongs_to_a_projected_position(): void
    {
        // Membuat instance kosong untuk menguji relasi
        $careerProjection = new CareerProjection();
        $relation = $careerProjection->projectedPosition();

        // Assert
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Position::class, $relation->getRelated());
        $this->assertEquals('projected_position_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_belongs_to_a_creator(): void
    {
        // Membuat instance kosong untuk menguji relasi
        $careerProjection = new CareerProjection();
        $relation = $careerProjection->creator();

        // Assert
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
        $this->assertEquals('created_by', $relation->getForeignKeyName());
    }
}