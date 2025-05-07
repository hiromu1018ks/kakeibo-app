<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->EndOfMonth();

        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // --- 当月の集計 ---
        // 当月の収入合計
        $totalIncome = $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        // 当月の支出合計
        $totalExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        // 当月の収支
        $balance = $totalIncome - $totalExpense;

        // ビューにデータを渡す
        return view('transactions.index', [ // resources/views/transactions/index.blade.php を想定
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'currentMonth' => $now->format('Y年n月'), // 例: "2025年5月"
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('type')->orderBy('name')->get();

        return view('transactions.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['user_id'] = Auth::id();

        $category = Category::find($validated['category_id']);
        if (!$category || $category->type !== $validated['type']) {
            return back()->withErrors(['category_id' => '選択された種類とカテゴリの組み合わせが正しくありません。'])->withInput();
        };

        Transaction::create($validated);

        return redirect()->route('dashboard')->with('success', '取引を登録しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
