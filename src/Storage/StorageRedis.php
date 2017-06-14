<?php

namespace Andskur\LaraCart\Storage;

use Illuminate\Contracts\Redis\Factory as Redis;

class StorageRedis implements StorageContract
{
	private $client;
	private $prefix = 'cart:';
	public function __construct (Redis $client)
	{
		$this->client = $client;
	}

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

	public function store ($item)
	{
		$key = $this->genKey($item, 'row');
		$this->client->hmset($key, $item);
	}

	public function get ($key)
	{
		$item = $this->client->hgetall($key);
		return $item;
	}

	public function incr ($key, $qnt)
	{
		return $this->client->hincrby($key, 'qnt', $qnt);
	}

	public function delete ($item)
	{
		$key = $item->keys()[0];
		return $this->client->del($key);
	}

	public function addSubItem ($subItem)
	{
		$keySub = $this->genKey($subItem, 'sub');
		$this->client->hmset($keySub, $subItem->all());
		$keyRow = 'cart:row:' . $subItem->get('belongTo');
		$subId = 'sub_' . $subItem->get('id');
		$this->client->hset($keyRow, $subId, $keySub);
		return $subItem->get('belongTo');
	}

	private function genKey ($item, $type)
	{
		$prefix = $this->prefix . $type . ':';
		$rows = collect($this->client->keys($prefix . '*'));
		$row = $rows->count() + 1;
		$key = $prefix . $row;
		return $key;
	}
}