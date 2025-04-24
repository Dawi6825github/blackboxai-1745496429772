<?php

namespace Tests\Feature\Admin;

use App\Models\Pattern;
use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoundTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $pattern;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);
        
        $this->pattern = Pattern::factory()->create();
    }

    public function test_admin_can_view_rounds_list()
    {
        $rounds = Round::factory()->count(3)->create();
        
        $response = $this->actingAs($this->admin)
                         ->get(route('admin.rounds.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('rounds');
        
        foreach ($rounds as $round) {
            $response->assertSee($round->name);
        }
    }

    public function test_admin_can_create_round()
    {
        $roundData = [
            'name' => 'Evening Round',
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'prize_pool' => 1000,
            'entry_fee' => 10,
            'status' => 'pending',
            'patterns' => [$this->pattern->id]
        ];
        
        $response = $this->actingAs($this->admin)
                         ->post(route('admin.rounds.store'), $roundData);
        
        $response->assertRedirect(route('admin.rounds.index'));
        $this->assertDatabaseHas('rounds', [
            'name' => 'Evening Round',
            'prize_pool' => 1000,
            'entry_fee' => 10,
            'status' => 'pending'
        ]);
        
        $round = Round::where('name', 'Evening Round')->first();
        $this->assertNotNull($round);
        $this->assertTrue($round->patterns->contains($this->pattern->id));
    }

    public function test_admin_can_update_round()
    {
        $round = Round::factory()->create();
        $round->patterns()->attach($this->pattern->id);
        
        $updatedData = [
            'name' => 'Updated Round',
            'start_time' => now()->addHours(3)->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(4)->format('Y-m-d H:i:s'),
            'prize_pool' => 2000,
            'entry_fee' => 20,
            'status' => 'pending',
            'patterns' => [$this->pattern->id]
        ];
        
        $response = $this->actingAs($this->admin)
                         ->put(route('admin.rounds.update', $round), $updatedData);
        
        $response->assertRedirect(route('admin.rounds.index'));
        $this->assertDatabaseHas('rounds', [
            'id' => $round->id,
            'name' => 'Updated Round',
            'prize_pool' => 2000,
            'entry_fee' => 20
        ]);
    }

    public function test_admin_can_start_round()
    {
        $round = Round::factory()->create(['status' => 'pending']);
        
        $response = $this->actingAs($this->admin)
                         ->post(route('admin.rounds.start', $round));
        
        $response->assertRedirect();
        $this->assertDatabaseHas('rounds', [
            'id' => $round->id,
            'status' => 'active'
        ]);
    }

    public function test_admin_can_call_number()
    {
        $round = Round::factory()->create(['status' => 'active']);
        
        $response = $this->actingAs($this->admin)
                         ->post(route('admin.rounds.call', $round));
        
        $response->assertRedirect();
        $this->assertDatabaseHas('called_numbers', [
            'round_id' => $round->id
        ]);
    }

    public function test_admin_can_end_round()
    {
        $round = Round::factory()->create(['status' => 'active']);
        
        $response = $this->actingAs($this->admin)
                         ->post(route('admin.rounds.end', $round));
        
        $response->assertRedirect();
        $this->assertDatabaseHas('rounds', [
            'id' => $round->id,
            'status' => 'completed'
        ]);
    }

    public function test_non_admin_cannot_access_rounds()
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        
        $response = $this->actingAs($user)
                         ->get(route('admin.rounds.index'));
        
        $response->assertStatus(403);
    }
}
