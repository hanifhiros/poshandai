<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\Role;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_number',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_by',
    ];
    // public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user_store')
            ->withPivot('store_id', 'is_multistore') // ini penting
            ->withTimestamps();
    }



    /**
     * Cek apakah user punya role tertentu secara umum
     * Bisa dipakai untuk MultiStore (store_id = null)
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }
        // App\Models\User.php

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'reseller_store', 'reseller_id', 'store_id')
                    ->withPivot('payment_rate', 'qty_sold');
    }


    /**
     * Cek apakah user punya role tertentu di store tertentu
     */
    public function hasRoleInStore(string $roleName, int $storeId): bool
    {
        return $this->roles()
            ->where('name', $roleName)
            ->wherePivot('store_id', $storeId)
            ->exists();
    }

    /**
     * Cek apakah user punya akses global (MultiStore) untuk role tertentu
     */
    public function hasMultiStoreRole(string $roleName): bool
    {
        return $this->roles()
            ->where('name', $roleName)
            ->whereNull('role_user_store.store_id')
            ->exists();
    }
    public function ownedStores()
    {
        return $this->hasMany(Store::class, 'owner_id');
    }
    public function reseller()
{
    return $this->hasOne(\App\Models\Reseller::class, 'user_id');
}


public function accessibleStores()
{
    // Cek jika user adalah Superadmin (role `superadmin`)
    $isSuperAdmin = $this->roles()->where('name', 'superadmin')->exists();

    if ($isSuperAdmin) {
        // Jika superadmin, tampilkan store yang owner_id = user.id
        return Store::where('owner_id', $this->id)->get();
    }

    // Jika bukan superadmin, tampilkan store dari relasi role_user_store
    return Store::whereIn('id', function ($query) {
        $query->select('store_id')
            ->from('role_user_store')
            ->where('user_id', $this->id);
    })->get();
}






}
