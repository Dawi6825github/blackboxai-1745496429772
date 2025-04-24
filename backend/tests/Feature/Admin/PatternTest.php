<?php

namespace Tests\Feature\Admin;

use App\Models\Pattern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PatternTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);
    }

    public function test_admin_can_view_patterns_list()
    {
        $patterns = Pattern::factory()->count(3)->create();
        
        $response = $this->actingAs($this->admin)
                         ->get(route('admin.patterns.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('patterns');
        
        foreach ($patterns as $pattern) {
            $response->assertSee($pattern->name);
        }
    }

    public function test_admin_can_create_pattern()
    {
        $patternData = [
            'name' => 'X Pattern',
            'description' => 'Form an X on the card',
            'positions' => json_encode([
                [0, 0], [0, 4], [1, 1], [1, 3], [2, 2], [3, 1], [3, 3], [4, 0], [4, 4]
            ])
        ];
        
        $response = $this->actingAs($this->admin)
                         ->post(route('admin.patterns.store'), $patternData);
        
        $response->assertRedirect(route('admin.patterns.index'));
        $this->assertDatabaseHas('patterns', [
            'name' => 'X Pattern',
            'description' => 'Form an X on the card'
        ]);
    }

    public function test_admin_can_update_pattern()
    {
        $pattern = Pattern::factory()->create();
        
        $updatedData = [
            'name' => 'Updated Pattern',
            'description' => 'Updated description',
            'positions' => json_encode([
                [0, 0], [0, 1], [0, 2], [0, 3], [0, 4]
            ])
        ];
        
        $response = $this->actingAs($this->admin)
                         ->put(route('admin.patterns.update', $pattern), $updatedData);
        
        $response->assertRedirect(route('admin.patterns.index'));
        $this->assertDatabaseHas('patterns', [
            'id' => $pattern->id,
            'name' => 'Updated Pattern',
            'description' => 'Updated description'
        ]);
    }

    public function test_admin_can_delete_pattern()
    {
        $pattern = Pattern::factory()->create();
        
        $response = $this->actingAs($this->admin)
                         ->delete(route('admin.patterns.destroy', $pattern));
        
        $response->assertRedirect(route('admin.patterns.index'));
        $this->assertDatabaseMissing('patterns', [
            'id' => $pattern->id
        ]);
    }

    public function test_non_admin_cannot_access_patterns()
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        
        $response = $this->actingAs($user)
                         ->get(route('admin.patterns.index'));
        
        $response->assertStatus(403);
    }
}
