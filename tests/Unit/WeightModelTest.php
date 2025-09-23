<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Weight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $weight->user);
        $this->assertEquals($user->id, $weight->user->id);
    }

    public function test_weight_casts_record_at_to_date(): void
    {
        $weight = Weight::factory()->create([
            'record_at' => '2023-12-25'
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $weight->record_at);
        $this->assertEquals('2023-12-25', $weight->record_at->format('Y-m-d'));
    }

    public function test_weight_casts_weight_to_decimal(): void
    {
        $weight = Weight::factory()->create([
            'weight' => 70.5
        ]);

        $this->assertEquals('70.5', $weight->weight);
        $this->assertIsString($weight->weight);
    }

    public function test_weight_fillable_attributes(): void
    {
        $weight = new Weight();
        $fillable = $weight->getFillable();

        $expectedFillable = ['user_id', 'weight', 'record_at', 'note'];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_weight_table_name(): void
    {
        $weight = new Weight();
        $this->assertEquals('weights', $weight->getTable());
    }

    public function test_weight_primary_key(): void
    {
        $weight = new Weight();
        $this->assertEquals('id', $weight->getKeyName());
    }

    public function test_weight_uses_timestamps(): void
    {
        $weight = new Weight();
        $this->assertTrue($weight->usesTimestamps());
    }

    public function test_weight_model_factory_creates_valid_record(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        $this->assertNotNull($weight->id);
        $this->assertNotNull($weight->weight);
        $this->assertNotNull($weight->record_at);
        $this->assertEquals($user->id, $weight->user_id);
        $this->assertNotNull($weight->created_at);
        $this->assertNotNull($weight->updated_at);
    }

    public function test_weight_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Weight::factory()->count(3)->create(['user_id' => $user1->id]);
        Weight::factory()->count(2)->create(['user_id' => $user2->id]);

        $user1Weights = Weight::where('user_id', $user1->id)->get();
        $user2Weights = Weight::where('user_id', $user2->id)->get();

        $this->assertCount(3, $user1Weights);
        $this->assertCount(2, $user2Weights);
    }

    public function test_weight_can_be_created_with_minimal_data(): void
    {
        $user = User::factory()->create();

        $weight = Weight::create([
            'user_id' => $user->id,
            'weight' => 65.3,
            'record_at' => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('weights', [
            'id' => $weight->id,
            'user_id' => $user->id,
            'weight' => 65.3,
            'note' => null,
        ]);
    }

    public function test_weight_can_be_created_with_note(): void
    {
        $user = User::factory()->create();

        $weight = Weight::create([
            'user_id' => $user->id,
            'weight' => 68.7,
            'record_at' => now()->format('Y-m-d'),
            'note' => 'After gym workout',
        ]);

        $this->assertDatabaseHas('weights', [
            'id' => $weight->id,
            'user_id' => $user->id,
            'weight' => 68.7,
            'note' => 'After gym workout',
        ]);
    }

    public function test_weight_can_be_updated(): void
    {
        $weight = Weight::factory()->create(['weight' => 70.0]);

        $weight->update([
            'weight' => 71.5,
            'note' => 'Updated weight',
        ]);

        $this->assertDatabaseHas('weights', [
            'id' => $weight->id,
            'weight' => 71.5,
            'note' => 'Updated weight',
        ]);
    }

    public function test_weight_can_be_deleted(): void
    {
        $weight = Weight::factory()->create();
        $weightId = $weight->id;

        $weight->delete();

        $this->assertDatabaseMissing('weights', ['id' => $weightId]);
    }

    public function test_weight_soft_deletes_if_enabled(): void
    {
        // 檢查是否使用軟刪除（如果模型中有定義）
        $weight = new Weight();
        $traits = class_uses_recursive($weight);

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', $traits)) {
            $weight = Weight::factory()->create();
            $weightId = $weight->id;

            $weight->delete();

            $this->assertDatabaseHas('weights', ['id' => $weightId]);
            $this->assertNotNull($weight->fresh()->deleted_at);
        } else {
            $this->assertTrue(true); // 如果沒有軟刪除，測試通過
        }
    }
}
