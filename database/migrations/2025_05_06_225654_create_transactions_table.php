<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // BIGINT, Primary Key, Auto Increment
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // usersテーブルへの外部キー。ユーザー削除時にトランザクションも削除
            $table->foreignId('category_id')->constrained()->onDelete('restrict'); // categoriesテーブルへの外部キー。カテゴリ削除時はエラー（関連トランザクションがある場合）
            $table->enum('type', ['income', 'expense']); // 'income' または 'expense'
            $table->decimal('amount', 10, 0); // 金額 (整数部10桁、小数点以下0桁の例。必要に応じて変更) ※要件定義書では小数点以下も扱えるようにとあるので、例えば (10,2)など
            $table->date('transaction_date'); // 取引日
            $table->text('description')->nullable(); // メモ (任意なのでnullable)
            $table->timestamps(); // created_at と updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
