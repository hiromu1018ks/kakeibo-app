<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaction モデルクラス
 *
 * このクラスは、アプリケーション内の「取引」を表すエンティティです。
 * 例えば、家計簿アプリケーションであれば、日々の支出や収入の記録がこれに該当します。
 * Eloquentモデルとして定義されており、データベースの 'transactions' テーブル（複数形が規約）と対応します。
 * CRUD操作（作成、読み取り、更新、削除）やリレーションシップの定義を通じて、取引データを効率的に扱えます。
 */
class Transaction extends Model
{
    /**
     * @use HasFactory<\Database\Factories\TransactionFactory>
     * HasFactory トレイトを使用します。
     *
     * これにより、Transactionモデルに対応するファクトリ (Database\Factories\TransactionFactory) を利用して、
     * テストデータの生成や初期データの投入が容易になります。
     * 例えば、`Transaction::factory()->count(10)->create()` のようにして、簡単に10件の取引データを作成できます。
     * これは、開発効率の向上や、信頼性の高いテストの実施に不可欠です。
     */
    use HasFactory;

    /**
     * マスアサインメント可能な属性
     *
     * `create()` メソッドや `update()` メソッドなどで、一度に複数の属性を安全に設定できるようにするためのホワイトリストです。
     * ここに指定されていない属性は、意図しない書き込みから保護されます（マスアサインメント脆弱性の防止）。
     * 例えば、ユーザーがフォームから送信したデータを使って新しい取引を作成する場合、
     * ここにリストされた属性のみがデータベースに保存されるため、セキュリティが向上します。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',          // この取引を行ったユーザーのID。どのユーザーの取引かを識別するために必須です。リレーションシップを通じてユーザー情報にアクセスできます。
        'category_id',      // この取引が属するカテゴリのID。取引を分類し、分析やレポート作成に役立てるために使用します（例：食費、交通費など）。
        'type',             // 取引の種類（例: 'expense'（支出）、'income'（収入））。データの分類や、残高計算ロジックの分岐などに利用されます。
        'amount',           // 取引金額。この取引の規模を示します。正確な会計処理や予算管理の基礎となります。
        'transaction_date', // 取引が発生した日付。いつの取引かを記録し、月次レポート作成や期間指定でのデータ検索に不可欠です。
        'description',      // 取引に関するメモや詳細。ユーザーが取引内容を後から見返した際に理解しやすくするために使用します。任意入力であることが多いです。
    ];

    /**
     * この取引を行ったユーザーを取得します。
     *
     * TransactionモデルはUserモデルに「属している」(belongsTo)という関係を定義します。
     * これにより、`$transaction->user` のようにして、取引データから関連するユーザー情報（例：ユーザー名、メールアドレス）に簡単にアクセスできます。
     * データベースレベルでは、'transactions'テーブルに'user_id'カラムが存在し、それが'users'テーブルの主キーを参照することを想定しています。
     * このリレーションは、特定のユーザーの取引一覧を表示する際や、権限管理（自分の取引のみ閲覧可能など）に役立ちます。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user():BelongsTo
    {
        // Userクラスとのリレーションを定義します。
        // 'transactions'テーブルの'user_id'カラム（Laravelの規約により自動的に推測されます）を外部キーとして、
        // Userモデルの主キーと関連付けます。
        return $this->belongsTo(User::class);
    }

    /**
     * この取引が属するカテゴリを取得します。
     *
     * TransactionモデルはCategoryモデルに「属している」(belongsTo)という関係を定義します。
     * これにより、`$transaction->category` のようにして、取引データから関連するカテゴリ情報（例：カテゴリ名、カテゴリの種類）に簡単にアクセスできます。
     * データベースレベルでは、'transactions'テーブルに'category_id'カラムが存在し、それが'categories'テーブルの主キーを参照することを想定しています。
     * このリレーションは、カテゴリごとの支出集計や、取引入力時のカテゴリ選択などに利用されます。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category():BelongsTo
    {
        // Categoryクラスとのリレーションを定義します。
        // 'transactions'テーブルの'category_id'カラム（Laravelの規約により自動的に推測されます）を外部キーとして、
        // Categoryモデルの主キーと関連付けます。
        return $this->belongsTo(Category::class);
    }

    /**
     * Eloquentが自動的に型変換する属性とその型を定義します。
     *
     * ここで定義された属性は、データベースから取得した際やモデルに値をセットした際に、指定された型に自動的に変換（キャスト）されます。
     * これにより、例えば日付文字列をPHPの強力な日付操作ライブラリであるCarbonインスタンスとして扱えるようになり、
     * 日付のフォーマット変更、比較、加減算などが非常に簡単かつ安全に行えます。
     * また、モデルをJSONや配列にシリアライズする際も、適切な形式で出力されるようになります。
     *
     * @var array<string, string>
     */
    protected $casts = [ // 追加
        // 'transaction_date' カラムの値をPHPのDateTimeオブジェクト（具体的にはCarbonインスタンス）に自動変換します。
        // データベースには 'YYYY-MM-DD HH:MM:SS' のような文字列で保存されていても、
        // PHPコード内では `$transaction->transaction_date->format('Y年m月d日')` のようにCarbonのメソッドを直接利用できます。
        // これにより、日付処理の記述が簡潔になり、バグの混入を防ぎます。
        'transaction_date' => 'datetime',
    ];
}
