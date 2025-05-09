<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            {{-- 月ナビゲーション --}}
            <div class="flex items-center space-x-2 mb-4 md:mb-0">
                <a href="{{ $prevMonthLink }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    &laquo; {{ __('前月') }}
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $currentMonthCarbon->format('Y年n月') }}
                </h2>
                <a href="{{ $nextMonthLink }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('翌月') }} &raquo;
                </a>
                {{-- 「今月へ」ボタン（現在の表示が今月でない場合のみ表示するなどの工夫も可能） --}}
                @if(!$currentMonthCarbon->isToday())
                    {{-- 簡単な例：今日が含まれる月でなければ表示 --}}
                    <a href="{{ $thisMonthLink }}"
                       class="ml-4 inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ __('今月へ') }}
                    </a>
                @endif
            </div>

            <a href="{{ route('transactions.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('項目を追加') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- フラッシュメッセージ表示 --}}
            @if (session('success'))
                <div
                    class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-700 dark:text-green-100 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- 集計情報 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- 表示している月を集計タイトルにも反映 --}}
                    <h3 class="text-lg font-medium mb-2 dark:active:bg-gray-100">{{ $currentMonthCarbon->format('Y年n月') . __('の集計') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('収入合計') }}</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($totalIncome) . __('円') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('支出合計') }}</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalExpense) . __('円') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('収支') }}</p>
                            <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($balance) . __('円') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($expensesByCategory->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">
                            {{ $currentMonthCarbon->format('Y年n月') . __('のカテゴリ別支出') }}
                        </h3>
                        <ul class="space-y-2">
                            @foreach ($expensesByCategory as $expense)
                                <li class="flex justify-between items-center">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $expense->category_name }}</span>
                                    <span
                                        class="font-semibold text-red-600 dark:text-red-400">{{ number_format($expense->total_amount) . __('円') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        {{-- もし総支出に対する割合も表示したい場合、ここで計算して表示も可能 --}}
                        {{-- 例: ($expense->total_amount / $totalExpense) * 100 --}}
                    </div>
                </div>
            @endif

            {{-- 取引一覧 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">{{ __('取引履歴') }}</h3>
                    @if($transactions->isEmpty())
                        <p>{{ __('登録されている取引はありません。') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('日付') }}</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('種類') }}</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('カテゴリ') }}</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('金額') }}</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('メモ') }}</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">{{ __('操作') }}</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $transaction->transaction_date->format('Y/m/d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($transaction->type === 'income')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">{{ __('収入') }}</span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">{{ __('支出') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $transaction->category->name ?? '-' }}</td> {{-- カテゴリ名が取得できない場合（通常はないはず）は'-' --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($transaction->amount) . __('円') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $transaction->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('transactions.edit', $transaction) }}"
                                               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 mr-3">{{ __('編集') }}</a>
                                            {{-- 削除はフォームで行うため、後で実装 --}}
                                            <form method="POST"
                                                  action="{{ route('transactions.destroy', $transaction) }}"
                                                  class="inline-block"
                                                  onsubmit="return confirm('本当に削除してもよろしいですか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">{{ __('削除') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- ページネーションリンク --}}
                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
