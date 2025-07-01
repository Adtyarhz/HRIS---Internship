<?php

namespace Tests\Unit\Models;

// Gunakan alias "as Position" agar kode lebih mudah dibaca sesuai konvensi
use App\Models\Position;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PositionTest extends TestCase
{
    #[Test]
    public function a_position_has_a_title(): void
    {
        // Arrange & Act: Buat instance model di memori menggunakan factory
        $position = Position::factory()->make([
            'title' => 'Lead Developer'
        ]);

        // Assert: Periksa apakah properti sesuai dengan yang di-set
        $this->assertEquals('Lead Developer', $position->title);
        $this->assertIsString($position->title);
    }

    #[Test]
    public function a_position_has_many_employees(): void
    {
        // Arrange: Buat instance kosong dari model
        $position = new Position();

        // Act: Panggil metode relasi untuk mendapatkan objeknya
        $relation = $position->employees();

        // Assert: Verifikasi bahwa deklarasi relasi sudah benar
        // 1. Apakah ini instance dari HasMany?
        $this->assertInstanceOf(HasMany::class, $relation);

        // 2. Apakah relasi ini mengarah ke model Employee?
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        
        // 3. Apakah foreign key di tabel 'employees' sudah benar?
        $this->assertEquals('position_id', $relation->getForeignKeyName());
    }
}