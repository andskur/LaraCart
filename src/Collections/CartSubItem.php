<?php

namespace Andskur\LaraCart\Collections;

use Illuminate\Support\Collection;

class CartSubItem extends Collection
{
	public $fullPrice;
	function __construct($item)
	{
		parent::__construct($item);
		$fullPrice = $this->get('qnt') * $this->get('price');
		$this->put('fullPrice', $fullPrice);
	}
}