<?php

namespace Andskur\LaraCart;

use Carbon\Carbon;
use Andskur\LaraCart\Collections\CartItem as Item;
use Andskur\LaraCart\Collections\CartSubItem as SubItem;
use Andskur\LaraCart\Storage\StorageContract as Storage;

class LaraCart
{
	protected $storage;
	protected $time;
	function __construct(Storage $storage, Carbon $carbon)
	{
		$this->storage = $storage;
		$this->time = $carbon;
	}

	/**
	 * get full cart with total prices
	 * @return array
	 */
	public function getAll ()
	{
		$cart = $this->cart();
		$cart_prices = [
			"price"			=> $this->cartPrice(),
			"priceDiscount" => $this->cartPriceDiscount()
		];
		$cart->put('cartPrices', $cart_prices);
		return $cart;
	}

	/**
	 * Compare cart
	 * @return array
	 */
	protected function cart ()
	{
		$rows = collect($this->storage->all());
		$cart = $rows->map(function ($row) {
			$item = new Item($row);
			$item->put('subitems', $this->getSubs($item));
			$item->put('prices', $this->itemPrices($item));
			return $item->storage();
		});
		return $cart;
	}

	/**
	 * Get formated item
	 * @param  int 	$id 	item id
	 * @return array
	 */
	public function getItem ($id)
	{
		$row = $this->item($id)->values()[0];
		$item = new Item($row);
		return $item->storage();
	}

	/**
	 * Get item from cart
	 * @param  int 	$id 	item id
	 * @return array
	 */
	protected function item ($id)
	{
		$cart = $this->cart();
		$item = $cart->where('id', $id);
		return $item;
	}

	/**
	 * Store item to cart
	 * @param  int     	$id       	item id
	 * @param  int     	$qnt      	item quanity
	 * @param  string  	$name     	item name
	 * @param  float    $price    	item price
	 * @param  array    $discount 	discount array
	 * @param  array 	$subitems 	sub items array
	 * @return array               	stored item
	 */
	public function storeItem ($id, $qnt, $name, $price, $discount = false, array $subitems = null)
	{
		$fields = [
			"id" 		=> $id,
			"qnt"		=> $qnt,
			"name" 		=> $name,
			"price"		=> $price,
		];
		if ($discount != false) {
			$fields['discount'] = $discount;
		}
		$item = new Item($fields);
		if ($this->checkItem($item->get('id'))) {
			$item = $this->item($id);
			$key = $item->keys()[0];
			$this->storage->incr($key, $qnt);
		} else {
			$this->storage->store($item->storage(), 'row');
			$item = $this->item($id);
		}
		$row = substr($item->keys()[0], 9, 1);
		if ($subitems) {
			foreach ($subitems as $subitem) {
				$this->addSubItem($subitem[0], $subitem[1], $subitem[2], $subitem[3], $row);
			}
		}
		return $item;
	}

	/**
	 * Add sub item to cart row
	 * @param  int     	$id       	item id
	 * @param  int     	$qnt      	item quanity
	 * @param  string  	$name     	item name
	 * @param  float    $price    	item price
	 * @param  string 	$belongTo 	cart row
	 * @return array               	stored sub item
	 */
	public function addSubItem ($id, $qnt, $name, $price, $belongTo)
	{
		$fields = [
			"id" 		=> $id,
			"qnt"		=> $qnt,
			"name" 		=> $name,
			"price"		=> $price,
			"belongTo" 	=> $belongTo
		];
		$subItem = new SubItem ($fields);
		$row = collect($this->cart()->get('cart:row:' . $belongTo));
		if (collect($row['subitems'])->contains('id', $id)) {
			$key = collect($row->get('subitems'))->where('id', $id)->keys()[0];
			$this->storage->incr($key, $qnt);
		} else {
			$this->storage->addSubItem($subItem);
		}
		return $subitem;
	}

	/**
	 * Delete item from cart
	 * @param  int 	$id 	item id
	 * @return boolean
	 */
	public function delItem ($id)
	{
		$item = $this->item($id);
		return $this->storage->delete($item->keys()[0]);
	}

	/**
	 * Check item in cart
	 * @param  int 	$id 	item id
	 * @return boolean
	 */
	public function checkItem ($id)
	{
		$cart = $this->cart();
		return $cart->contains('id', $item);
	}

	/**
	 * Get sub items from cart row
	 * @param  object 	$item 	CartItem object
	 * @return array
	 */
	protected function getSubs ($item)
	{
		$subItems = collect([]);
		$item->each(function ($value, $key) use ($item, $subItems) {
			if (starts_with($key, 'sub')) {
				$subItem = new SubItem($this->storage->get($value));
				$subItem->forget('belongTo');
				$subItems->put($value, $subItem);
				$item->forget($key);
			}
		});
		$subItems_prices =  $subItems->sum('fullPrice');
		$subItems->put('price_all', $subItems_prices);
		return $subItems;
	}

	/**
	 * Get cart row total prices
	 * @param  object 	$item 	CartItem object
	 * @return array
	 */
	protected function itemPrices ($item)
	{
		$total = $item->get('fullPrice') + $item->get('subitems')->get('price_all');
		$discount = json_decode($item->get('discount'));
		$total_discount = $total;
		if ($this->checkDiscount($discount)) {
			switch ($discount->type) {
				case 'percent':
					$total_discount -= $total * $discount->value / 100;
					break;
				default:
					$total_discount -= $total - $discount->value;
					break;
			}
		}
		$prices = [
			"total" 			=> $total,
			"total_discount" 	=> $total_discount
		];
		return $prices;
	}

	/**
	 * Check discount period
	 * @param  array 	$discount 	discount array
	 * @return boolean
	 */
	protected function checkDiscount ($discount)
	{
		$start = $discount->start;
		$end = $discount->end;
		$now = $this->time->timestamp;
		if ($start < $now && $now < $end) {
			return true;
		}
		return false;
	}

	/**
	 * Full cart total price
	 * @return float
	 */
	public function cartPrice ()
	{
		$cart = $this->cart();
		$price = $cart->sum(function ($item) {
			$rowPrice = $item['prices']['total'];
		    return $rowPrice;
		});
		return $price;
	}

	/**
	 * Full cart total discount price
	 * @return float	 
	 */
	public function cartPriceDiscount ()
	{
		$cart = $this->cart();
		$price = $cart->sum(function ($product) {
			$rowDiscPrice = $product['prices']['total_discount'];
		    return $rowDiscPrice;
		});
		return $price;
	}
}