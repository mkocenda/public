<?php

	require __DIR__ . "/../vendor/autoload.php";

	$configurator = new Nette\Configurator;

	$tracyConfigNeon = __DIR__ . "/config/config.local.neon";
	$tracyCacheDir = __DIR__ . "/../temp/cache/";
	$tracyTempDir = $tracyCacheDir . "tracy/";
	$tracyCookieKey = \Nette\Configurator::COOKIE_SECRET;
	$tracyGetKey = "tracy";

	if (isset($_COOKIE[$tracyCookieKey]) && file_exists($tracyTempDir . $_COOKIE[$tracyCookieKey])) {
		$isDebugMode = true;
	}

	if (isset($_GET[$tracyGetKey])) {
		if ($_GET[$tracyGetKey] == "on") {
			$tracySecret = md5($_SERVER["REMOTE_ADDR"] . rand(1000, 9999) . $_SERVER["SERVER_NAME"]);
			if (!isset($_COOKIE[$tracyCookieKey]) || !file_exists($tracyTempDir . $_COOKIE[$tracyCookieKey])) {
				if (file_exists($tracyConfigNeon)) {
					$source = file_get_contents($tracyConfigNeon);
					$config = \Nette\Neon\Neon::decode($source);
					if (isset($config["parameters"]["tracy"])) {
						$allowedIPs = @file_get_contents($config["parameters"]["tracy"]);
						$allowedIPs = explode("\n", trim(str_replace("\r", "", $allowedIPs)));
						if (in_array($_SERVER["REMOTE_ADDR"], $allowedIPs)) {
							if (!file_exists($tracyCacheDir)) {
								mkdir($tracyCacheDir);
							}
							if (!file_exists($tracyTempDir)) {
								mkdir($tracyTempDir);
							}
							file_put_contents($tracyTempDir . $tracySecret, "");
							setcookie($tracyCookieKey, $tracySecret, strtotime("1 years"), "/", "", "", true);
							$isDebugMode = true;
						}
					}
				}
			}
		} elseif (isset($_COOKIE[$tracyCookieKey])) {
			setcookie($tracyCookieKey, '', 1, '/', '', '', true);
		}
	}

	$configurator->setDebugMode(isset($isDebugMode) ? true : "localhost");
	$configurator->enableDebugger(__DIR__ . "/../log");

	$configurator->setTempDirectory(__DIR__ . "/../temp");

	$configurator->createRobotLoader()
		->addDirectory(__DIR__)
		->register();

	$environment = Nette\Configurator::detectDebugMode() ? "development" : "production";

	$configurator->addConfig(__DIR__ . "/config/config.neon");
	$configurator->addConfig(__DIR__ . "/config/config.local.neon");

	$container = $configurator->createContainer();
	$container->parameters["environment"] = $environment;

return $container;
