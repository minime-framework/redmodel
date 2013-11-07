<?php

namespace Minime\RedModel;

use Minime\Annotations\Facade as Meta;
use Minime\Annotations\Traits\Reader as AnnotationsReader;
use Minime\RedModel\QueryWriter as Writer;
use Minime\RedModel\Behaviors;
use R;

abstract class Model implements \JsonSerializable
{
	use AnnotationsReader;

	/**
	 * Bean
	 * 
	 * @var \RedBean_OODBBean
	 */
	private $bean;

	/**
	 * ErrosBag
	 * 
	 * @var \Minime\RedModel\ErrorsBag
	 */
	private $errors = [];

	public function __construct($id = null, \RedBean_OODBBean $bean = null)
	{	
		$database = self::selectDatabase();
		$entity = self::entity();
		$called = get_called_class();

		if(NULL !== $id)
		{
			if(!is_integer($id))
			{
				throw new \InvalidArgumentException("ID must be a valid integer.");
			}
			$this->bean = R::load(self::entity(), $id);
		}
		else if($bean)
		{
			if($entity !== $bean->getMeta('type'))
			{
				throw new \InvalidArgumentException("Invalid bean for model {{$called}} managing {{$entity}} entity");
			}
			$this->bean = $bean;
		}
		else
		{
			$this->bean = R::dispense($entity);
		}
	}

	/**
	 * Model overload for getters and setters
	 *
	 * @param  string $method
	 * @param  string $arguments
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		if(isset($arguments[0]))
		{
			$value = $arguments[0];
			return $this->set($method, $value);
		}
		else
		{
			return $this->get($method);
		}
	}

	public function set($property, $value)
	{
		$this->hasColumnOrFail($property);
		$this->bean->$property = $value;
	}

	public function get($property)
	{
		return $this->bean->$property;
	}

	private function hasColumn($column)
	{
		try
		{			
			$this->hasColumnOrFail($column);
			return true;
		}
		catch(\ReflectionException $e)
		{
		}
		return false;
	}

	private function hasColumnOrFail($column)
	{
		try
		{
			$annotations = $this->getPropertyAnnotations($column);
		}
		catch(\ReflectionException $e)
		{
			$class = get_called_class();
			$entity = self::entity();
			$message = "Undeclared column \"{$column}\" for {$class} managing \"{$entity}\" entity";
			throw new \InvalidArgumentException($message);
		}
	}

	public function getColumns()
	{
		$columns = [];

		$properties = array_keys(get_object_vars($this));
		foreach($properties as $property)
		{
			try
			{
				if($this->getPropertyAnnotations($property)->has('redmodel.column'))
				{
					$columns[] = $property;
				}
			}
			catch(\ReflectionException $e)
			{

			}
		}

		return $columns;
	}

    /**
     * Export all bean properties to associative array.
     * 
     * @return array Associative array: `["property" => "values"]`
     */
    public function export()
    {
        return $this->bean->export();
    }

    /**
     * JsonSerializable
     */
    public function jsonSerialize() {
        return $this->bean->export();
    }

	public function getErrors()
	{
		return $this->errors;		
	} 

	/**
	 * Save to database.
	 * 
	 * @return integer Primary key of saved row
	 */
	public function save()
	{
		$this->errors = $this->behaviors()->validate();
		if(!$this->errors->count())
		{
			$this->behaviors()->updateTimestamps();
			return R::store($this->bean);
		}
		return false;
	}

	
	/**
	 * Delete from database.
	 * 
	 * @return bool
	 */
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
		return false;
	}

	/**
	 * Wipe entire table and reset primary key sequence (TRUNCATE).
	 * 
	 * @return void
	 */
	public static function truncate()
	{
		self::selectDatabase();
		return R::wipe( self::entity() );
	}

	public static function entity()
	{
		$annotations = Meta::getClassAnnotations(get_called_class())->useNamespace('redmodel');

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

	/**
	 * Remove all entities from database! Use with caution.
	 * 
	 * @return void
	 */
	public static function reset()
	{
		self::selectDatabase();
		R::nuke();
	}

	/**
	 * Switch to annotated dabasase declared in model class
	 * trhoug "@database" annotation: `@database test`
	 *
	 * @return string the current database name
	 */
	public static function selectDatabase()
	{
		$db_name = 'default';
		$annotations = Meta::getClassAnnotations(get_called_class())->useNamespace('redmodel');
		if($annotations->has('db'))
		{
			$db_name = $annotations->get('db');
		}
		R::selectDatabase($db_name);
		return $db_name;
	}

	/**
     * Converts a word into the format for a RedModel table name. Converts 'ModelName' to 'model_name'.
     *
     * @param string $word The word to tableize.
     * @return string The tableized word.
     */
    private static function tableize($word)
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word));
    }

	/**
	 * Exposes bean
	 * @return \RedBean_OODBBean
	 */
	public function bean()
	{
		return $this->bean;
	}

    /**
     * Exposes association manager for current model
     * 
     * @return Minime\RedModel\AssociationManager
     */
	public function associations()
	{
		return new AssociationManager($this);
	}

	/**
	 * Starts query writer for current model
	 * 
	 * @return Minime\RedModel\QueryWriter
	 */
	public static function writer()
	{
		return new Writer(self::entity());
	}

	/**
	 * Internal model behaviors
	 * @return Minime\Redmodel\Behaviors
	 */
	protected function behaviors()
	{
		return new Behaviors($this);
	}
}
