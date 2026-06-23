<?php

namespace App\Services\RabbitMQ;

use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService {
    private ?AMQPChannel $channel = null;

    private AMQPStreamConnection $connection;

    private string $exchangeName;

    public function __construct() {
        $this->connection = new AMQPStreamConnection(
            host: Config::get('queue.connections.rabbitmq.host'),
            port: (int) Config::get('queue.connections.rabbitmq.port'),
            user: Config::get('queue.connections.rabbitmq.user'),
            password: Config::get('queue.connections.rabbitmq.password'),
        );

        $this->exchangeName = Config::get('queue.connections.rabbitmq.exchange');
    }

    public function publishNotifications(Collection $notifications): void {
        foreach ($notifications as $notification) {
            $this->publish($notification);
        }
    }

    public function publish(Notification $notification): bool {
        $channel = $this->getChannel();

        // Определяем приоритет уведомления
        $priority = $notification->notificationType->priority === 'high' ? 1 : 0;

        $message = new AMQPMessage(
            body: json_encode([
                'id' => $notification->id,
                'channel' => $notification->channel,
                'recipient' => $notification->recipient,
                'message' => $notification->message,
            ]),
            properties: [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'priority' => $priority,
            ]
        );

        $channel->basic_publish(
            $message,
            $this->exchangeName,
            $notification->channel // sms или email
        );

        return true;
    }

    private function getChannel(): AMQPChannel {
        if ($this->channel === null) {
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
    }
}
