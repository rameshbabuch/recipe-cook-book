# recipe-cook-book (RCB)


Recipe Cook Book (RCB) is a simple application to creation recipes.


### Installation

RCB requires [PHP](https://php.net/) > 5.5 to run.
RCB requires [PHPUnit](https://phpunit.de/index.html) > 4.8 to run unit tests.

Download or clone the repository.

Run the sql file located at ```data/db/database.sql``` to create database 

Update database configuration in config.ini.php

Install the dependencies start the server.

```sh
$ cd recipe-cook-book
$ composer install
$ bower install
$ php -S localhost:8000
```

To run unit tests.
```sh
$ phpunit --bootstrap vendor/autoload.php tests/ServicesTest.php
```

License
----



**Free Software**

