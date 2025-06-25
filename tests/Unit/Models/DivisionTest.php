<?php

namespace Tests\Unit\Models;

// Kita gunakan alias "as Division" agar kode lebih mudah dibaca sesuai konvensi
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DivisionTest extends TestCase
{
    #[Test]
    public function a_division_has_a_name(): void
    {
        // Arrange & Act: Buat instance menggunakan factory->make()
        $division = Division::factory()->make([
            'name' => 'Technology'
        ]);

        // Assert: Periksa apakah properti sesuai dan tipenya benar
        $this->assertEquals('Technology', $division->name);
        $this->assertIsString($division->name);
    }

    #[Test]
    public function a_division_has_many_employees(): void
    {
        // Arrange: Buat instance model divisi
        $division = new Division();

        // Act: Panggil metode relasi
        $relation = $division->employees();

        // Assert: Periksa detail deklarasi relasi
        // 1. Apakah metode ini mengembalikan object dari class HasMany?
        $this->assertInstanceOf(HasMany::class, $relation);

        // 2. Apakah relasinya mengarah ke model Employee yang benar?
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        
        // 3. Apakah foreign key yang digunakan di tabel 'employees' sudah benar?
        $this->assertEquals('division_id', $relation->getForeignKeyName());
    }
}