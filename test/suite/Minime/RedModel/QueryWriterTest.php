<?php

namespace Minime\RedModel;

use \R;

class QueryWriterTest extends \PHPUnit_Framework_TestCase
{

	private $writer;

	public function setUp()
	{
		R::setup();
		R::setStrictTyping( false );
		$this->writer = new QueryWriter("\Minime\RedModel\Fixtures\QueryModel");
	}

	public function tearDown()
	{
		$this->writer = null;
		R::nuke();
	}

	/**
	 * @test
	 */
	public function all()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(3, $this->writer->select()->all());
	}

	/**
	 * @test
	 */
	public function order()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(3, $this->writer->select()->order("id ASC")->all());
	}

	/**
	 * @test
	 */
	public function limit()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(1, $this->writer->select()->limit(1)->all());
	}

	/**
	 * @test
	 */
	public function group()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(3, $this->writer->select("COUNT(id), id")->group("id")->all());
	}

	/**
	 * @test
	 */
	public function first()
	{
		$i = 1;
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = $i++;
			R::store($bean);
		};
		$this->assertEquals(1, $this->writer->select()->first()->column1());
	}

	/**
	 * @test
	 */
	public function twoFirst()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(2, $this->writer->select()->first(2));
	}

	/**
	 * @test
	 */
	public function last()
	{
		$i = 1;
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = $i++;
			R::store($bean);
		};
		$this->assertEquals(3, $this->writer->select()->last()->column1());
	}

	/**
	 * @test
	 */
	public function twoLast()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(2, $this->writer->select()->last(2));
	}

	/**
	 * @test
	 */
	public function whereWithPut()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = "x";
			R::store($bean);
		};
		$this->assertCount(3, $this->writer->select()->where("column1 = ?")->put("x")->all());
	}

	/**
	 * @test
	 */
	public function whereWithoutPut()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = "x";
			R::store($bean);
		};
		$this->assertCount(3, $this->writer->select()->where("column1 = ?", "x")->all());
	}

	/**
	 * @test
	 */
	public function whereWithoutValue()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = "x";
			R::store($bean);
		};
		$this->writer->select()->where("column1 = ?")->all();
	}

	/**
	 * @test
	 */
	public function whereAsIn()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(2, $this->writer->select()->with(["id" => [1, 3]])->all());
	}

	/**
	 * @test
	 */
	public function whereWithIn()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = "x";
			R::store($bean);
		};
		$this->assertCount(2, $this->writer->select()->where("column1 = ?", "x")->with(["id" => [1, 3]])->all());
	}

	/**
	 * @test
	 */
	public function whereWithCount()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = "x";
			R::store($bean);
		};
		$this->assertEquals(2, $this->writer->select()->with(["id" => [1, 3]])->count());
	}

	/**
	 * @test
	 */
	public function countAll()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertEquals(3, $this->writer->select()->count());
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function attributeIsEmpty()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->writer->select()->with(["" => [1, 3]]);
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function valuesIsEmpty()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->writer->select()->with(["id" => []]);
	}
}
