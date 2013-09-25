<?php

namespace Minime\RedModel;

use R;

class QueryWriter
{
	private $class;
	private $writer;

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
		foreach($this->writer->get() as $item)
		{
			$bean = R::dispense($class::entity());
			$results[] = new $class(null, $bean->import($item));
		}
		return $results;
	}

	public function first($limit = 1)
	{
		$this->limit($limit);
		$result = $this->all();
		return (count($result) > 1) ? $result : $result[0];
	}

	public function last($limit = 1)
	{
		$this->order(" id DESC ");
		return $this->first($limit);
	}

	private function whereWithIn($values)
	{
		$this->where(" 1=1 ");

		$condition = [];
		foreach ($values as $key => $value) {
			$this->writer->addSQL(" AND ");
			$this->writer->addSQL(" $key IN ");
			foreach ($value as $cond) {
				$condition[$key][] = "?";
			}
			$this->writer->open()->addSQL(join(", ", $condition[$key]))->close();
			foreach ($value as $id)
			{
				$this->put($id);
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
		$class = $this->class;
		return R::count( $class::entity() );
	}
}