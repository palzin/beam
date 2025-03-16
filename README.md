<p align="center">
  <img src="./art/logo.png" height="128" alt="" />
</p>
<h1 align="center">Beam</h1>
<div align="center">
  <h4><a href="https://palzin.app/beam" target="_blank">Download the App</a></h4>
  <sub>Available for Windows, Linux and macOS.</sub>
  <br />
  <br />
  <p>
    <a href="https://palzin.app/beam/docs"> 📚 Documentation </a>
  </p>
</div>
 <br/>
<div align="center">
  
</div>

### 👋 Hello Dev,

<br/>

Beam is a friendly app that boosts your [Laravel](https://larvel.com/) PHP coding and debugging experience.

When using Beam, you can see the result of your debug displayed in a standalone Desktop application.

These are some debug tools available for you:

- [Dump](https://beam.dev/debug/usage.html#dump) single or multiple variables at once.
- Send `dump`, `dd` to Beam app.
- Watch [Laravel Mail](https://laravel.com/docs/mail).
- See your dumped values in a [Table](https://beam.dev/debug/usage.html#table), with a built-in search feature.
- Improve your debugging experience using different [screens](https://beam.dev/debug/usage.html#screens).
- Watch [SQL Queries](hhttps://beam.dev/debug/usage.html#sql-queries).
- Watch Slow Queries [SQL Queries](hhttps://beam.dev/debug/usage.html#sql-queries).
- Monitor [Laravel Logs](https://laravel.com/docs/logging).
- Monitor [Livewire component](https://livewire.laravel.com).
- Validate [JSON strings](https://beam.dev/debug/usage.html#json).
- Verify if a string [contains](https://beam.dev/debug/usage.html#contains) a substring.
- View `phpinfo()` configuration.
- List your [Laravel Routes](https://laravel.com/docs/routing).
- Inspect [Model](https://laravel.com/docs/eloquent) attributes.
- Learn more in our [Reference Sheet](https://beam.dev/debug/reference-sheet.html).
- Multiple Themes (light, dark, dracula, dim, retro ...)
- Shortcuts (clear, always on top)

<br/>

### Get Started

#### Requirements

 PHP 8.1+ and Laravel 10.0+

#### Using Laravel
```shell
 composer require palzin/beam --dev
 ```

#### PHP Project
```shell
 composer require palzin/beam-core --dev
 ```

See also: https://palzin.app/beam

* Debug your code using `ds()` in the same way you would use Laravel's native functions dump() or dd().

* Run your Laravel application and see the debug dump in the Beam App window.

### Example

Here's an example:

```php
// File: routes/web.php

<?php 

Route::get('/', function () {
    ds('Home page accessed!');
    return view('home');
});
```

The Desktop App receives:

<p align="center">
  <img src="./art/light.png" height="500" alt="" />
  <img src="./art/dark.png" height="500" alt="" />
  <img src="./art/dracula.png" height="500" alt="" />
</p>

```php
// File: routes/web.php

<?php 

Route::get('/', function () {
    \App\Models\User::all(); // duplicate query example
    \App\Models\User::all(); // duplicate query example
    \App\Models\Dish::all();
    return '';
});
```

The Desktop App receives:

<p align="center">
  <img src="./art/queries.png" height="500" alt="" />
</p>
