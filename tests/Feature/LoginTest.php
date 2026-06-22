<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected Role $superadminRole;
    protected Role $managerRole;
    protected Role $posRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base data
        $this->store = Store::factory()->create([
            'store_name' => 'Cabang Test',
        ]);

        $this->superadminRole = Role::create(['name' => 'Superadmin']);
        $this->managerRole = Role::create(['name' => 'Manager']);
        $this->posRole = Role::create(['name' => 'POS']);
    }

    public function test_superadmin_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'admin@handai.com',
            'password' => Hash::make('password'),
            'role' => 'Superadmin',
        ]);

        $user->roles()->attach($this->superadminRole->id, [
            'store_id' => $this->store->id,
            'is_multistore' => 1,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@handai.com',
            'password' => 'password',
            'login_type' => 'admin',
            'role' => 'Superadmin',
        ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('user_role', 'Superadmin');
        $response->assertSessionHas('isMultistore', 1);
    }

    public function test_manager_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'manager@poshandai.com',
            'password' => Hash::make('password'),
            'role' => 'Manager',
        ]);

        $user->roles()->attach($this->managerRole->id, [
            'store_id' => $this->store->id,
            'is_multistore' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'manager@poshandai.com',
            'password' => 'password',
            'login_type' => 'pegawai',
            'role' => 'Manager',
        ]);

        $response->assertRedirect(route('manager.store'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('user_role', 'Manager');
        $response->assertSessionHas('selected_store', $this->store->id);
    }

    public function test_pos_cashier_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'kasir@poshandai.com',
            'password' => Hash::make('password'),
            'role' => 'POS',
        ]);

        $user->roles()->attach($this->posRole->id, [
            'store_id' => $this->store->id,
            'is_multistore' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'kasir@poshandai.com',
            'password' => 'password',
            'login_type' => 'pegawai',
            'role' => 'POS',
        ]);

        $response->assertRedirect(route('pos.store'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('user_role', 'POS');
        $response->assertSessionHas('selected_store', $this->store->id);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'manager@poshandai.com',
            'password' => Hash::make('password'),
            'role' => 'Manager',
        ]);

        $response = $this->post('/login', [
            'email' => 'manager@poshandai.com',
            'password' => 'wrong_password',
            'login_type' => 'pegawai',
            'role' => 'Manager',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_when_role_does_not_match()
    {
        $user = User::factory()->create([
            'email' => 'manager@poshandai.com',
            'password' => Hash::make('password'),
            'role' => 'Manager',
        ]);

        $user->roles()->attach($this->managerRole->id, [
            'store_id' => $this->store->id,
            'is_multistore' => 0,
        ]);

        // Attempt logging in as POS instead of Manager
        $response = $this->post('/login', [
            'email' => 'manager@poshandai.com',
            'password' => 'password',
            'login_type' => 'pegawai',
            'role' => 'POS',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }
}
