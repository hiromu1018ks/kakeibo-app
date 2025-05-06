<?php

use /**
 * Class Migration
 *
 * This abstract class provides a blueprint for defining database migrations within the Laravel framework.
 * Migrations are used for managing and versioning the application's database schema in a consistent and structured way.
 * By extending this class, developers can define `up` and `down` methods to apply and roll back schema changes respectively.
 *
 * @package Illuminate\Database\Migrations
 */
    Illuminate\Database\Migrations\Migration;
use /**
 * Class Blueprint
 *
 * This class is part of the Laravel framework and is used to define and manipulate
 * database table schemas in migrations. It provides a fluent interface to specify
 * table columns, indexes, and constraints.
 *
 * You can utilize this class in migrations to create or modify database tables
 * through the Laravel schema builder.
 *
 * @see Illuminate\Support\Facades\Schema::create()
 * @see Illuminate\Support\Facades\Schema::table()
 */
    Illuminate\Database\Schema\Blueprint;
use /**
 * Facade for the Schema Builder in Laravel.
 *
 * The Schema facade provides a means of interacting with the database's schema
 * in a database-agnostic way. It allows developers to create, drop, and modify
 * database tables and their columns.
 *
 * This facade is typically utilized for database migrations to manage the structure
 * of the application's database.
 *
 * Methods available on this facade correspond to schema builder operations such as:
 * - Creating and dropping tables
 * - Manipulating columns
 * - Managing indexes and foreign keys
 * - Inspecting the database structure
 *
 * Migrations can use this Schema facade to define database structures
 * independent of the SQL syntax used by the underlying database, making it
 * easier to handle multiple database systems.
 *
 * Database connection used: 'pgsql'.
 *
 * @see \Illuminate\Database\Schema\Builder
 * @see \Illuminate\Database\Schema\Blueprint
 */
    Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // BIGINT, Primary Key, Auto Increment
            $table->string('name'); //VARCHAR
            $table->enum('type', ['income', 'expense']); // 'income' または 'expense' を格納
            $table->timestamps(); // created_at と updated_at (nullableはデフォルトで設定されることが多い)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
