<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    // Di-hash sekali lalu dipakai ulang, karena hash password lambat dan sengaja dibuat begitu.
    protected static ?string $password;

    public function definition(): array
    {
        // Domain .test dicadangkan standar internet, jadi email demo tidak pernah menjangkau orang sungguhan.
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->userName().'@presensi.test',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Pegawai#2026'),
            'remember_token' => null,
        ];
    }

    // role dan must_change_password tidak fillable, jadi diisi lewat afterMaking agar tidak ditolak.
    public function configure(): static
    {
        return $this->afterMaking(function (User $user): void {
            $user->role ??= UserRole::Employee;
        });
    }

    public function admin(): static
    {
        return $this->afterMaking(function (User $user): void {
            $user->role = UserRole::Admin;
            $user->password = Hash::make('Admin#2026');
        });
    }

    public function mustChangePassword(): static
    {
        return $this->afterMaking(function (User $user): void {
            $user->must_change_password = true;
        });
    }
}