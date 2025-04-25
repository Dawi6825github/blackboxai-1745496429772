<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_admin_can_view_users_list()
    {
        $users = User::factory()->count(3)->create();
        
        $response = $this->actingAs($this->admin)
                         ->get(route('admin.users.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('users');
        
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => false,
            'balance' => 100
        ];
        
        $response = $this->actingAs($this->admin)
                         ->post(route('admin.users.store'), $userData);
        
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => false,
            'balance' => 100
        ]);
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create();
        
        $updatedData = [
            'name' => 'Updated User',
            'email' => $user->email,
            'is_admin' => true,
            'balance' => 200
        ];
        
        $response = $this->actingAs($this->admin)
                         ->put(route('admin.users.update', $user), $updatedData);
        
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'is_admin' => true,
            'balance' => 200
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($this->admin)
                         ->delete(route('admin.users.destroy', $user));
        
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    public function test_non_admin_cannot_access_user_management()
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);
        
        $response = $this->actingAs($user)
                         ->get(route('admin.users.index'));
        
        $response->assertStatus(403);
    }
}
