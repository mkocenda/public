<?php

namespace App\Service;

use Nette;

class ConfigService
{

	public $container;

	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	public function readConfig($section = '')
	{
		if (strlen($section) > 0) {
			return $this->container->parameters[$section];
		}
		return $this->container->parameters;
	}
}