<?php

namespace Minime\RedModel;

use \R;

class ModelTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		R::setup();
		R::setStrictTyping( false );
		// R::log(__DIR__ . '/../../../build/logs/timeline.sql');
	}

	public function tearDown()
	{
		R::selectDatabase('default');
		R::nuke();
	}

	/**
	 * @test
	 */
	public function entity()
	{
		$this->assertEquals('foo', FooClass::entity());
		$this->assertEquals('foo_table_from_class_name', FooTableFromClassName::entity());
	}

	/**
	 * @test
	 * @depends entity
	 */
	public function counting()
	{
		$i = 3;
		while($i--)
		{
			R::store( R::dispense( FooClass::entity() ) );
		}
		$this->assertEquals(3, FooClass::count());
	}

	/**
	 * @test
	 * @depends counting
	 */
	public function saving()
	{
		$ids = [];
		$i = 10;
		while($i--)
		{
			$ids[] = (new FooClass)->save();
		}
		$this->assertEquals(10, FooClass::count());
		$this->assertSame(range(1, 10), $ids);
	}

	/**
	 * @test
	 * @depends saving
	 */
	public function usingMultipleDatabases()
	{
		R::addDatabase('test', 'sqlite:/tmp/test.sqlite');

		$this->assertEquals('default', FooClass::selectDatabase());
		$this->assertEquals('test', FooAlternativeDatabase::selectDatabase());
		$ids_db1 = [];
		$ids_db2 = [];
		$i = 10;
		while($i--)
		{
			$ids_db1[] = (new FooClass)->save();
			$ids_db2[] = (new FooAlternativeDatabase)->save();
		}
		$this->assertEquals(10, FooClass::count());
		$this->assertSame(range(1, 10), $ids_db1);

		$this->assertEquals(10, FooAlternativeDatabase::count());
		$this->assertSame(range(1, 10), $ids_db2);

		FooAlternativeDatabase::RESET();
	}

	/**
	 * @test
	 * @depends saving
	 */
	public function deleting()
	{
		$i = 3;
		while($i--)
		{
			R::store( R::dispense( FooClass::entity() ) );
		}
		$this->assertSame(TRUE, (new FooClass(3))->delete());
		$this->assertSame(NULL, (new FooClass(3))->delete());
		$this->assertEquals(2, FooClass::count());

		$this->assertSame(TRUE, (new FooClass(2))->delete());
		$this->assertSame(NULL, (new FooClass(2))->delete());
		$this->assertEquals(1, FooClass::count());

		$this->assertSame(NULL, (new FooClass())->delete());
		$this->assertEquals(1, FooClass::count());
	}

	/**
	 * @test
	 * @depends saving
	 */
	public function setDefinedColumn()
	{

		$foo = new FooClass();
		$foo->name = 'bar';
		$this->assertEquals(['id' => 0,  'name' => 'bar'], $foo->export());
		$foo->save();
		$this->assertEquals(['id' => 1,  'name' => 'bar'], $foo->export());

		$retrieved_foo = new FooClass(1);
		$this->assertEquals(['id' => 1,  'name' => 'bar'], $retrieved_foo->export());
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function setUndefinedCollumnFails()
	{
		$retrieved_foo = new FooClass();
		$retrieved_foo->undefined_column = 'error';
	}

	/**
	 * @test
	 * @depends counting
	 */
	public function all()
	{
		$i = 3;
		while($i--)
		{
			(new FooClass())->save();
		}
		$this->assertEquals(3, count(FooClass::all()));
	}

	/**
	 * @test
	 * @depends saving
	 */
	public function wipe()
	{
		$i = 3;
		while($i--)
		{
			(new FooClass())->save();
		}
		FooClass::wipe();
		$this->assertEquals(0, count(FooClass::all()));
	}

	/**
	 * @test
	 */
	public function transactions()
	{
		R::freeze(true);
	}

	/**
	 * @todo  Ativar teste
	 * @__test__
	 * @depends setDefinedColumn
	 */
	public function buildUniqueConstraints()
	{
		$i = 2;
		while($i--)
		{
			$foo = new FooClass();
			$foo->name = "marcio";
			$foo->save();
		}
		$this->assertEquals(1, count(FooClass::all()));
	}
}

/**
 * @entity
 * @table foo
 * @unique-constrainst [["name"]]
 */
class FooClass extends Model
{

	/**
	 * @column @unique @null
	 * @type
	 * @default <value>
	 * @validation.regex /regexp/
	 * @validation.max-length <integer>
	 * @validation.min-length <integer>
	 * @validation.max <integer>
	 * @validation.min <integer>
	 * @validation.
	 */
		protected $name;

}

/**
 * @entity
 */
class FooTableFromClassName extends Model
{
}

/**
 * @entity
 * @db test
 * @table foo
 */
class FooAlternativeDatabase extends Model
{
}