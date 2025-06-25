<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\FamilyDependent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FamilyDependentTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // PERBAIKAN: Berikan ID palsu untuk relasi 'employee_id'
        // agar factory tidak mencoba membuat model Employee terkait di database.
        $dependent = FamilyDependent::factory()->make([
            'employee_id' => 1, // Ini adalah perbaikan kuncinya
            'contact_name' => 'Siti Aminah',
            'relationship' => 'Istri',
        ]);

        // Assert
        $this->assertInstanceOf(FamilyDependent::class, $dependent);
        $this->assertEquals('Siti Aminah', $dependent->contact_name);
        $this->assertEquals('Istri', $dependent->relationship);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // TIDAK PERLU DIUBAH: Tes ini sudah terisolasi karena menggunakan 'new'.
        
        // Arrange
        $dependent = new FamilyDependent();

        // Act
        $relation = $dependent->employee();

        // Assert
        // Verifikasi bahwa deklarasi relasi BelongsTo sudah benar
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }
}
