<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 開発用のテストユーザーを作成 (ID:1 になる想定)
        // UserFactory を経由せず、User モデルの firstOrCreate を直接呼び出す
        User::firstOrCreate(
            ['email' => 'test@example.com'], // 検索条件
            [                                 // 見つからなかった場合に作成するデータ
                'name' => 'Test User',
                // 'password' => bcrypt('password'), // Userモデルに password のミューテタがあれば不要な場合も
                // UserFactoryのデフォルトパスワード設定に合わせるのが良い
                // Userモデルに $fillable が設定されているか、または guarded = [] であることを確認
                // 通常、Breezeを導入していればUserモデルは適切に設定されている
                // UserFactory を使わないので、パスワードはここでハッシュ化する必要がある
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                // email_verified_at も設定しておくとログイン時に問題が起きにくい
                'email_verified_at' => now(),
            ]
        );


        $this->call([
            CategorySeeder::class,
            TransactionSeeder::class
        ]);
    }
}
