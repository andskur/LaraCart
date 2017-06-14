<?php

namespace Andskur\LaraCart\Storage;

interface StorageContract
{
	public function all();

	public function get($key);

	public function store($item);

	public function incr($id, $qnt);

	public function delete($id);
}