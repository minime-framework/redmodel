<?php

namespace Minime\RedModel;

use R;

class QueryWriter
{
	private $class;
	private $writer;
	private $hasFilter = false;

	public function __construct($class)
	{
		if(method_exists($class,"entity"))
		{
			$this->class = $class;
			$this->writer = R::$f;
		}
		else
		{
			throw new \InvalidArgumentException("Undefined method entity() for $class");
		}
		return $this;
	}

	public function all()
	{
		$class   = $this->class;
		$results = [];
		try
		{
			foreach($this->writer->get() as $args)
			{
				$bean = R::dispense($class::entity());
				$results[] = new $class(null, $bean->import($args));
			}
			return $results;
		}
		catch(\RedBean_Exception_SQL $e)
		{
			throw new \InvalidArgumentException("Error in query execution: $e");
		}
	}

	public function select($attrs = "*")
	{
		$class = $this->class;
		$this->writer->begin()->select($attrs)->from($class::entity());
		return $this;
	}

	public function first($limit = 1)
	{
		$result = $this->limit($limit)->all();
		return (count($result) > 1) ? $result : $result[0];
	}

	public function last($limit = 1)
	{
		return $this->order(" id DESC ")->first($limit);
	}

	public function where($args)
	{
		if(count($values = func_get_args()) == 1)
		{
			$this->writer->where($values[0]);
		}
		else 
		{
			$condition = array_shift($values);
			$this->writer->where($condition);
			foreach ($values  as $value)
			{
				$this->put($value);
			}
		}
		$this->hasFilter = true;
		return $this;
	}

	public function put($value)
	{
		foreach (func_get_args() as $key => $value) {
			$this->writer->put($value);
		}
		return $this;
	}

	public function with($args)
	{
		if(!$this->hasFilter)
		{
			$this->where(" 1=1 ");
		}
		foreach ($args as $key => $values)
		{
			if(empty($key))
			{
				throw new \InvalidArgumentException("Attribute is empty.");
			}
			else
			{
				$this->writer->addSQL(" AND $key IN ");
			}
			if(count($values) == 0)
			{
				throw new \InvalidArgumentException("Values is empty.");
			}
			else
			{
				$this->writer->open()->addSQL(R::genSlots($values))->close();
				foreach ($values as $id)
				{
					$this->put($id);
				}
			}
		}
		$this->hasFilter = true;
		return $this;
	}

	public function order($args)
	{
		if($order = func_get_args())
		{
			$this->writer->addSQL(" ORDER BY " . join(", ", $order));
		}
		else
		{
			throw new \InvalidArgumentException("Expecting an value array");
		}
		return $this;
	}

	public function limit($limit)
	{
		#
		# mysql | postgres | sqlite
		# select col from tbl limit 20;
		$this->writer->addSQL(" LIMIT $limit ");
		#
		# Oracle
		# select col from tbl where rownum <= 20;
		// $this->where(" ROWNUM <= $limit ");
		#
		# Microsoft SQL
		# select top 20 col from tbl;
		// $this->select(" TOP $limit ");
		return $this;
	}

	public function offset($limit)
	{
		$this->writer->addSQL(" OFFSET $limit ");
		return $this;
	}

	public function count()
	{
		if($this->hasFilter)
		{
			// maybe use count(*)
			return count($this->writer->get());
		}
		else
		{
			$class = $this->class;
			return R::count( $class::entity() );
		}
	}

	public function group($args)
	{
		$this->writer->addSQL(" GROUP BY $args ");
		return $this;
	}
	
	public function distinct($attr)
	{
		$this->select(" DISTINCT $attr ");
		return $this;
	}

	public function max($attr)
	{
		$this->select(" MAX($attr) ");
		return $this;
	}

	public function min($attr)
	{
		$this->select(" MIN($attr) ");
		return $this;
	}
	
	public function sum($attr)
	{
		$this->select(" SUM($attr) ");
		return $this;
	}
}
