# Asynchronous and parallel PHP


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