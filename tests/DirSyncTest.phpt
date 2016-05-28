<?php

use DirSync\DirSync;
use Tester\Assert;
use Tester\TestCase;


require __DIR__ . '/bootstrap.php';


/**
 * Test Class DirSyncTest
 *
 * @author Petr Blazicek 2016
 */
class DirSyncTest extends TestCase
{

	/** @var DirSync */
	private $ds;


	public function __construct()
	{
		$this->ds = new DirSync();
	}

	public function testSetRootDir()
	{
		$this->ds->setRootDir( '/home/pb/virt/karlin' );
		$d = $this->ds->getRootDir();

		Assert::same( '/home/pb/virt/karlin', $d );
	}

}

$test = new DirSyncTest;
$test->run();
