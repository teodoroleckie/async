# Asynchronous and parallel PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tleckie/async.svg?style=flat-square)](https://packagist.org/packages/tleckie/async)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/teodoroleckie/async/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/teodoroleckie/async/?branch=main)
[![Total Downloads](https://img.shields.io/packagist/dt/tleckie/async.svg?style=flat-square)](https://packagist.org/packages/tleckie/async)


## Installation

You can install the package via composer:

```bash
composer require tleckie/async
```

## Usage

```php
<?php

use Tleckie\Async\Async;

$async = new Async();

foreach([1,2,3,4,5,6,7,8,9,10] as $value){

    $async->add(function() use($value){
        sleep(1);
        return $value*2;
    })->then( function($value){
        var_dump($value);
    })->catch(function(\Exception $exception){
        var_dump($exception->getMessage());
    });
    
}

var_dump($async->wait());
```