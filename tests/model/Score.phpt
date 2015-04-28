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

	/** @var Game */
	private $gameModel;


	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
//		$dbConnection = $this->container->getService('nette.database.default');
//		$db = new Nette\Database\Context($dbConnection);
//		$this->scoreModel = new Score($db);
		$this->scoreModel = $this->container->getByType('App\Model\Score');
		$this->gameModel = $this->container->getByType('App\Model\Game');
	}

	function setUp()
	{

	}

	protected function tearDown()
	{
		echo "\n";
	}

	public function getGameEndArgs()
	{
		return array(
			array(
				array(
					'gameName' => 'pexeso',
					'difficulty' => 1,
					'songList' => '17,22,125,30',
					'time' => 1520,
					'steps' => 7
				)
			),
			array(
				array(
					'gameName' => 'melodicCubes',
					'difficulty' => 2,
					'cubeCount' => '6',
					'time' => 20*1000,
					'steps' => 12
				)
			),
			array(
				array(
					'gameName' => 'faders',
					'difficulty' => 3,
					'sliderCount' => 3,
					'time' => 127100,
					'steps' => 25
				)
			),
		);
	}

	/**
	 * @dataProvider getGameEndArgs
	 */
	function testProcessGameEndResult($result)
	{
		$gameId = $this->gameModel->getByName($result['gameName'])->id;
		$result = $this->scoreModel->processGameEndResult(1, $gameId, $result);

		echo $result['score'];

//		Assert::type('array', $result);

//		Assert::contains(array('score', 'personalRecord', 'gameRecord'), $score);
//		Assert::contains('score', $score);
//		Assert::contains('personalRecord', $score);
//		Assert::contains('gameRecord', $score);

	}

}


$test = new ScoreTest($container);
$test->run();
