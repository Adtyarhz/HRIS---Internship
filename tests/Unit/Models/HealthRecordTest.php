<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\HealthRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthRecordTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // Arrange & Act
        // Buat objek di memori menggunakan factory
        $healthRecord = HealthRecord::factory()->make([
            'employee_id' => 1,
            'blood_type' => 'O',
            'height' => 175.5,
        ]);

        // Assert
        $this->assertInstanceOf(HealthRecord::class, $healthRecord);
        $this->assertEquals('O', $healthRecord->blood_type);
        $this->assertEquals(175.5, $healthRecord->height);
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        // Arrange
        $healthRecord = HealthRecord::factory()->make([
            'employee_id' => 1,
            'last_checkup_date' => '2025-06-24',
        ]);

        // Assert
        // Pastikan 'last_checkup_date' di-cast menjadi objek Carbon (date)
        $this->assertInstanceOf(Carbon::class, $healthRecord->last_checkup_date);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // Arrange
        $healthRecord = new HealthRecord();

        // Act
        $relation = $healthRecord->employee();

        // Assert
        // Verifikasi deklarasi relasi BelongsTo
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }
}