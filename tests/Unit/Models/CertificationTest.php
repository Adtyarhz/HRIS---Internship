<?php

namespace Tests\Unit\Models;

use App\Models\Certification;
use App\Models\CertificationMaterial;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CertificationTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // Kode ini sudah benar
        $certification = Certification::factory()->make([
            'employee_id' => 1,
            'certification_name' => 'My Awesome Certification',
            'issuer' => 'Test Academy',
        ]);

        $this->assertInstanceOf(Certification::class, $certification);
        $this->assertEquals('My Awesome Certification', $certification->certification_name);
        $this->assertEquals('Test Academy', $certification->issuer);
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $certification = Certification::factory()->make([
            'employee_id' => 1,
            'date_obtained' => '2023-05-15',
            'expiry_date' => '2026-05-15',
            'cost' => '1250000.75'
        ]);

        // Assert
        $this->assertInstanceOf(Carbon::class, $certification->date_obtained);
        $this->assertInstanceOf(Carbon::class, $certification->expiry_date);
        $this->assertEquals(1250000.75, $certification->cost); // Tes ini benar karena membandingkan nilai
        
        // PERBAIKAN: Ganti assertIsFloat dengan assertIsNumeric.
        // Ini adalah tes yang lebih tepat untuk cast 'decimal'.
        $this->assertIsNumeric($certification->cost);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // Kode ini sudah benar
        $certification = new Certification();
        $relation = $certification->employee();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        $this->assertEquals('employee_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_has_many_certification_materials(): void
    {
        // Kode ini sudah benar
        $certification = new Certification();
        $relation = $certification->certificationMaterial();
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(CertificationMaterial::class, $relation->getRelated());
        $this->assertEquals('certification_id', $relation->getForeignKeyName());
    }
}
