# LaraCart

**LaraCart** is simple shoping Cart for Laravel framework

## Install

    composer require andskur/laracart

### Configuration

After installing, register the `Andskur\LaraCart\LaraCartServiceProvider` in your `config/app.php` configuration file:

```php
'providers' => [
    // Other service providers...

    Andskur\LaraCart\LaraCartServiceProvider::class,
],
```

Also, add the `LaraCart` facade to the `aliases` array in your `app` configuration file:

```php
'LaraCart' => Andskur\LaraCart\LaraCartFacade::class,
```

### Usage

### Add item to cart

Add a new item.

**example:**

```php

$discount = [
    "value" => 10,
    "type"  => 'percent',
    "start" => 1496228400,
    "end"   => 1497204305
];
$subitems = [
    [1, 1, 'salami', 23],
    [2, 3, 'pepper', 15]
];
LaraCart::storeItem(1, 4, 'pizza', 50, $discount, $subitems);
...
```

### Add sub item to cart row

Add sub item.

**example:**

```php

LaraCart::addSubItem(2, 2, 'salami', 23, 1);
...
```

### Check item in cart

Check item.

**example:**

```php

LaraCart::checkItem($id);
...
```

### Get all cart

All cart.

**example:**

```php

LaraCart::getAll();
...
```

### Get item from cart

Get item.

**example:**

```php

LaraCart::getItem($id);
...
```

### Delete item from cart

Remove item.

**example:**

```php

LaraCart::delItem($id);
...
```

### Get full cart total price

Full price.

**example:**

```php

LaraCart::cartPrice();
...
```

### Get full cart total price with discount

**example:**

```php

LaraCart::cartPriceDiscount();
...
```


## License

LaraCart is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)