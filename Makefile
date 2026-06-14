# Set UID and GID
export UID=$(shell id -u)
export GID=$(shell id -g)

#Подготовка к запуску
setup_dev: init_dev composer_install_dev laravel_init laravel_setup_storage stop

#Запуск
run_dev:
	docker compose -f ./docker-compose.yaml --profile dev up -d --build

#Остановка
stop:
	docker compose --profile "*" down

init_dev:
	cp -n ./project/.env.example ./project/.env

composer_install_dev:
	docker compose -f ./docker-compose.yaml run --rm --no-deps --workdir /var/www/project php composer install

laravel_init:
	docker compose run --workdir /var/www/project --no-deps --rm php php artisan key:generate

laravel_setup_storage:
	docker compose run --workdir /var/www/project --no-deps --rm php php artisan storage:link