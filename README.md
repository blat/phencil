# ![Logo](http://www.blizzart.net/~mickael/phencil.png?27) phencil
Home-made PHP framework based on:
* [FastRoute](https://github.com/nikic/FastRoute)
* [Symfony HttpFoundation component](https://symfony.com/doc/current/components/http_foundation.html)
* [Plates](http://platesphp.com)

## Quick start

First, install using [Composer](https://getcomposer.org):
```
$ composer require blat/phencil
```

Then, create an `index.php` file with the following contents:
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new Phencil\App();

$app->get('/', function() {
    return "Hello world!";
});

$app->run();
```

Finaly, test using the built-in PHP server:
```
$ php -S localhost:8000
```

## Templates

Update `index.php` to define your templates folder:
```php
$app = new Phencil\App([
    'templates' => __DIR__ . '/templates',
]);
```

Add a new endpoint calling `render` method with the template name and some variables:
```php
$app->get('/{name}', function($name) {
    return $this->render('hello', ['name' => $name]);
});
```

Create the template file `templates/hello.php`:
```php
<p>Hello <?= $name ?>!</p>
```

## Access to request data

Use `getParam` method to access to `GET` and `POST` parameter:
```php
$app->get('/', function() {
    $foo = $this->getParam('foo');
});
```

Use `getFile` method to access to `FILES`. Result is an [`UploadedFile`](http://api.symfony.com/master/Symfony/Component/HttpFoundation/File/UploadedFile.html):
```php
$app->get('/', function() {
    $file = $this->getFile('bar');
});
```

## Advanced response

Redirect to another URL:
```php
$app->get('/', function() {
    $this->redirect('/login');
});
```

Serve a static file:
```php
$app->get('/download/', function() {
    $this->sendFile('/path/to/some-file.txt', 'pretty-name.txt');
});
```
