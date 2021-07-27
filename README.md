Что используется
===
PHP - 7.3.2  
PostgreSQL - 13 
Laravel - 8.51.0


Как развернуть
=====================
***
1)```git clone https://github.com/Rabbit864/Parser.git``` - скачать проект на компьютер.  
2)```composer install --no-dev -o``` - скачать зависимости php  
Далее сгенерировать конфиг  
3)```copy .env.example .env``` - на Windows  
3.1)```cp .env.example .env``` - на Linux  
4)```php artisan key:generate --ansi``` - сгенировать ключ  
Далее нужно в файле .env прописать нужные настройки для подключения к бд  
Для PostgreSQL:  
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=postgres
DB_PASSWORD=
5)```php artisan migrate``` - запуск миграций  
6)```php artisan serve``` - запуск тестового сервера  

