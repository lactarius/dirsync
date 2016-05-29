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

	/** @var string */
	private $path;


	public function setUp()
	{
		$this->ds = new DirSync();
	}

	public function tearDown()
	{
		if ( $this->path ) {
			rmdir( $this->path );
			$this->path = NULL;
		}
	}

	public function testSetRootDirAbs()
	{
		$this->ds->setRootDir( '/home/pb/testds1' );
		$d = $this->ds->getRootDir();

		Assert::same( '/home/pb/testds1', $d );

		$this->path = $d;
	}

	public function testSetRootDirAlt1()
	{
		$this->ds->setRootDir( '~/testds2' );
		$d = $this->ds->getRootDir();

		Assert::same( '/home/pb/testds2', $d );

		$this->path = $d;
	}

	public function testSetRootDirAlt2()
	{
		$this->ds->setRootDir( './testds3' );
		$d = $this->ds->getRootDir();

		Assert::same( '/home/pb/virt/dirsync/tests/testds3', $d );

		$this->path = $d;
	}

	public function testSetJsonInput()
	{
		$j = '{ "__root__": "~/testds1", "src": null }';
		$this->ds->setJsonInput( $j );
		$d = $this->ds->getRootDir();
		$a = $this->ds->getAssocData();

		Assert::null( $a[ 'src' ] );
		Assert::same( '/home/pb/testds1', $d );

		$this->path = $d;
	}

	public function testFromFile()
	{
		$this->ds->fromFile( '~/virt/dirsync/res/struct.json' );
		$d = $this->ds->getRootDir();
		$a = $this->ds->getAssocData();

		Assert::false( $a[ 'vendor' ] );
		Assert::same( '/home/pb/testds1', $d );
	}

}

$test = new DirSyncTest;
$test->run();
