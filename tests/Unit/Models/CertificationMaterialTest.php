<?php

namespace Tests\Unit\Models;

use App\Models\Certification;
use App\Models\CertificationMaterial;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CertificationMaterialTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_a_file_path(): void
    {
        // Arrange & Act
        // Buat objek di memori menggunakan factory
        $material = CertificationMaterial::factory()->make([
            'certification_id' => 1,
            'file_path' => 'test/materi-penting.zip',
        ]);

        // Assert
        $this->assertInstanceOf(CertificationMaterial::class, $material);
        $this->assertEquals('test/materi-penting.zip', $material->file_path);
    }

    #[Test]
    public function it_belongs_to_a_certification(): void
    {
        // Arrange
        $material = new CertificationMaterial();

        // Act
        $relation = $material->certification();

        // Assert
        // Verifikasi bahwa deklarasi relasi BelongsTo sudah benar
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Certification::class, $relation->getRelated());
        $this->assertEquals('certification_id', $relation->getForeignKeyName());
    }
}