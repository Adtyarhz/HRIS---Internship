<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Insurance;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InsuranceTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // Arrange & Act
        // Buat objek di memori menggunakan factory
        $insurance = Insurance::factory()->make([
            'employee_id' => 1,
            'insurance_number' => 'KES-123456789',
            'insurance_type' => 'KES',
        ]);

        // Assert
        $this->assertInstanceOf(Insurance::class, $insurance);
        $this->assertEquals('KES-123456789', $insurance->insurance_number);
        $this->assertEquals('KES', $insurance->insurance_type);
    }

    #[Test]
    public function it_casts_date_attributes_correctly(): void
    {
        // Arrange
        $insurance = Insurance::factory()->make([
            'employee_id' => 1,
            'start_date' => '2023-01-01',
            'expiry_date' => '2028-01-01',
        ]);

        // Assert
        // Pastikan atribut tanggal di-cast menjadi objek Carbon
        $this->assertInstanceOf(Carbon::class, $insurance->start_date);
        $this->assertInstanceOf(Carbon::class, $insurance->expiry_date);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // Arrange
        $insurance = new Insurance();

        // Act
        $relation = $insurance->employee();

        // Assert
        // Verifikasi deklarasi relasi BelongsTo
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }
}