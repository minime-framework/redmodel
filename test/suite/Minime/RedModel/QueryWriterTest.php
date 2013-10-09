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
    public function calculated()
    {
        $i = 1;
        foreach(R::dispense("query_model", 3) as $bean){
            $bean->name = "vinicius";
            $bean->count = $i++;
            R::store($bean);
        };
        $this->assertCount(1, $this->writer->select("distinct name")->all());
        $this->assertCount(1, $this->writer->select("max(count)")->all());
        $this->assertCount(1, $this->writer->select("min(count)")->all());
        $this->assertCount(1, $this->writer->select("sum(id)")->all());
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
        $this->assertCount(3, $this->writer->select("count(id), id")->group("id")->all());
    }

    /**
     * @test
     */
    public function firstAndTwoFirst()
    {
        $i = 1;
        foreach(R::dispense("query_model", 3) as $bean){
            $bean->name = $i++;
            R::store($bean);
        };
        $this->assertEquals(1, $this->writer->select()->first()->name());
        $this->assertCount(2, $this->writer->select()->first(2));
    }

    /**
     * @test
     */
    public function lastAndTwoLast()
    {
        $i = 1;
        foreach(R::dispense("query_model", 3) as $bean){
            $bean->name = $i++;
            R::store($bean);
        };
        $this->assertEquals(3, $this->writer->select()->last()->name());
        $this->assertCount(2, $this->writer->select()->last(2));
    }

    /**
     * @test
     */
    public function withPut()
    {
        foreach(R::dispense("query_model", 3) as $bean){
            $bean->name = "vinicius";
            R::store($bean);
        };
        $this->assertCount(3, $this->writer->select()->where("name = ?")->put("vinicius")->all());
        $this->assertCount(1, $this->writer->select("count(?)")->group("?")->put("id", "id")->all());
    }

    /**
     * @test
     */
    public function whereWithoutPut()
    {
        foreach(R::dispense("query_model", 3) as $bean){
            $bean->name = "vinicius";
            R::store($bean);
        };
        $this->assertCount(3, $this->writer->select()->where("name = ?", "vinicius")->all());
    }

    /**
     * @test
     */
    public function whereWithoutValue()
    {
        foreach(R::dispense("query_model", 3) as $bean){
            $bean->name = "vinicius";
            R::store($bean);
        };
        $this->writer->select()->where("name = ?")->all();
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
            $bean->name = "vinicius";
            R::store($bean);
        };
        $this->assertCount(2, $this->writer->select()->where("name = ?", "vinicius")->with(["id" => [1, 3]])->all());
    }

    /**
     * @test
     */
    public function whereWithCount()
    {
        foreach(R::dispense("query_model", 3) as $bean){
            $bean->name = "vinicius";
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

    /**
     * @test
     */
    public function findBySQL()
    {
        foreach(R::dispense("query_model", 3) as $bean){
            R::store($bean);
        };
        $this->assertCount(2, $this->writer->findBySQL( " select * from query_model where 1=1 limit 2 offset 1 " ));
    }

    /**
     * @test
     */
    public function execute()
    {
        foreach(R::dispense("query_model", 3) as $bean){
            R::store($bean);
        };
        $this->assertEquals(3, $this->writer->execute( " delete from query_model " ));
    }
}
