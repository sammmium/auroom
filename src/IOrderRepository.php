<?php

namespace App;

interface IOrderRepository
{
	public function load($orderId);
}
