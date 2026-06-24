# Set UID and GID
export UID=$(shell id -u)
export GID=$(shell id -g)

# Подготовка к запуску
setup_dev: init_dev composer_install_dev laravel_init laravel_setup_storage rabbitmq_create_exchange_and_queues stop

# Запуск
run_dev:
	docker compose -f ./docker-compose.yaml --profile dev up -d --build

# Остановка
stop:
	docker compose --profile "*" down

# Создание необходимых exchange и queues для RabbitMQ
rabbitmq_create_exchange_and_queues: rabbitmq_create_exchange rabbitmq_create_sms_queue rabbitmq_create_email_queue rabbitmq_binding_sms_queue rabbitmq_binding_email_queue

init_dev:
	cp -n ./project/.env.example ./project/.env

composer_install_dev:
	docker compose -f ./docker-compose.yaml run --rm --no-deps --workdir /var/www/project php composer install

laravel_init:
	docker compose run --workdir /var/www/project --no-deps --rm php php artisan key:generate

laravel_setup_storage:
	docker compose run --workdir /var/www/project --no-deps --rm php php artisan storage:link

rabbitmq_create_exchange:
	docker compose exec rabbitmq rabbitmqadmin declare exchange name=notifications type=direct

rabbitmq_create_sms_queue:
	docker compose exec rabbitmq rabbitmqadmin declare queue name=sms

rabbitmq_create_email_queue:
	docker compose exec rabbitmq rabbitmqadmin declare queue name=email

rabbitmq_binding_sms_queue:
	docker compose exec rabbitmq rabbitmqadmin declare binding source=notifications destination=sms routing_key=sms

rabbitmq_binding_email_queue:
	docker compose exec rabbitmq rabbitmqadmin declare binding source=notifications destination=email routing_key=email
