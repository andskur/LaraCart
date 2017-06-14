<?php

namespace Andskur\LaraCart\Collections;

use Illuminate\Support\Collection;

/**
 * Item collection class
 */
class CartItem extends Collection
{
	function __construct($item)
	{
		parent::__construct($item);
		$fullPrice = $this->get('qnt') * $this->get('price');
		$this->put('fullPrice', $fullPrice);
	}

	/**
	 * Format collection to/from storage
	 * @return array
	 */
	public function storage ()
	{
		$row = $this;
		if ($row->count() == null)
			return 'error';
		$row->each(function ($item, $key) use ($row) {
			if ($key == 'discount') {
				$row->put($key, $this->discount($item));
			}
		});
		return $row->toArray();
	}

	/**
	 * encode/decode discount object
	 * @param  json 	$discount 	discount object
	 * @return json
	 */
	protected function discount ($discount)
	{
		switch (gettype($discount)) {
			case 'string':
				$discount = (array) json_decode($discount);
				break;
			case 'array':
				$discount = json_encode($discount);
				break;
		}
		return $discount;
	}
}