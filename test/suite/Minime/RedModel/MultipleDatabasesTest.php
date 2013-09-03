<?php

namespace Minime\RedModel;

use \Minime\RedModel\Fixtures\DatabaseA;
use \Minime\RedModel\Fixtures\DatabaseB;
use \Minime\RedModel\Fixtures\DatabaseDefault;
use \R;

class MultipleDatabasesTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		R::addDatabase('database_a', 'sqlite:/tmp/database_a.sqlite');
		R::addDatabase('database_b', 'sqlite:/tmp/database_b.sqlite');
		R::setup(); // setup default database
		R::setStrictTyping( false );
	}

	public function tearDown()
	{
		DatabaseA::reset();
		DatabaseB::reset();
		DatabaseDefault::reset();
	}

	/**
	 * @test
	 */
	public function usingMultipleDatabases()
	{
		$this->assertEquals('database_a', DatabaseA::selectDatabase());
		$this->assertEquals('database_b', DatabaseB::selectDatabase());
		$this->assertEquals('default', 	  DatabaseDefault::selectDatabase());

		$ids_db_a = [];
		$ids_db_b = [];
		$ids_db_default = [];

		$i = 3;
		while($i--)
		{
			$ids_db_a[] = (new DatabaseA)->save();
			$ids_db_b[] = (new DatabaseB)->save();
			$ids_db_default[] = (new DatabaseDefault)->save();
		}

		$this->assertEquals(3, DatabaseA::count());
		$this->assertSame(range(1, 3), $ids_db_a);

		$this->assertEquals(3, DatabaseB::count());
		$this->assertSame(range(1, 3), $ids_db_b);

		$this->assertEquals(3, DatabaseDefault::count());
		$this->assertSame(range(1, 3), $ids_db_default);
	}

	/**
	 * @__test__
	 */
	public function syncDatabases()
	{
		$this->fail();
	}

}