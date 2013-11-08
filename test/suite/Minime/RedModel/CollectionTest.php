<?php
namespace Minime\RedModel;

use \Minime\RedModel\Fixtures\GenericModel;
use \R;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

  private $Collection;

  public function setUp()
  {
    R::setup();
    R::setStrictTyping( false );
    $this->Collection = new Collection($this->data());
  }

  public function tearDown()
  {
    R::selectDatabase('default');
    R::nuke();
  }

  /**
   * @test
   * @expectedException \PHPUnit_Framework_Error
   */
  public function collectionInvalidArgument()
  {
    $models = [new \stdClass()];
    new Collection($models);
  }

  /**
   * @test
   */
  public function each()
  {
    $collection = new Collection($this->data());

    $collection->each(function ($m) {
      $m->name('newname');
    });

    foreach ($collection as $coll) {
      $this->assertEquals($coll->name(), 'newname');
    }
  }

  /**
   * @test
   */
  public function filter()
  {

    $alpha = (new GenericModel());
    $alpha->name('jonh');

    $beta  = (new GenericModel());
    $beta->name('jonh');

    $gama  = (new GenericModel());
    $gama->name('jax');

    $collection = (new Collection([$alpha, $beta, $gama]))->filter(function ($model) {
      if ($model->name() == 'jonh') {
        return $model;
      }
    });
    $models = $collection->export();
    $this->assertCount(2, $collection);
    $this->assertSame($alpha, $models[0]);
  }

  /**
   * @test
   * @expectedException PHPUnit_Framework_Error
   */
  public function pushException()
  {
    $this->Collection->push(null);
    $this->Collection->push();
  }

  /**
   * @test
   */
  public function push()
  {
    $models = $this->data();
    $collection = new Collection($models);
    $collection->push($models[0]);
    $this->assertCount(4, $collection);
  }

  /**
   * @test
   */
  public function reverse()
  {
    $this->Collection->reverse();
    $this->assertEquals(array_reverse($this->data()), $this->Collection->export());
  }

  /**
   * @test
   */
  public function export()
  {
    $collection = new Collection([]);
    $this->assertCount(0, $collection->export());
    $this->assertCount(3, $this->Collection);
    $this->assertCount(3, $this->Collection->export());
  }

  /**
   * @test
   */
  public function isCountable()
  {
    $this->assertCount(3, $this->Collection);
    $this->assertCount(count($this->Collection), $this->Collection->export());
  }

  /**
  * @test
  */
  public function isJsonSerializable()
  {
    $this->assertSame(json_encode($this->Collection->export()), json_encode($this->Collection));
  }

  /**
   * @test
   */
  public function isTraversable()
  {
    $data = $this->data();
    foreach ($this->Collection as $k => $model) {
      $this->assertEquals($data[$k]->name(), $model->name());
    }
  }

  private function data()
  {
    $x = new GenericModel;
    $x->name('name-1');

    $y = new GenericModel;
    $y->name('name-2');

    $z = new GenericModel;
    $z->name('name-3');

    return [$x, $y, $z];
  }
}
