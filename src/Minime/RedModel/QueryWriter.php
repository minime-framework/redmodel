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
			$this->writer = R::$f->begin()->select("*")->from($class::entity());
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
		if($items = $this->writer->get())
		{
			foreach($items as $item)
			{
				$bean = R::dispense($class::entity());
				$results[] = new $class(null, $bean->import($item));
			}
		}
		else
		{
			throw new \InvalidArgumentException("Error in query execution.");
		}
		return $results;
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

	private function whereWithIn($args)
	{
		$this->where(" 1=1 ");
		$condition = [];
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
				while (each($values)) {
					$condition[$key][] = "?";
				}
				$this->writer->open()->addSQL(join(", ", $condition[$key]))->close();
				foreach ($values as $id)
				{
					$this->put($id);
				}
			}
		}
	}

	private function whereSimple($values)
	{
		$condition = array_shift($values);
		$this->writer->where($condition);
		foreach ($values  as $value)
		{
			$this->put($value);
		}
	}

	public function where($args)
	{
		if(NULL === $args)
		{
			throw new \InvalidArgumentException("Put all values query");
		}
		if(is_array($args))
		{
			$this->whereWithIn($args);
		}
		else
		{
			if(count($values = func_get_args()) < 2)
			{
				$this->writer->where($values[0]);
			}
			else 
			{
				$this->whereSimple($values);
			}
		}
		$this->hasFilter = true;
		return $this;
	}

	public function put($value)
	{
		if(NULL === $value)
		{
			throw new \InvalidArgumentException("Put expects values of condition");
		}
		$this->writer->put($value);
		return $this;
	}

	public function order($args)
	{
		if(NULL === $args)
		{
			throw new \InvalidArgumentException("Put values for ordenation");
		}
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
		if(NULL === $limit)
		{
			throw new \InvalidArgumentException("Put value limit");
		}
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
		if(NULL === $args)
		{
			throw new \InvalidArgumentException("Put values for ordenation");
		}
		$this->writer->addSQL(" GROUP BY $args ");
		return $this;
	}

	public function having($condition)
	{
		if(NULL === $condition)
		{
			throw new \InvalidArgumentException("Put values of having");
		}
		$this->writer->addSQL(" HAVING $condition ");
		return $this;
	}

	public function select()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}
	
	public function distinct()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}

	public function maximum()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}

	public function minimum()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}
	
	public function sum()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}

	public function joins()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}
	
	public function offset()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}

	public function exists()
	{
		throw new \InvalidArgumentException("awaiting implementation..");
	}
}