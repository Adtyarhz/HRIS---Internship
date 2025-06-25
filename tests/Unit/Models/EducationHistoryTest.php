<?php

namespace Tests\Unit\Models;

use App\Models\EducationHistory;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EducationHistoryTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        // PERBAIKAN: Berikan ID palsu untuk relasi 'employee_id'
        // agar factory tidak mencoba membuat model Employee terkait di database.
        $history = EducationHistory::factory()->make([
            'employee_id' => 1, // Ini adalah perbaikan kuncinya
            'education_level' => 'S1',
            'institution_name' => 'Universitas Coding Keren',
        ]);

        // Assert
        // Pastikan objek berhasil dibuat dan atributnya benar
        $this->assertInstanceOf(EducationHistory::class, $history);
        $this->assertEquals('S1', $history->education_level);
        $this->assertEquals('Universitas Coding Keren', $history->institution_name);
    }

    #[Test]
    public function it_belongs_to_an_employee(): void
    {
        // TIDAK PERLU DIUBAH: Tes ini sudah terisolasi karena menggunakan 'new'.
        
        // Arrange: Buat instance kosong dari model
        $history = new EducationHistory();

        // Act: Panggil metode relasi untuk mendapatkan objeknya
        $relation = $history->employee();

        // Assert: Verifikasi bahwa deklarasi relasi sudah benar
        // 1. Apakah ini instance dari BelongsTo?
        $this->assertInstanceOf(BelongsTo::class, $relation);

        // 2. Apakah relasi ini mengarah ke model Employee?
        $this->assertInstanceOf(Employee::class, $relation->getRelated());
        
        // 3. Apakah foreign key di tabel 'education_histories' sudah benar?
        $this->assertEquals('employee_id', $relation->getForeignKeyName());

        // 4. Apakah owner key (primary key di tabel 'employees') sudah benar?
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }
}
