<?php

namespace Tests\Unit\Models;

use App\Models\CareerHistory;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CareerHistoryTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        $careerHistory = CareerHistory::factory()->make([
            'employee_id' => 1,
            'position_id' => 1,
            'division_id' => 1,
            'employee_type' => 'Fulltime',
            'type' => 'Promosi',
            'notes' => 'Promoted to senior role',
        ]);

        $this->assertInstanceOf(CareerHistory::class, $careerHistory);
        $this->assertEquals(1, $careerHistory->employee_id);
        $this->assertEquals(1, $careerHistory->position_id);
        $this->assertEquals(1, $careerHistory->division_id);
        $this->assertEquals('Fulltime', $careerHistory->employee_type);
        $this->assertEquals('Promosi', $careerHistory->type);
        $this->assertEquals('Promoted to senior role', $careerHistory->notes);
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $careerHistory = CareerHistory::factory()->make([
            'start_date' => '2023-01-15',
            'end_date' => '2024-01-15',
        ]);

        $this->assertInstanceOf(Carbon::class, $careerHistory->start_date);
        $this->assertEquals('2023-01-15', $careerHistory->start_date->toDateString());
        $this->assertInstanceOf(Carbon::class, $careerHistory->end_date);
        $this->assertEquals('2024-01-15', $careerHistory->end_date->toDateString());
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        $careerHistory = new CareerHistory();
        $relation = $careerHistory->employee();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_belongs_to_a_position(): void
    {
        $careerHistory = new CareerHistory();
        $relation = $careerHistory->position();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Position::class, $relation->getRelated());
        $this->assertEquals('position_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_belongs_to_a_division(): void
    {
        $careerHistory = new CareerHistory();
        $relation = $careerHistory->division();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Division::class, $relation->getRelated());
        $this->assertEquals('division_id', $relation->getForeignKeyName());
    }
}