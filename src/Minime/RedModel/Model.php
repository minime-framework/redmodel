<?php

namespace Minime\RedModel;

use Minime\Annotations\Facade as Meta;
use Minime\Annotations\Traits\Reader as AnnotationsReader;
use R;

class Model
{
	use AnnotationsReader;

	/**
	 * Bean
	 * 
	 * @var \RedBean_OODBBean
	 */
	private $bean;

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

	/**
	 * Export all bean properties to associative array.
	 * 
	 * @return array Associative array: `["property" => "values"]`
	 */
	public function export()
	{
		return $this->bean->export();
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
				if($this->getPropertyAnnotations($property)->has('column'))
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
	 * Export bean properties to JSON.
	 * 
	 * @return string JSON
	 */
	public function exportJSON()
	{
		return json_encode($this->export());
	}

	/**
	 * Unbox bean
	 * @return \RedBean_OODBBean
	 */
	public function unboxBean()
	{
		return $this->bean;
	}

	/**
	 * @todo Add database agnostic logic to check unique constrainsts of values
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

	private function validate()
	{
		if(!$this->checkUniqueConstraints()) return false;
		return true;
	}

	/**
	 * Save to database.
	 * 
	 * @return integer Primary key of saved row
	 */
	public function save()
	{
		if($this->validate())
		{
			$annotations = $this->getClassAnnotations($this);
			if($annotations->has('timestamps'))
			{
				$this->updateTimestamps();
			}
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
	 * Load all rows from table.
	 * 
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
	
	/**
	 * Count rows from table.
	 * 
	 * @return integer
	 */
	public static function count()
	{
		self::selectDatabase();
		return R::count( self::entity() );
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
		$annotations = Meta::getClassAnnotations(get_called_class());

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
		$annotations = Meta::getClassAnnotations(get_called_class());
		if($annotations->has('db'))
		{
			$db_name = $annotations->get('db');
		}
		R::selectDatabase($db_name);
		return $db_name;
	}

	/**
	 * Update the "created_at" and "update_at" fields with their respective timestamps.
	 *
	 * @return void
	 */
	protected function updateTimestamps()
	{
		$time = $this->freshTimestamp();

		if($this->bean->getMeta('tainted'))
		{
			if(!$this->bean->id)
			{
				$this->bean->created_at = $time;		
			}
			$this->bean->updated_at = $time;
		}
	}

	/**
	 * Get a fresh timestamp for the model.
	 *
	 * @return \DateTime
	 */
	public function freshTimestamp()
	{
		return new \DateTime;
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
     * @todo  Associate many description
     * 
     * @param  array $models
     * @return self
     */
	public function associateMany($models)
	{
		$manager = new AssociationManager($this);
		$manager->relateOneToMany($models);
		return $this;
	}

	/**
	 * @todo  Unassociate many description
	 * 
	 * @param  array $models
	 * @return self
	 */
	public function unassociateMany($models)
	{
		$manager = new AssociationManager($this);
		$manager->unrelateOneToMany($models);
		return $this;
	}

	/**
	 * @todo  Retrieve many description
	 * 
	 * @param  [type] $model [description]
	 * @return array
	 */
	public function retrieveMany($model)
	{
		$manager = new AssociationManager($this);
		return $manager->getOneToMany($model);
	}
}
