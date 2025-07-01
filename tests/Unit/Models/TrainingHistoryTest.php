<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\TrainingHistory;
use App\Models\TrainingMaterial;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingHistoryTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // Arrange & Act
        $training = TrainingHistory::factory()->make([
            'employee_id' => 1,
            'training_name' => 'Advanced Leadership Workshop',
            'provider' => 'Leader Academy',
        ]);

        // Assert
        $this->assertInstanceOf(TrainingHistory::class, $training);
        $this->assertEquals('Advanced Leadership Workshop', $training->training_name);
        $this->assertEquals('Leader Academy', $training->provider);
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        // Arrange
        $training = TrainingHistory::factory()->make([
            'employee_id' => 1,
            'start_date' => '2024-02-20',
            'end_date' => '2024-02-23',
            'cost' => '2500000.00'
        ]);

        // Assert
        $this->assertInstanceOf(Carbon::class, $training->start_date);
        $this->assertInstanceOf(Carbon::class, $training->end_date);
        $this->assertEquals(2500000.00, $training->cost);
        $this->assertIsNumeric($training->cost);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // Arrange
        $training = new TrainingHistory();

        // Act
        $relation = $training->employee();

        // Assert
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_has_many_training_materials(): void
    {
        // Arrange
        $training = new TrainingHistory();

        // Act
        $relation = $training->trainingMaterial();

        // Assert
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(TrainingMaterial::class, $relation->getRelated());
        $this->assertEquals('training_id', $relation->getForeignKeyName());
    }
}