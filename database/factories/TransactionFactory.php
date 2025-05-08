<?php

namespace Database\Factories;
// 名前空間の宣言

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

// Categoryモデルをインポート
// Transactionモデルをインポート
// Userモデルをインポート
// 日付操作ライブラリCarbonをインポート
// Eloquentファクトリーの基底クラスをインポート

/**
 * Transactionモデルのファクトリークラスです。
 * テストデータや初期データの生成に使用されます。
 */
class TransactionFactory extends Factory
{
    /**
     * このファクトリーが対応するモデルのクラス名。
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * モデルのデフォルト状態を定義します。
     * ここで定義された属性でモデルが生成されます。
     *
     * @return array<string, mixed> 生成するモデルの属性の配列
     */
    public function definition(): array
    {
        // 存在するカテゴリの中からランダムに1つ取得します。
        // 注意: この処理はCategorySeederなどによって事前にカテゴリデータが登録されていることを前提としています。
        // カテゴリが存在しない場合、エラーが発生する可能性があります。
        $category = Category::inRandomOrder()->first();

        // カテゴリが取得できなかった場合のフォールバック処理です。
        // 通常、事前にSeederを実行するため、この状況は稀ですが、念のため例外をスローします。
        if (!$category) {
            throw new \Exception('カテゴリが存在しません。CategorySeederを実行してください。');
        }

        // 取得したカテゴリのタイプ（'income' または 'expense'）を変数に格納します。
        // このタイプは取引の種類（収入または支出）として使用されます。
        $type = $category->type;

        // 取引日を生成します。現在から過去3ヶ月以内のランダムな日時を生成します。
        // $this->faker はダミーデータを生成するためのFakerインスタンスです。
        $transactionDate = Carbon::instance($this->faker->dateTimeBetween('-3 months', 'now'));

        // 生成するTransactionモデルの属性を配列で返します。
        return [
            // ユーザーIDを設定します。
            // UserモデルのIDが1のユーザーが存在すればそのIDを、存在しなければ新しいユーザーをファクトリーで作成し、そのIDを使用します。
            // 今回は固定でID 1のユーザーを指定するという指示に基づき、このような条件分岐になっています。
            'user_id' => User::find(1) ? 1 : User::factory(),

            // カテゴリIDを設定します。上でランダムに取得したカテゴリのIDを使用します。
            'category_id' => $category->id,

            // 取引タイプ（収入または支出）を設定します。上で取得したカテゴリのタイプを使用します。
            'type' => $type,

            // 金額を生成します。100から50,000の間のランダムな整数値を設定します。
            'amount' => $this->faker->numberBetween(100, 50000),

            // 取引日を設定します。上で生成したランダムな日付を使用します。
            'transaction_date' => $transactionDate,

            // 説明（メモ）を生成します。70%の確率で5単語程度のランダムな文章を生成し、30%の確率でnullを設定します。
            'description' => $this->faker->optional(0.7)->sentence(5),

            // 作成日時を設定します。
            // 取引日($transactionDate)を基準に、1時から23時の間のランダムな時と、1分から59分の間のランダムな分を追加して設定します。
            // copy()メソッドで元の$transactionDateオブジェクトを変更しないようにコピーを作成しています。
            'created_at' => $transactionDate->copy()->addHours($this->faker->numberBetween(1, 23))->addMinutes($this->faker->numberBetween(1, 59)),

            // 更新日時を設定します。作成日時と同様のロジックで設定します。
            'updated_at' => $transactionDate->copy()->addHours($this->faker->numberBetween(1, 23))->addMinutes($this->faker->numberBetween(1, 59)),
        ];
    }

    /**
     * user_id を指定するためのカスタムステートです。
     * (今回は指示により、直接 definition メソッド内にロジックを記述したため、このメソッドはコメントアウトされています。)
     *
     * 使用例: Transaction::factory()->forUser($user)->create();
     *
     * public function forUser(User $user)
     * {
     *     return $this->state([
     *         'user_id' => $user->id,
     *     ]);
     * }
     */
}
