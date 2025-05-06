<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Categoryモデルクラス
 *
 * アプリケーション内のカテゴリ情報を表現し、データベースの'categories'テーブル（Laravelの命名規則に従う場合）と対応します。
 * 家計簿アプリであれば「食費」「交通費」、ブログシステムであれば記事の「技術」「趣味」などが該当します。
 */
class Category extends Model
{
    use HasFactory; // HasFactoryトレイトを使用することで、Categoryモデルのファクトリを簡単に利用できるようになります。これは主にテストデータの準備に役立ちます。

    /**
     * マスアサインメント可能な属性
     *
     * createメソッドやupdateメソッドなどで一度に複数の属性を安全に設定できるようにするためのホワイトリストです。
     * これにより、意図しないカラムへの値の書き込みを防ぎ、セキュリティを向上させます。
     * 例えば、ユーザーがフォームから送信したデータを使って新しいカテゴリを作成する場合、ここにリストされた属性のみがデータベースに保存されます。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',          // このカテゴリを作成したユーザーのID。どのユーザーがこのカテゴリを所有しているかを示します。リレーションシップを通じてユーザー情報にアクセスできます。
        'category_id',      // 親カテゴリのID。カテゴリを階層構造にする場合（例：食費 -> 外食）に使用します。null許容であればトップレベルのカテゴリを示します。
        'type',             // カテゴリの種類（例: 'expense'（支出）、'income'（収入）など）。データの分類やレポート作成時に利用されます。
        'amount',           // 金額や数量。このカテゴリに関連する何らかの数値データ（例：予算額、取引額の合計など）。
        'transaction_date', // 取引日や有効期限など、このカテゴリに関連する日付。Carbonインスタンスとして扱われます。
        'description',      // カテゴリに関する詳細な説明やメモ。ユーザーがカテゴリの内容を理解しやすくするために使用します。
    ];

    /**
     * このカテゴリを所有するユーザーを取得します。
     *
     * CategoryモデルはUserモデルに「属している」(belongsTo)という関係を定義します。
     * これにより、$category->user のようにして、カテゴリから関連するユーザー情報に簡単にアクセスできます。
     * データベースレベルでは、'categories'テーブルに'user_id'カラムが存在し、それが'users'テーブルの主キーを参照することを想定しています。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        // Userクラスとのリレーションを定義します。
        // 'categories'テーブルの'user_id'カラム（デフォルト）を外部キーとして、Userモデルの主キーと関連付けます。
        return $this->belongsTo(User::class);
    }

    /**
     * このカテゴリの親カテゴリを取得します（自己参照リレーション）。
     *
     * Categoryモデルは別のCategoryモデルに「属している」(belongsTo)という関係を定義できます。
     * これにより、カテゴリを階層構造で管理することができます（例：親カテゴリ「趣味」の子カテゴリ「読書」）。
     * $category->category のようにして、子カテゴリから親カテゴリ情報にアクセスできます。
     * データベースレベルでは、'categories'テーブルに'category_id'カラムが存在し、それが同じ'categories'テーブルの別のレコードの主キーを参照することを想定しています。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        // Categoryクラス自身とのリレーションを定義します（自己参照）。
        // 'categories'テーブルの'category_id'カラム（デフォルト）を外部キーとして、Categoryモデルの主キーと関連付けます。
        return $this->belongsTo(Category::class);
    }

    /**
     * Eloquentが自動的に型変換する属性とその型を定義します。
     *
     * ここで定義された属性は、データベースから取得した際に指定された型に自動的に変換されます。
     * これにより、例えば日付文字列をCarbonインスタンスとして扱えるようになり、日付操作が容易になります。
     * また、JSONとしてシリアライズされる際も、適切な形式で出力されます。
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'transaction_date' カラムの値をPHPのDateTimeオブジェクト（具体的にはCarbonインスタンス）に自動変換します。
        // これにより、日付のフォーマット変更や比較、加減算などが簡単に行えるようになります。
        'transaction_date' => 'datetime',
    ];
}
