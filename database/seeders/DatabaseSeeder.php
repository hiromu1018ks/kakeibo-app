<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // test@example.com のユーザーが存在しない場合のみ作成
        User::factory()->firstOrCreate(
            ['email' => 'test@example.com'], // 検索条件
            [                                 // 見つからなかった場合に作成するデータ
                'name' => 'Test User',
                'password' => bcrypt('password'), // パスワードも指定する必要がある場合
            ]
        );


        $this->call([
            CategorySeeder::class
        ]);
    }
}
