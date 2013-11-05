<?php

namespace Minime\RedModel;

class ErrorsBag implements \IteratorAggregate
{
	/**
	 * keeper errors
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
		if(is_string($key))
		{
			if(array_key_exists($key, $this->errors))
			{
				return true;
			}
			return false;
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
		if($this->has($key))
		{
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
		if($this->has($key))
		{
			if(is_array($this->errors[$key]))
			{
				$result = $this->errors[$key];
			}
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

}