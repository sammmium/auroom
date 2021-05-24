<?php

namespace App;

use App\IOrderRepository;

class MySQLOrderSource implements IOrderRepository
{
	public function load($orderId)
	{
		return 'LOAD FROM MYSQL: ' . $orderId;
	}
}
