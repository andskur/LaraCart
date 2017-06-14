<?php

namespace Andskur\LaraCart\Storage;

interface StorageContract
{
	/**
	 * Get all items from storage
	 * @return array
	 */
	public function all();

	/**
	 * Store item to storage
	 * @param  array 	$item 	array of item values
	 * @return array 			stored item
	 */
	public function store($item);

	/**
	 * Get item from storage
	 * @param  string $key storage key
	 * @return array      item
	 */
	public function get($key);

	/**
	 * Increase item quanity
	 * @param  int 	$id  Row id
	 * @param  int 	$qnt increase on quanity
	 * @return int
	 */
	public function incr($id, $qnt);

	/**
	 * Delete item from storage
	 * @param  string 	$key 	key for remove
	 * @return boolean
	 */
	public function delete($key);

	/**
	 * Add sub item to item and store it to storage
	 * @param array		$subItem 	subitem to added
	 * @return array
	 */
	public function addSubItem ($subItem);
}