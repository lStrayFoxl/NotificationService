<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique()->comment('Тип уведомления');
            $table->string('name')->comment('Название типа');
            $table->enum('priority', ['high', 'normal'])->comment('Приоритет типа');
            $table->text('description')->nullable()->comment('Описание типа');
            $table->timestamps();
            $table->comment('Таблица типов уведомлений');
        });
    }

    public function down(): void {
        Schema::dropIfExists('notification_types');
    }
};
