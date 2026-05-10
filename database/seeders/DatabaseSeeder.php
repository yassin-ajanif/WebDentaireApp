<?php

namespace Database\Seeders;

use App\Entities\Auth\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@espace-dentaire.ma',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $this->call(CatalogSeeder::class);
    }
}
