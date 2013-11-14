<?php

namespace Minime\RedModel;

class ErrorsBag implements \IteratorAggregate, \Countable
{
  /**
   * Errors
   * @var array
   */
  private $errors = [];

  public function __construct(array $errors)
  {
    $this->errors = $errors;
  }

  /**
   * Export errors
   * @return array
   */
  public function export()
  {
    return $this->errors;
  }

  /**
   * Has
   * @param  string
   * @return boolean
   */
  public function has($key)
  {
    if (is_string($key)) {
      return array_key_exists($key, $this->errors);
    }
    throw new \InvalidArgumentException('Key must be a string');
  }

  /**
   * get
   * @param  string
   * @return string
   */
  public function get($key)
  {
    if ($this->has($key)) {
      return $this->errors[$key];
    }

    return null;
  }

  /**
   * find
   * @param  string
   * @return object this
   */
  public function find($key)
  {
    $result = [];
    if (is_array($this->get($key))) {
      $result = $this->get($key);
    }

    return new self($result);
  }

  /**
   * getIterator
   * @return object \ArrayIterator
   */
  public function getIterator()
  {
      return new \ArrayIterator($this->errors);
  }

  /**
   * Countable
   * @return integer
   */
  public function count()
  {
      return count($this->errors);
  }

}
