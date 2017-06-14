<?php

namespace Andskur\LaraCart\Collections;

use Illuminate\Support\Collection;

/**
 * SubItem collection class
 */
class CartSubItem extends Collection
{
	function __construct($item)
	{
		parent::__construct($item);
		$fullPrice = $this->get('qnt') * $this->get('price');
		$this->put('fullPrice', $fullPrice);
	}
}