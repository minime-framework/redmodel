<?php

namespace Minime\RedModel;

use Eloquent\Cosmos\ClassName;
use Eloquent\Cosmos\ClassNameResolver;
use R;

class AssociationManager
{
	/**
	 * The model wich model manager will take care of
	 * @var Minime\RedModel\Model
	 */
	protected $model;

	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	public function associateMany($models)
	{
		if(!is_array($models))
		{
			throw new \InvalidArgumentException("Expected arrays of Minime\RedModel\Model");
		}

		$relations = $this->getOwnManyAssociationsMetadata();

		foreach($models as $model)
		{
			$related_class = '\\'.get_class($model);
			$this->validateAssociationOrFail($relations, $related_class);
			$own = 'own' . ClassName::fromString($related_class)->shortName();
			$this->model->unboxBean()->{$own}[] = $model->unboxBean();
		}
		return $this->model;
	}

	public function unassociateMany($models)
	{
		if(!is_array($models))
		{
			throw new \InvalidArgumentException("Expected arrays of Minime\RedModel\Model");
		}

		$relations = $this->getOwnManyAssociationsMetadata();

		foreach ($models as $model)
		{
			$related_class = '\\'.get_class($model);
			$this->validateAssociationOrFail($relations, $related_class);
			$own = 'own' . ClassName::fromString($related_class)->shortName();
			unset($this->model->unboxBean()->{$own}[$model->id()]);
		}
		return $this->model;
	}

	public function retrieveMany($related_class)
	{		
		$this->validateAssociationOrFail(
			$this->getOwnManyAssociationsMetadata(),
			$related_class
		);

		$RelatedClass = $this->solveRelatedClass($related_class);

		$own = 'own' . $RelatedClass->shortName();
		
		$related = $RelatedClass->string();
		$results = [];
		foreach($this->model->unboxBean()->$own as $related_bean)
		{
			$results[] = new $related(null, $related_bean);
		}
		return $results;
	}

	public function relateOneToOne(Model $item)
	{

	}

	public function retrieveOneToOne()
	{

	}

	protected function getOwnManyAssociationsMetadata()
	{
		return $this->model->getClassAnnotations()->grepNamespace('redmodel')->get('own-many');
	}

	/**
	 * @todo Throw exception in case of undeclared association
	 */
	protected function validateAssociationOrFail($annotations, $related_class)
	{
		$RelatedClass = $this->solveRelatedClass($related_class);
		
		foreach($annotations as $declared_class)
		{
			$DeclaredClass = $this->solveRelatedClass($declared_class);

			if($RelatedClass->isRuntimeEquivalentTo($DeclaredClass))
			{
				return true;
			}
		}

		throw new InvalidAssociationException();
	}

	private function solveRelatedClass($related_class_name)
	{
		$RelatedClass = ClassName::fromString($related_class_name);

		if(!$RelatedClass->isAbsolute())
		{
			$RelatedClass = $this->solveRelatedShortNameClass($RelatedClass);
		}

		return $RelatedClass;
	}

	private function solveRelatedShortNameClass(ClassName $RelatedClass)
	{
		$ModelClassName = ClassName::fromString(get_class($this->model));
		return $ModelClassName->parent()->toAbsolute()->join($RelatedClass);
	}
}

