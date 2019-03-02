<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// PresentersLocator extension:
//$configurator->defaultExtensions['decorator'] = Nette\DI\Extensions\DecoratorExtension::class;
//$configurator->defaultExtensions['inject'] = Nette\DI\Extensions\InjectExtension::class;

$configurator->setDebugMode([
	'secretCookie147@192.168.1.10',
	'secretCookie147@188.75.144.92',
	'secretCookie147@176.74.128.122'
]);
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');


$container = $configurator->createContainer();

//setcookie('nette-debug', 'secretCookie147', strtotime('1 years'), '/', '', '', TRUE); // DANGER! beware to run on production
//setcookie('nette-debug', 'secretCookie147', 1, '/', '', '', TRUE); // FOR COOKIE UNSET

return $container;
