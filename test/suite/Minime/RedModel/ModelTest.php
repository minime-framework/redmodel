<?php

namespace Minime\RedModel;

use \Minime\RedModel\Fixtures\GenericModel;
use \Minime\RedModel\Fixtures\TableFromModelClassName;
use \R;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        R::setup();
        R::setStrictTyping( false );
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
        $this->assertEquals('foo', GenericModel::entity());
        $this->assertEquals('table_from_model_class_name', TableFromModelClassName::entity());
    }

    /**
     * @test
     * @depends entity
     */
    public function counting()
    {
        $i = 3;
        while ($i--) {
            R::store( R::dispense( GenericModel::entity() ) );
        }
        $this->assertEquals(3, R::count(GenericModel::entity()));
    }

    /**
     * @test
     * @depends counting
     */
    public function saving()
    {
        $ids = [];
        $i = 3;
        while ($i--) {
            $ids[] = (new GenericModel)->save();
        }
        $this->assertEquals(3, R::count(GenericModel::entity()));
        $this->assertSame(range(1, 3), $ids);
    }

    /**
     * @test
     * @depends saving
     */
    public function timestamps()
    {
        $foo = new GenericModel;
        $foo->save();
        $this->assertEquals($foo->created_at(), $foo->updated_at());

        $foo->save();
        $this->assertEquals($foo->created_at(), $foo->updated_at());

        sleep(1);

        $foo->name("bar");
        $foo->save();
        $this->assertNotEquals($foo->created_at(), $foo->updated_at());
    }

    /**
     * @test
     * @depends saving
     */
    public function deleting()
    {
        $i = 3;
        while ($i--) {
            (new GenericModel)->save();
        }
        $this->assertSame(TRUE, (new GenericModel(3))->delete());
        $this->assertSame(FALSE, (new GenericModel(3))->delete());
        $this->assertEquals(2, R::count(GenericModel::entity()));

        $this->assertSame(TRUE, (new GenericModel(2))->delete());
        $this->assertSame(FALSE, (new GenericModel(2))->delete());
        $this->assertEquals(1, R::count(GenericModel::entity()));

        $this->assertSame(FALSE, (new GenericModel())->delete());
    }

    /**
     * @test
     * @depends saving
     */
    public function setDefinedColumn()
    {

        $foo = new GenericModel();
        $foo->name('bar');
        $this->assertTrue(in_array('name', array_keys($foo->export())));
        $foo->save();
        $this->assertTrue(in_array('name', array_keys($foo->export())));

        $retrieved_foo = new GenericModel(1);
        $this->assertTrue(in_array('name', array_keys($retrieved_foo->export())));

    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function setUndefinedCollumnFails()
    {
        $retrieved_foo = new GenericModel();
        $retrieved_foo->undefined_column('error');
    }

    /**
     * @test
     */
    public function modelIsJsonSerializable()
    {
        $foo = new GenericModel();
        $foo->name('bar');
        $this->assertEquals(json_encode(['id' => 0,'name'=>'bar']), json_encode($foo));
    }

    /**
     * @test
     */
    public function getColumns()
    {
        $this->assertSame(['name'], (new GenericModel())->getColumns());
    }

    /**
     * @test
     * @depends saving
     */
    public function wipe()
    {
        $i = 3;
        while ($i--) {
            (new GenericModel())->save();
        }
        GenericModel::truncate();
        $this->assertEquals(0, R::count(GenericModel::entity()));
    }

    /**
     * @test
     */
    public function transactions()
    {
        R::freeze(true);
    }

    /**
     * @__test__
     * @depends setDefinedColumn
     */
    public function buildUniqueConstraints()
    {
        $i = 2;
        while ($i--) {
            $foo = new GenericModel();
            $foo->name("marcio");
            $foo->save();
        }
        $this->assertEquals(1, R::count(GenericModel::entity()));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function incompatileBeanShouldRaiseException()
    {
        new GenericModel(null, R::dispense('wrong_bean_type'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function invalidIdShouldRaiseException()
    {
        new GenericModel([]);
    }
}
