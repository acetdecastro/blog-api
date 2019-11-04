# Blog-api

- Basic CRUD for article (id, title, description)
- Requests via API built from Laravel with JWT
- Built in TDD

## Build Setup for local development
1. Run commands below

``` bash
$ composer install
$ cp .env.example .env (OR) copy .env.example .env
```

2. Change values of DB_USERNAME and DB_PASSWORD in .env file with your SQL server's credentials
  - Optional, change value of DB_DATABASE in .env file to your preference
3. Run commands below

``` bash
$ composer dump-autoload
$ php artisan key:generate
$ php artisan make:schema
$ php artisan migrate
$ php artisan serve
```

4. Clone and follow the steps for the front-end app [blog](https://github.com/acetdecastro/blog) on port 8000