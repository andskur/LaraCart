<?php

Route::group(['prefix' => 'cart'], function() {
	Route::group(['prefix' => 'all'], function() {
    	Route::get('/', 'Andskur\LaraCart\Http\LaraCartController@getAll');
		Route::group(['prefix' => 'price'], function() {
    		Route::get('/', 'Andskur\LaraCart\Http\LaraCartController@getAllPrice');
    		Route::get('discount', 'Andskur\LaraCart\Http\LaraCartController@getAllDiscPrice');
		});
	});
	Route::group(['prefix' => 'item'], function() {
    	Route::post('/', 'Andskur\LaraCart\Http\LaraCartController@postItem');
	    Route::get('{item}', 'Andskur\LaraCart\Http\LaraCartController@getItem');
	    Route::post('{item}/sub', 'Andskur\LaraCart\Http\LaraCartController@postSubItem');
	    Route::delete('{item}/delete', 'Andskur\LaraCart\Http\LaraCartController@deleteItem');
	});
});
