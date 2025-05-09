<?php

namespace App\Http\Controllers;
// このファイルがApp\Http\Controllers名前空間に属することを示す

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Categoryモデルを使用する
// Transactionモデルを使用する
// 日付操作ライブラリCarbonを使用する
// HTTPリクエストを扱うクラスを使用する
// 認証関連の機能を提供するAuthファサードを使用する
// バリデーションルールを扱うRuleクラスを使用する

/**
 * 取引 (Transaction) に関するHTTPリクエストを処理するコントローラー
 */
class TransactionController extends Controller
{
    /**
     * 取引の一覧を表示します。
     * ログインユーザーの当月の取引と、その集計結果（収入合計、支出合計、収支）を表示します。
     *
     * @return \Illuminate\Contracts\View\View ビュー (transactions.index)
     */
    public function index(Request $request)
    {
        // 現在認証されているユーザーを取得します。
        $user = Auth::user();

        // --- 表示対象の年月を取得 ---
        // HTTPリクエストから 'year' と 'month' の値を取得します。
        // これらの値が存在しない場合は null になります。
        $requestedYear = $request->input('year');
        $requestedMonth = $request->input('month');

        // Carbonライブラリを使用して、対象となる日付を扱います。
        // CarbonはPHPで日付と時刻を簡単に操作するためのライブラリです。
        if ($requestedYear && $requestedMonth) {
            // 'year' と 'month' がリクエストで指定されている場合の処理
            try {
                // 指定された年と月でCarbonインスタンスを作成します。
                // 月が1桁 (例: 5) でも2桁 (例: 05) でも正しく解釈できるように、
                // 日付のフォーマットを 'Y-n-d' (年-月-日) とし、月の初日 ('-1') を指定してインスタンスを生成します。
                // startOfDay() メソッドで、その日の始まりの時刻 (00:00:00) に設定します。
                $targetDate = Carbon::createFromFormat('Y-n-d', $requestedYear . '-' . $requestedMonth . '-1')->startOfDay();
            } catch (Exception $e) {
                // 指定された年月が無効な場合 (例: 存在しない月が指定されたなど) は、
                // 例外が発生する可能性があるため、catchブロックで補足します。
                // その場合は、現在の日付を対象とします。
                $targetDate = Carbon::now()->startOfDay();
            }
        } else {
            // 'year' または 'month' がリクエストで指定されていない場合は、
            // 現在の日付を対象とします。
            $targetDate = Carbon::now()->startOfDay();
        }

        // 対象月の開始日 (例: 2023-05-01 00:00:00) を取得します。
        // copy() メソッドで元の $targetDate オブジェクトを変更しないようにコピーを作成してから操作します。
        $currentMonthStart = $targetDate->copy()->startOfMonth();
        // 対象月の終了日 (例: 2023-05-31 23:59:59) を取得します。
        $currentMonthEnd = $targetDate->copy()->endOfMonth();

        // --- ナビゲーション用の年月を計算 ---
        // 表示している月の前月の日付を計算します。
        // subMonthNoOverflow() メソッドは、月を1つ減らします。
        // 日付が月の最終日だった場合でも、翌月の同じ日になることを防ぎます (例: 3月31日の1ヶ月前は2月28日または29日)。
        $prevMonthDate = $targetDate->copy()->subMonthNoOverflow();
        // 表示している月の翌月の日付を計算します。
        // addMonthNoOverflow() メソッドは、月を1つ増やします。
        // こちらも同様に、日付のオーバーフローを防ぎます。
        $nextMonthDate = $targetDate->copy()->addMonthNoOverflow();

        // ログインユーザーの、対象月における取引履歴を取得します。
        $transactions = $user->transactions() // Userモデルに定義された transactions リレーションを利用します。
        ->with('category') // 取引履歴に紐づくカテゴリ情報も一緒に取得します (N+1問題対策)。
        ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd]) // 'transaction_date' カラムが対象月の開始日と終了日の間にあるものを絞り込みます。
        ->orderBy('transaction_date', 'desc') // 'transaction_date' (取引日) の降順 (新しいものが先) で並び替えます。
        ->orderBy('created_at', 'desc')      // 取引日が同じ場合は 'created_at' (作成日時) の降順で並び替えます。
        ->paginate(10) // 結果を1ページあたり10件でページネーションします。
        ->appends($request->query()); // ページネーションのリンクに、現在のリクエストのGETパラメータ (例: year, month) を引き継ぎます。

        // 対象月の総収入額を計算します。
        // 'type' カラムが 'income' (収入) である取引の 'amount' (金額) を合計します。
        $totalIncome = $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        // 対象月の総支出額を計算します。
        // 'type' カラムが 'expense' (支出) である取引の 'amount' (金額) を合計します。
        $totalExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        // 対象月の収支を計算します (総収入 - 総支出)。
        $balance = $totalIncome - $totalExpense;

        // --- ★★★ カテゴリ別支出集計 (JOIN使用版) ここから ★★★ ---
        $expensesByCategory = $user->transactions() // 現在のユーザーに関連付けられたトランザクション（取引履歴）を取得するためのクエリを開始します。
        ->join('categories', 'transactions.category_id', '=', 'categories.id') // transactionsテーブルとcategoriesテーブルを結合します。結合キーはtransactionsテーブルのcategory_idとcategoriesテーブルのidです。
        ->where('transactions.user_id', $user->id) // 抽出対象を現在のユーザーのトランザクションに限定します。JOINによりカラム名が重複する可能性があるため、テーブル名を明示的に指定しています。
        ->where('transactions.type', 'expense') // トランザクション種別が 'expense'（支出）であるものに限定します。
        ->whereBetween('transactions.transaction_date', [$currentMonthStart, $currentMonthEnd]) // トランザクションの日付が指定された期間内（当月の開始日から終了日まで）であるものに限定します。
        ->select(
            'categories.name as category_name', // categoriesテーブルのnameカラムをcategory_nameという別名で選択します。これがカテゴリ名となります。
            DB::raw('SUM(transactions.amount) as total_amount') // transactionsテーブルのamountカラムの合計を計算し、total_amountという別名で選択します。DB::raw()は生のSQL式を記述するために使用します。
        )
            ->groupBy('categories.id', 'categories.name') // categoriesテーブルのidとnameでグループ化します。これにより、カテゴリごとにamountが集計されます。
            ->orderBy('total_amount', 'desc') // 集計結果をtotal_amount（合計金額）の降順（多い順）で並び替えます。
            ->get(); // 上記の条件で構築されたクエリを実行し、結果をコレクションとして取得します。
        // --- ★★★ カテゴリ別支出集計 (JOIN使用版) ここまで ★★★ ---

        // 計算結果や必要なデータをビュー (transactions.index) に渡して表示します。
        return view('transactions.index', [
            'transactions' => $transactions,           // 対象月の取引一覧 (ページネーション済みオブジェクト)
            'totalIncome' => $totalIncome,             // 対象月の収入合計額
            'totalExpense' => $totalExpense,           // 対象月の支出合計額
            'balance' => $balance,                     // 対象月の収支
            'currentMonthCarbon' => $targetDate,       // 表示用 (例: "2025年5月" のようにビュー側で整形して使用する) のCarbonインスタンス
            'prevMonthLink' => route('dashboard', ['year' => $prevMonthDate->year, 'month' => $prevMonthDate->month]), // 前月へのリンクURLを生成
            'nextMonthLink' => route('dashboard', ['year' => $nextMonthDate->year, 'month' => $nextMonthDate->month]), // 翌月へのリンクURLを生成
            'thisMonthLink' => route('dashboard'),     // 当月 (パラメータなし) へのリンクURLを生成
            'expensesByCategory' => $expensesByCategory,
        ]);
    }

    /**
     * 新しい取引を作成するためのフォームを表示します。
     *
     * @return \Illuminate\Contracts\View\View ビュー (transactions.create)
     */
    public function create()
    {
        // 登録フォームで選択肢として表示するカテゴリの一覧を取得します
        // 種類 (type) で昇順、次に名前 (name) で昇順に並べ替えます
        $categories = Category::orderBy('type')->orderBy('name')->get();
        // ビュー (resources/views/transactions/create.blade.php) にカテゴリ一覧を渡して表示します
        return view('transactions.create', ['categories' => $categories]);
    }

    /**
     * 新しく作成された取引をストレージ（データベース）に保存します。
     *
     * @param \Illuminate\Http\Request $request HTTPリクエスト
     * @return \Illuminate\Http\RedirectResponse ダッシュボードへリダイレクト
     */
    public function store(Request $request)
    {
        // リクエストされたデータをバリデーション（検証）します
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'], // 取引日は必須、かつ日付形式であること
            'type' => ['required', Rule::in(['income', 'expense'])], // 種類は必須、かつ 'income' または 'expense' のいずれかであること
            'category_id' => ['required', 'exists:categories,id'], // カテゴリIDは必須、かつ categories テーブルの id カラムに存在すること
            'amount' => ['required', 'numeric', 'min:1'], // 金額は必須、かつ数値であり、1以上であること
            'description' => ['nullable', 'string', 'max:1000'], // 説明は任意入力、文字列であり、最大1000文字であること
        ]);

        // バリデーション済みのデータに、現在認証されているユーザーのIDを追加します
        $validated['user_id'] = Auth::id();

        // 選択されたカテゴリIDに対応するカテゴリ情報をデータベースから取得します
        $category = Category::find($validated['category_id']);
        // カテゴリが存在しない、または選択された取引の種類 (income/expense) とカテゴリの種類が一致しない場合
        if (!$category || $category->type !== $validated['type']) {
            // エラーメッセージを添えて、直前の入力画面に戻ります (入力値は保持されます)
            return back()->withErrors(['category_id' => '選択された種類とカテゴリの組み合わせが正しくありません。'])->withInput();
        }

        // バリデーション済みデータを使って、新しい取引レコードを作成し、データベースに保存します
        Transaction::create($validated);

        // ダッシュボード画面 (route 'dashboard') にリダイレクトし、成功メッセージをセッションに保存して表示します
        return redirect()->route('dashboard')->with('success', '取引を登録しました');
    }

    /**
     * 指定された取引の詳細を表示します。(このメソッドは現在使用されていません)
     *
     * @param \App\Models\Transaction $transaction 表示する取引モデルのインスタンス
     * @return void
     */
    public function show(Transaction $transaction)
    {
        // 現状、このメソッドは具体的な処理を持っていません。
        // 必要に応じて、取引の詳細表示ロジックをここに追加します。
    }

    /**
     * 指定された取引を編集するためのフォームを表示します。
     *
     * @param \App\Models\Transaction $transaction 編集する取引モデルのインスタンス
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse ビュー (transactions.edit) またはエラー時リダイレクト
     */
    public function edit(Transaction $transaction)
    {
        // 編集しようとしている取引が、現在認証されているユーザーのものであるかを確認します
        // そうでない場合は、403 (Forbidden) エラーを返します
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'この取引を編集する権限がありません');
        }

        // 編集フォームで選択肢として表示するカテゴリの一覧を取得します
        // 種類 (type) で昇順、次に名前 (name) で昇順に並べ替えます
        $categories = Category::orderBy('type')->orderBy('name')->get();

        // ビュー (resources/views/transactions.edit.blade.php) に取引データとカテゴリ一覧を渡して表示します
        return view('transactions.edit', [
            'transaction' => $transaction, // 編集対象の取引データ
            'categories' => $categories,   // カテゴリ一覧
        ]);
    }

    /**
     * ストレージ（データベース）内の指定された取引を更新します。
     *
     * @param \Illuminate\Http\Request $request HTTPリクエスト
     * @param \App\Models\Transaction $transaction 更新する取引モデルのインスタンス
     * @return \Illuminate\Http\RedirectResponse ダッシュボードへリダイレクト
     */
    public function update(Request $request, Transaction $transaction)
    {
        // 更新しようとしている取引が、現在認証されているユーザーのものであるかを確認します
        // そうでない場合は、403 (Forbidden) エラーを返します
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'この取引を更新する権限がありません。');
        }

        // リクエストされたデータをバリデーション（検証）します
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'], // 取引日は必須、かつ日付形式であること
            'type' => ['required', Rule::in(['income', 'expense'])], // 種類は必須、かつ 'income' または 'expense' のいずれかであること
            'category_id' => ['required', 'exists:categories,id'], // カテゴリIDは必須、かつ categories テーブルの id カラムに存在すること
            'amount' => ['required', 'numeric', 'min:1'], // 金額は必須、かつ数値であり、1以上であること
            'description' => ['nullable', 'string', 'max:1000'], // 説明は任意入力、文字列であり、最大1000文字であること
        ]);

        // 選択されたカテゴリIDに対応するカテゴリ情報をデータベースから取得します
        $category = Category::find($validated['category_id']);
        // カテゴリが存在しない、または選択された取引の種類 (income/expense) とカテゴリの種類が一致しない場合
        if (!$category || $category->type !== $validated['type']) {
            // エラーメッセージを添えて、直前の入力画面に戻ります (入力値は保持されます)
            return back()->withErrors(['category_id' => '選択された種類とカテゴリの組み合わせが正しくありません。'])->withInput();
        }

        // バリデーション済みのデータを使って、該当の取引レコードを更新します
        $transaction->update($validated);

        // ダッシュボード画面 (route 'dashboard') にリダイレクトし、成功メッセージをセッションに保存して表示します
        return redirect()->route('dashboard')->with('success', '取引を更新しました。');
    }

    /**
     * 指定された取引をストレージ（データベース）から削除します。
     *
     * @param \App\Models\Transaction $transaction 削除する取引モデルのインスタンス
     * @return \Illuminate\Http\RedirectResponse ダッシュボードへリダイレクト
     */
    public function destroy(Transaction $transaction)
    {
        // 削除しようとしている取引が、現在認証されているユーザーのものであるかを確認します
        // (ポリシーやゲートを使った認可チェックも考慮に入れるとより堅牢になります)
        // そうでない場合は、403 (Forbidden) エラーを返します
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'この取引を削除する権限がありません。');
        }

        // 該当の取引データをデータベースから削除します
        $transaction->delete();

        // 削除後は一覧画面（この場合はダッシュボード画面、route 'dashboard'）にリダイレクトし、
        // 成功メッセージをセッションに保存して表示します
        return redirect()->route('dashboard')->with('success', '取引を削除しました。');
    }
}
