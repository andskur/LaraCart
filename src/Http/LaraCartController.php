<?php

namespace Andskur\LaraCart\Http;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Andskur\LaraCart\LaraCart;

class LaraCartController extends Controller
{
	protected $LaraCart;
	function __construct(LaraCart $LaraCart)
	{
		$this->LaraCart = $LaraCart;
	}

	/**
	 * Get full cart
	 * @return json
	 */
	public function getAll ()
	{
		$cart = $this->LaraCart->getAll();
		return $this->responseJson($cart);
	}

	/**
	 * Get full cart price
	 * @return array [description]
	 */
	public function getAllPrice ()
	{
		$price = $this->LaraCart->cartPrice();
		return $price;
	}

	/**
	 * Get all price with discount
	 * @return array [description]
	 */
	public function getAllDiscPrice ()
	{
		$price = $this->LaraCart->cartPriceDiscount();
		return $price;
	}

	/**
	 * Get item from cart
	 * @param  int 	$id 	item id
	 * @return array
	 */
	public function getItem ($id)
	{
		$item = $this->LaraCart->getItem($id);
		return $this->responseJson($item);
	}

	/**
	 * Store item to cart
	 * @param  Request $request Post data
	 * @return boolean
	 */
	public function postItem (Request $request)
	{
		$id 	= $request->id;
		$qnt 	= $request->qnt;
		$name 	= $request->name;
		$price 	= $request->price;

		$discount = [
			"value" => $request->discount_value,
			"type"	=> $request->discount_type,
			"start"	=> $request->discount_start,
			"end"	=> $request->discount_end
		];

		$subitems = $request->input('subitems', false);

		return $this->LaraCart->storeItem($id, $qnt, $name, $price, $discount, $subitems);
	}

	/**
	 * Store sub item to cart
	 * @param  Request $request Post data
	 * @return boolean
	 */
	public function postSubItem ($item, Request $request)
	{
		$id 	= $request->id;
		$qnt 	= $request->qnt;
		$name 	= $request->name;
		$price 	= $request->price;

		return $this->LaraCart->storeItem($id, $qnt, $name, $price, $item);
	}

	/**
	 * Delete item from cart
	 * @param  int 	$id 	item id
	 * @return boolean
	 */
	public function deleteItem ($id)
	{
		return $this->LaraCart->delItem($id);
	}

	/**
	 * Build pretty Json response
	 * @param  array 	$data
	 * @return json
	 */
	private function responseJson ($json)
	{
		return Response::json($json, 200, [ ], JSON_PRETTY_PRINT);
	}
}