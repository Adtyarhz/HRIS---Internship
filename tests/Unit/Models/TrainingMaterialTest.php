<?php

namespace Tests\Unit\Models;

use App\Models\TrainingHistory;
use App\Models\TrainingMaterial;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingMaterialTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_a_file_path(): void
    {
        // Arrange & Act
        // Buat objek di memori menggunakan factory
        $material = TrainingMaterial::factory()->make([
            'training_id' => 1,
            'file_path' => 'materi/leadership-101.pdf',
        ]);

        // Assert
        $this->assertInstanceOf(TrainingMaterial::class, $material);
        $this->assertEquals('materi/leadership-101.pdf', $material->file_path);
    }

    #[Test]
    public function it_belongs_to_a_training_history(): void
    {
        // Arrange
        $material = new TrainingMaterial();

        // Act
        $relation = $material->trainingHistory();

        // Assert
        // Verifikasi bahwa deklarasi relasi BelongsTo sudah benar
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(TrainingHistory::class, $relation->getRelated());
        $this->assertEquals('training_id', $relation->getForeignKeyName());
    }
}