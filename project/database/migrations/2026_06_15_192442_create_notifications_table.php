<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('ID получателя');
            $table->unsignedBigInteger('notification_type_id')->comment('ID типа уведомления');
            $table->enum('channel', ['sms', 'email'])->comment('Тип канала');
            $table->string('recipient')->comment('Получатель (номер телефона или email)');
            $table->text('message')->comment('Текст сообщения');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued')->comment('Статус');
            $table->timestamp('sent_at')->nullable()->comment('Время отправки');
            $table->timestamp('delivered_at')->nullable()->comment('Время подтверждения доставки');
            $table->timestamps();
            $table->comment('Таблица уведомлений');

            $table->index(['user_id', 'status'], 'idx_user_id_status');
            $table->index('notification_type_id', 'idx_notification_type_id');

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('notification_type_id')->references('id')->on('notification_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('notifications');
    }
};
