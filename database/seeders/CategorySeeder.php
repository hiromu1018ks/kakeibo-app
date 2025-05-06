<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now(); // 現在時刻を取得

        DB::table('categories')->insert([
            // 収入カテゴリ
            ['name' => '給与', 'type' => 'income', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '賞与', 'type' => 'income', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '副収入', 'type' => 'income', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'その他収入', 'type' => 'income', 'created_at' => $now, 'updated_at' => $now],

            // 支出カテゴリ
            ['name' => '食費', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '日用品', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '交通費', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '趣味・娯楽', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '交際費', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '衣服・美容', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '健康・医療', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '住宅', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '水道・光熱費', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '通信費', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '税金・社会保険', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'その他支出', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
