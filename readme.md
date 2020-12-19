# Katra

## Installation

Create a new Laravel application:

```
laravel new katra 
```

> You can name your Laravel application anything you want

Update the `.env` file so that the application can connect to the database, 
you will know this was successful if you can run migrations

```
php artisan migrate
```

Install the Katra package using Composer:

```
composer require katra/katra
```

Run the install command:

```
php artisan katra:install
```

Now you can visit the following pages to get started

| Page         | Path      |
|--------------|-----------|
| Registration | /register |
| Login        | /login    |
| Dashboard    | /admin    |

## Screenshots

The default light theme:

![Katra Screenshot](https://raw.githubusercontent.com/wiki/devoption/katra/images/katra-light-profile-default.png)

[Check out the wiki for more screenshots](https://github.com/devoption/katra/wiki/Screenshots) (including the dark version)

## Dependencies

- [Laravel](https://laravel.com/docs/8.x/)
- [Laravel Fortify](https://laravel.com/docs/8.x/fortify)
- [Livewire](https://laravel-livewire.com/docs/2.x/quickstart)
- [Blade Icons](https://blade-ui-kit.com/blade-icons)
  - [Blade Fontawesome Icons](https://github.com/owenvoke/blade-fontawesome)
- [Laravel Mix](https://laravel-mix.com/docs/5.0)
- [Tailwind CSS](https://tailwindcss.com/docs)
  - [Tailwind CSS Filters](https://github.com/Larsklopstra/tailwindcss-css-filters)