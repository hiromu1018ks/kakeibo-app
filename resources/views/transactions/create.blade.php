<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('新しい取引を登録') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- エラーメッセージ表示 --}}
                    @if ($errors->any())
                        <div class="mb-4">
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('transactions.store') }}" novalidate>
                        @csrf

                        {{-- 取引日 --}}
                        <div class="mt-4">
                            <x-input-label for="transaction_date" :value="__('取引日')"/>
                            <x-text-input id="transaction_date" class="block mt-1 w-full" type="date"
                                          name="transaction_date" :value="old('transaction_date', date('Y-m-d'))"
                                          required autofocus/>
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2"/>
                        </div>

                        {{-- 種類 (収入/支出) --}}
                        <div class="mt-4">
                            <x-input-label for="type" :value="__('種類')"/>
                            <select id="type" name="type"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option
                                    value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>{{ __('支出') }}</option>
                                <option
                                    value="income" {{ old('type') == 'income' ? 'selected' : '' }}>{{ __('収入') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2"/>
                        </div>

                        {{-- カテゴリ --}}
                        <div class="mt-4">
                            <x-input-label for="category_id" :value="__('カテゴリ')"/>
                            <select id="category_id" name="category_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                {{-- カテゴリはコントローラーから渡す --}}
                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2"/>
                        </div>

                        {{-- 金額 --}}
                        <div class="mt-4">
                            <x-input-label for="amount" :value="__('金額')"/>
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount"
                                          :value="old('amount')" required/>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2"/>
                        </div>

                        {{-- メモ --}}
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('メモ')"/>
                            <textarea id="description" name="description" rows="3"
                                      class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2"/>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('登録') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
