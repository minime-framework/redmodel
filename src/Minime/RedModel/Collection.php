<?php
namespace Minime\RedModel;

class Collection implements  \IteratorAggregate, \Countable, \JsonSerializable
{
  protected $models = [];

  public function __construct(array $models)
  {
    $this->validateInstancesOrFail($models);
    $this->models = $models;
  }

  /**
   * Invokes the given callback on every element in the collection.
   * @param  callable
   * @return self
   */
  public function each(callable $callback)
  {
    array_map([$callback, '__invoke'], $this->models);

    return $this;
  }

  /**
   * Applies a filter in the current collection and returns a new collection.
   * @param  callable
   * @return self
   */
  public function filter(callable $callback)
  {
    $collection = new self([]);    
    foreach ($this as $model) {
      $response = $callback->__invoke($model);
      if ($response) {
        $collection->push($response);
      }
    }

    return $collection;
  }

  /**
   * Append one model in the collection
   * @param  Minime\RedModel\Model
   * @return self
   */
  public function push(Model $model)
  {
    array_push($this->models, $model);

    return $this;
  }

  /**
   * Reverse collection
   * @return self
   */
  public function reverse()
  {
    $this->models = array_reverse($this->models);

    return $this;
  }

  /**
   * Export collection from array
   * @return array Minime\RedModel\Model
   */
  public function export()
  {
    return $this->models;
  }

  /**
   * IteratorAggregate
   */
  public function getIterator()
  {
    return new \ArrayIterator($this->models);
  }

  /**
   * Countable
   */
  public function count()
  {
    return count($this->models);
  }

  /**
   * JsonSerializable
   */
  public function jsonSerialize()
  {
    return $this->export();
  }

  /**
   * Valid instance
   * @param  array  Minime\RedModel\Model
   */
  private function validateInstancesOrFail(array $models)
  {
    array_map(function (Model $model) {}, $models);
  }
}
