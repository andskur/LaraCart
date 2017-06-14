<?php
namespace Andskur\LaraCart;
use Illuminate\Support\Facades\Facade;
class LaraCartFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'LaraCart';
    }
}