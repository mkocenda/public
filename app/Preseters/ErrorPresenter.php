<?php

namespace App;

use Nette;
use Tracy\ILogger;
use Nette\Http\Response;


class ErrorPresenter extends Nette\Application\UI\Presenter
{
	/** @var ILogger */
	private $logger;

	/** @var Nette\DI\Container @inject */
	public $container;



	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @param  \Exception
	 * @return void
	 */
	public function renderDefault($exception)
	{
	}

}
