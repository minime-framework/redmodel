<?php

namespace Minime\RedModel;

use Minime\Annotations\Load as Meta;
use Minime\Annotations\Traits\Reader;
use R;
use \InvalidArgumentException;

class Model
{
	use Reader;

	private $bean;
	private $class_meta;
	private $fields_meta;

	public function __construct($id = null, \RedBean_OODBBean $bean = null)
	{
		$this->class_meta = $this->getClassAnnotations($this);
		
		self::selectDatabase();
		
		if($id)
		{
			$this->bean = R::load(self::entity(), $id);
		}
		else if($bean)
		{
			$this->bean = $bean;
		}
		else
		{
			$this->bean = R::dispense( self::entity() );
		}

		return $this;
	}

	public function __set($property, $value)
	{
		try
		{			
			$property_meta = $this->getPropertyAnnotations($property);
			if($property_meta->has('column'))
			{
				$this->bean->$property = $value;
			}
		}
		catch(\ReflectionException $e)
		{
			$class = get_called_class();
			$entity = self::entity();
			$message = "Column \"{$property}\" not declared for Model {$class} managing \"{$entity}\" entity";
			throw new InvalidArgumentException($message);
		}
		return true;
	}

	public function __get($property)
	{
		return $this->bean->$property;
	}

	public function export()
	{
		return $this->bean->export();
	}

	public function pluck()
	{
		$values = [];
		foreach(func_get_args() as $property)
		{
			$values[] = $this->$property;
		}
	}

	/**
	 * @todo Lógica universal para checagem de valores únicos
	 */
	private function checkUniqueConstraints()
	{
		return true;
		// if($this->class_meta->has('unique-constrainst'))
		// {
		// 	$conditions = [];
		// 	$values = [];
		// 	$constraints = $this->class_meta->get('unique-constrainst');
		// 	foreach ($constraints as $constraint) {
		// 		foreach ($constraint as $field) {
		// 			if($this->$field)
		// 			{					
		// 				$values[] = $this->$field;
		// 				$conditions[] = " {$field} = ? ";
		// 			}
		// 		}
		// 	}
		// 	$beans = R::findOne(self::entity(), implode(" AND ", $conditions), $values);
		// 	if(count($beans))
		// 	{
		// 		return false;
		// 	}
		// 	return true;
		// }
	}

	private function check()
	{
		if(!$this->checkUniqueConstraints()) return false;
		return true;
	}

	public function save()
	{
		if($this->check())
		{
			return R::store($this->bean);
		}
		return false;
	}
	
	public function delete()
	{
		if($this->bean->id)
		{
			R::trash($this->bean);
			if(!$this->bean->id)
			{
				return true;
			}
			return false;
		}
	}

	/**
	 * Load all rows from table
	 * @return array of Minime\RedModel\Model
	 */
	public static function all()
	{
		self::selectDatabase();
		$class = get_called_class();
		$table = self::entity();
		$beans = [];
		foreach(R::findAll($table) as $bean)
		{
			$beans[] = new $class(null, $bean);
		}
		return $beans;
	}
	
	public static function count()
	{
		self::selectDatabase();
		return R::count( self::entity() );
	}

	public static function wipe()
	{
		self::selectDatabase();
		return R::wipe( self::entity() );
	}

	public static function entity()
	{
		$annotations = Meta::fromClass(get_called_class());

		if($annotations->has('table'))
		{
			return $annotations->get('table');
		}
		else
		{
			$full_class_name = get_called_class();
			$segments = explode('\\', $full_class_name);
			$short_class_name = end($segments);
			return self::tableize($short_class_name);
		}
	}

	public static function RESET()
	{
		self::selectDatabase();
		R::nuke();			
	}

	public static function selectDatabase()
	{
		$db_name = 'default';
		$annotations = Meta::fromClass(get_called_class());
		if($annotations->has('db'))
		{
			$db_name = $annotations->get('db');
		}
		R::selectDatabase($db_name);
		return $db_name;
	}

	/**
     * Converts a word into the format for a Red Model table name. Converts 'ModelName' to 'model_name'.
     *
     * @param string $word The word to tableize.
     *
     * @return string The tableized word.
     */
    private static function tableize($word)
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word));
    }

    /**
     * Converts a word into the format for a Red Model class name. Converts 'table_name' to 'TableName'.
     *
     * @param string $word The word to classify.
     *
     * @return string The classified word.
     */
    private static function classify($word)
    {
        return str_replace(" ", "", ucwords(strtr($word, "_-", "  ")));
    }
}
