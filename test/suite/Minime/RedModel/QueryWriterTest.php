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
		$this->assertCount(3, $this->writer->all());
	}

	/**
	 * @test
	 */
	public function limit()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(1, $this->writer->limit(1)->all());
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
		$this->assertEquals(1, $this->writer->first()->column1());
	}

	/**
	 * @test
	 */
	public function twoFirst()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(2, $this->writer->first(2));
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
		$this->assertEquals(3, $this->writer->last()->column1());
	}

	/**
	 * @test
	 */
	public function twoLast()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(2, $this->writer->last(2));
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
		$this->assertCount(3, $this->writer->where("column1 = ?")->put("x")->all());
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
		$this->assertCount(3, $this->writer->where("column1 = ?", "x")->all());
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function whereWithoutValue()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			$bean->column1 = "x";
			R::store($bean);
		};
		$this->writer->where("column1 = ?")->all();
	}

	/**
	 * @test
	 */
	public function whereAsIn()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertCount(2, $this->writer->where(["id" => [1, 3]])->all());
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
		$this->assertEquals(2, $this->writer->where(["id" => [1, 3]])->count());
	}

	/**
	 * @test
	 */
	public function countAll()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertEquals(3, $this->writer->count());
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function attributeIsEmpty()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->writer->where(["" => [1, 3]]);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function valuesIsEmpty()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->writer->where(["id" => []]);
	}

	/**
	 * @test
	 */
	public function order()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->assertNotNull($this->writer->order("? ASC")->put("id"));
	}

	/**
	 * @test
	 */
	public function group()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->writer->group("?")->put('id');
	}

	/**
	 * @test
	 */
	public function having()
	{
		foreach(R::dispense("query_model", 3) as $bean){
			R::store($bean);
		};
		$this->writer->having("COUNT(*) > ?")->put(1);
	}
}