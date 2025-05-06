<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // メール認証機能を利用する場合にコメントアウトを解除します
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// HasManyリレーション型をインポートします

/**
 * Userモデルクラス
 *
 * アプリケーションのユーザー情報を表現し、認証機能を提供します。
 * LaravelのAuthenticatableを継承することで、ログイン、ログアウトなどの認証関連の機能を簡単に利用できます。
 */
class User extends Authenticatable
{
    /**
     * @use HasFactory<\Database\Factories\UserFactory>
     * HasFactoryトレイトを利用することで、ファクトリ（テストデータ生成機能）とモデルを連携させます。
     * これにより、`User::factory()->create()`のような形で簡単にテストユーザーを作成できます。
     * 開発やテストの効率を大幅に向上させます。
     */
    use HasFactory, Notifiable;

    // Notifiableトレイトは、メール通知などの通知機能をユーザーモデルに追加します。

    /**
     * マスアサインメント可能な属性。
     *
     * create()やupdate()メソッドなどで一括して値を設定できる属性のリストです。
     * 例えば、ユーザー登録フォームから送られてきた 'name', 'email', 'password' の値を一度にモデルにセットできます。
     * これを設定しない場合、個別に ` $user->name = $request->name; ` のように記述する必要があり、手間が増えます。
     * 同時に、意図しない属性が変更されるのを防ぐセキュリティ上の役割も担います（マスアサインメント脆弱性の対策）。
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',     // ユーザー名
        'email',    // メールアドレス
        'password', // パスワード (保存時に自動的にハッシュ化されます。下記casts()メソッド参照)
    ];

    /**
     * シリアライズ時に非表示にする属性。
     *
     * モデルのインスタンスをJSONや配列に変換する際に、このリストに含まれる属性は出力されません。
     * 例えば、APIレスポンスとしてユーザー情報を返す際に、パスワードやリメンバートークンといった機密情報が
     * 外部に漏れるのを防ぐために非常に重要です。
     * これにより、アプリケーションのセキュリティが向上します。
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',         // パスワードはAPIレスポンスなどには含めません。
        'remember_token',   // 「次回から自動的にログインする」機能のためのトークンも同様に非表示にします。
    ];

    /**
     * ユーザーが持つトランザクション（取引履歴など）を取得するためのリレーションを定義します。
     *
     * Eloquentのリレーション機能を利用して、UserモデルとTransactionモデルの関連付けを行います。
     * '1対多' の関係（一人のユーザーが多数のトランザクションを持つ）を表現します。
     * これにより、`$user->transactions` のようにして、特定のユーザーに関連する全てのトランザクションを簡単に取得できます。
     * 例えば、ECサイトであれば顧客の注文履歴、SaaSであればユーザーの利用履歴などが該当します。
     * このようなリレーション定義は、データベースの正規化を保ちつつ、関連データを効率的に扱うために不可欠です。
     *
     * @return HasMany Transactionモデルとの1対多のリレーションを返します。
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class); // Transactionモデルへのパスを指定します。
    }

    /**
     * 属性の型キャスト定義を取得します。
     *
     * モデルの属性がデータベースから取得された際や、モデルにセットされた際に、
     * 特定のデータ型へ自動的に変換するための定義です。
     * これにより、例えば日付文字列をCarbonインスタンス（PHPの日時操作ライブラリ）として扱えたり、
     * パスワードを自動的にハッシュ化したりすることができます。
     * コードの可読性と保守性を高め、データの一貫性を保つのに役立ちます。
     *
     * @return array<string, string> 属性名をキー、キャスト先の型を値とする連想配列。
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' 属性は、データベース上ではTIMESTAMP型などで保存されている日付文字列ですが、
            // PHP側では 'datetime' (実際にはCarbonインスタンス) として扱われます。
            // これにより、日付の比較やフォーマット変更などが容易になります。
            // 例えば、メール認証が完了した日時を管理するのに使われます。
            'email_verified_at' => 'datetime',

            // 'password' 属性は、モデルにセットされる際に自動的にハッシュ化 (bcrypt) されます。
            // データベースにはハッシュ化されたパスワードが保存されるため、元のパスワードを知ることはできません。
            // これはセキュリティ上非常に重要で、万が一データベースが漏洩した場合でも、ユーザーのパスワードを保護します。
            // Laravelの認証機能は、この 'hashed' キャストを前提として動作します。
            'password' => 'hashed',
        ];
    }
}
