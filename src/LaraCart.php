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

	public function getAll ()
	{
		$rows = collect($this->storage->all());
		$cart = $rows->map(function ($row) {
			$item = new Item($row);
			$subItems = $this->getSubs($item);
			$item->put('subitems', $subItems);
			$prices = $this->itemPrices($item);
			$item->put('prices', $prices);
			return $item->storage();
		});
		return $cart;
	}

	public function getItem ($id)
	{
		$row = $this->item($id)->values()[0];
		$item = new Item($row);
		return $item->storage();
	}

	private function item ($id)
	{
		$cart = $this->getAll();
		$item = $cart->where('id', $id);
		return $item;
	}

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
		$row = collect($this->getAll()->get('cart:row:' . $belongTo));
		if (collect($row['subitems'])->contains('id', $id)) {
			$key = collect($row->get('subitems'))->where('id', $id)->keys()[0];
			$this->storage->incr($key, $qnt);
		} else {
			$this->storage->addSubItem($subItem);
		}
	}

	public function delItem ($id)
	{
		$item = $this->item($id);
		return $this->storage->delete($item);
	}

	public function checkItem ($item)
	{
		$cart = $this->getAll();
		return $cart->contains('id', $item);
	}

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

	public function cartPrice ()
	{
		$cart = $this->getAll();
		$price = $cart->sum(function ($item) {
			$rowPrice = $item['prices']['total'];
		    return $rowPrice;
		});
		return $price;
	}

	public function cartPriceDiscount ()
	{
		$cart = $this->getAll();
		$price = $cart->sum(function ($product) {
			$rowDiscPrice = $product['prices']['total_discount'];
		    return $rowDiscPrice;
		});
		return $price;
	}
}