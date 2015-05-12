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
					'songList' => '18,50,53,54',
					'difficulty' => 1,
					'time' => 24000,
					'steps' => 5
				)
			),
			array(
				array(
					'gameName' => 'pexeso',
					'songList' => '17,22,125,30',
					'difficulty' => 2,
					'cubeCount' => '6',
					'time' => 20*1000,
					'steps' => 8
				)
			),
			array(
				array(
					'gameName' => 'pexeso',
					'songList' => '16,17,18,19,20,21,25,32,44,46,52',
					'difficulty' => 3,
					'steps' => 70,
					'time' => 135100,
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
