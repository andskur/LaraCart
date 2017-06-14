<?php

namespace Andskur\LaraCart\Storage;

use Illuminate\Contracts\Redis\Factory as Redis;

/**
 * Redis storage implementation
 */
class StorageRedis implements StorageContract
{
	private $client;
	private $prefix = 'cart:';
	public function __construct (Redis $client)
	{
		$this->client = $client;
	}

	/**
	 * Get all items from redis storage
	 * @return array
	 */
	public function all ()
	{
		$cart = [];
		$rows = $this->client->keys($this->prefix . 'row:*');
		foreach ($rows as $row) {
			$item = $this->client->hgetall($row);
			$cart[$row] = $item;
		}
		return $cart;
	}

	/**
	 * Store item to  redis storage
	 * @param  array 	$item 	array of item values
	 * @return array 			stored item
	 */
	public function store ($item)
	{
		$key = $this->genKey($item, 'row');
		$item = $this->client->hmset($key, $item);
		return $item;
	}

	/**
	 * Get item from redis storage
	 * @param  string $key storage key
	 * @return array      item
	 */
	public function get ($key)
	{
		$item = $this->client->hgetall($key);
		return $item;
	}

	/**
	 * Increase item quanity
	 * @param  int 	$id  Row id
	 * @param  int 	$qnt increase on quanity
	 * @return int
	 */
	public function incr ($key, $qnt)
	{
		return $this->client->hincrby($key, 'qnt', $qnt);
	}

	/**
	 * Delete item from redis storage
	 * @param  string 	$key 	key for remove
	 * @return boolean
	 */
	public function delete ($key)
	{
		return $this->client->del($key);
	}

	/**
	 * Add sub item to item and store it to redis storage
	 * @param array		$subItem 	subitem to added
	 * @return array
	 */
	public function addSubItem ($subItem)
	{
		$keySub = $this->genKey($subItem, 'sub');
		$this->client->hmset($keySub, $subItem->all());
		$keyRow = 'cart:row:' . $subItem->get('belongTo');
		$subId = 'sub_' . $subItem->get('id');
		$this->client->hset($keyRow, $subId, $keySub);
		return $subItem;
	}

	/**
	 * Generate redis key
	 * @param  string $type Item or Sub item
	 * @return string       redis key
	 */
	private function genKey ($type)
	{
		$prefix = $this->prefix . $type . ':';
		$rows = collect($this->client->keys($prefix . '*'));
		$row = $rows->count() + 1;
		$key = $prefix . $row;
		return $key;
	}
}