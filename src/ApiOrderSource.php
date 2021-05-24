<?php

namespace App;

use App\IOrderRepository;

class ApiOrderSource implements IOrderRepository
{
	public function load($orderId)
	{
		return 'LOAD FROM API: ' . $orderId;
	}
}
