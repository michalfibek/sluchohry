<?php

namespace Test;

use Nette,
	App,
	App\Model\Score,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';


class ScoreTest extends Tester\TestCase
{
	private $container;

	/** @var Score */
	private $scoreModel;


	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
//		$dbConnection = $this->container->getService('nette.database.default');
//		$db = new Nette\Database\Context($dbConnection);
//		$this->scoreModel = new Score($db);
		$this->scoreModel = $this->container->getByType('App\Model\Score');
	}

	function setUp()
	{

	}

	protected function tearDown()
	{
		echo '\r\n';
	}

	public function getGameEndArgs()
	{
		return array(
			array(
				array(
					'gameName' => 'pexeso',
					'difficulty' => 1,
					'songList' => '1,5,20,3,8,5',
					'time' => 20*1000,
					'steps' => 18,
				),
			),
			array(
				array(
					'gameName' => 'pexeso',
					'difficulty' => 1,
					'songList' => '1,5,20,3,8,5',
					'time' => 30*1000,
					'steps' => 28
				)
			)
		);
	}

	/**
	 * @dataProvider getGameEndArgs
	 */
	function testProcessGameEndResult($result)
	{
		$result = $this->scoreModel->processGameEndResult(1, $result);

		print_r($result);

//		Assert::type('array', $result);

//		Assert::contains(array('score', 'personalRecord', 'gameRecord'), $score);
//		Assert::contains('score', $score);
//		Assert::contains('personalRecord', $score);
//		Assert::contains('gameRecord', $score);

	}

}


$test = new ScoreTest($container);
$test->run();
