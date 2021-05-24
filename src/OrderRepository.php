<?php

namespace App;

class OrderRepository
{
	private $source;
	public function setSource(IOrderRepository $source)
	{
		$this->source = $source;
	}
	public function load($orderId)
	{
		return $this->source->load($orderId);
	}
}
