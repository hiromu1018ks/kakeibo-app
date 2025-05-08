<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

// Userモデルをインポート
// Transactionモデルをインポート

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // user_id が 1 のユーザーを取得 (存在確認)
        $user = User::find(1);

        if ($user) {
            // user_id:1 のユーザーに対して、ファクトリを使って50件の取引データを生成
            Transaction::factory()->count(50)->create([
                'user_id' => $user->id, // 明示的に user_id を指定
            ]);

            // もし、definition 内で user_id => 1 と固定しているなら、以下でもOK
            // Transaction::factory()->count(50)->create();

            // 特定のユーザーに対してファクトリのカスタムステートを使う場合 (TransactionFactoryにforUserステートを定義した場合)
            // Transaction::factory()->count(50)->forUser($user)->create();

            $this->command->info('User ID:1 のユーザーに50件の取引ダミーデータを追加しました。');
        } else {
            $this->command->warn('User ID:1 のユーザーが見つからなかったため、取引ダミーデータは追加されませんでした。');
        }
    }
}
