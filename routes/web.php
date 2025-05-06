<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| ここでは、アプリケーションのウェブルートを登録します。
| これらのルートは RouteServiceProvider によってロードされ、
| すべて "web" ミドルウェアグループに割り当てられます。
| アプリケーションの素晴らしいものを構築しましょう！
|
*/

// --- アプリケーションの公開ルート ---

// アプリケーションのルートURL ("/") にアクセスした際のルート定義です。
// 目的: ユーザーが最初にサイトにアクセスしたときに表示されるページ（例: ウェルカムページ）。
// 関連: ログインしていないユーザーが最初に訪れる可能性のあるページ。
//       将来的には、ログイン済みならダッシュボードへリダイレクトするなどの処理追加も検討可能。
Route::get('/', function () {
    return view('welcome');
});

// --- 認証関連ルートの読み込み ---
// 目的: ログイン、新規ユーザー登録、パスワードリセットなどの認証機能に関するルート群を読み込みます。
//       これらのルート定義は `routes/auth.php` に分離されており、コードの整理に貢献します。
// 関連: /login, /register, /forgot-password, /reset-password などのURLが利用可能になります。
//       Laravel Breezeによって自動的に設定されます。
require __DIR__ . '/auth.php';


// --- 認証済みユーザー向けルート ---
// 以下のルートは、ログイン済み('auth')かつメール認証済み('verified')のユーザーのみアクセス可能です。

Route::middleware(['auth', 'verified'])->group(function () {

    // --- プロフィール管理関連ルート (Laravel Breeze標準機能) ---
    // 目的: ユーザーが自身のプロフィール情報を管理するための機能を提供します。
    // 関連: ユーザー名、メールアドレス、パスワードの変更、アカウント削除など。

    // プロファイル編集画面表示 (例: /profile)
    // 担当コントローラー・メソッド: ProfileController@edit
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // プロファイル情報更新処理 (例: /profile へのPATCHリクエスト)
    // 担当コントローラー・メソッド: ProfileController@update
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // プロファイル削除（アカウント削除）処理 (例: /profile へのDELETEリクエスト)
    // 担当コントローラー・メソッド: ProfileController@destroy
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // --- 家計簿アプリケーションコア機能ルート ---

    // ログイン後のメイン画面（家計簿一覧）
    // 目的: ログイン・新規登録後、ユーザーを最初に案内するメインページとして家計簿一覧画面を表示します。
    // URL: /dashboard (ルート名は'dashboard'のままにすることでBreezeのデフォルトリダイレクトを活用)
    // 担当コントローラー・メソッド: TransactionController@index
    // 関連: `Route::resource('transactions', ...)` の `index` アクションと同じ機能を提供しますが、
    //       こちらはログイン後のデフォルトランディングページとしての役割が強いです。
    Route::get('/dashboard', [TransactionController::class, 'index'])->name('dashboard');

    // 家計簿データ (Transaction) のCRUD操作のためのリソースルート
    // 目的: 家計簿の収入・支出項目の作成、一覧表示、詳細表示、編集、更新、削除といった
    //       一連の操作（CRUD）に対応するルートを効率的に定義します。
    // 担当コントローラー: TransactionController
    // 生成される主なルートと担当メソッド:
    //   - GET     /transactions             (transactions.index)   -> TransactionController@index   (家計簿一覧表示 ※上記 /dashboard と機能重複の可能性あり)
    //   - GET     /transactions/create      (transactions.create)  -> TransactionController@create  (新規登録フォーム表示)
    //   - POST    /transactions             (transactions.store)   -> TransactionController@store   (新規登録処理)
    //   - GET     /transactions/{transaction} (transactions.show)    -> TransactionController@show    (個別詳細表示)
    //   - GET     /transactions/{transaction}/edit (transactions.edit)   -> TransactionController@edit    (編集フォーム表示)
    //   - PUT/PATCH /transactions/{transaction} (transactions.update)  -> TransactionController@update  (更新処理)
    //   - DELETE  /transactions/{transaction} (transactions.destroy) -> TransactionController@destroy (削除処理)
    // 関連: アプリケーションの主要なデータ操作機能。各画面やフォーム送信に対応します。
    Route::resource('transactions', TransactionController::class);
});

// ダッシュボードルートの以前の定義（コメントアウトされています）。
// 現在は上記の Route::get('/dashboard', [TransactionController::class, 'index']) がその役割を担っています。
//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');
