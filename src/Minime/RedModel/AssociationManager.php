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

	public function relateOneToMany($models)
	{
		if(!is_array($models))
		{
			throw new \InvalidArgumentException("Expected arrays of Minime\RedModel\Model");
		}

		$this->validateAssociationOrFail();

		foreach($models as $model)
		{
			$own = 'own' . ClassName::fromString(get_class($model))->shortName();
			$this->model->unboxBean()->{$own}[] = $model->unboxBean();
		}
	}

	public function unrelateOneToMany($models)
	{
		if(!is_array($models))
		{
			throw new \InvalidArgumentException("Expected arrays of Minime\RedModel\Model");
		}

		$this->validateAssociationOrFail();

		foreach ($models as $model)
		{
			$own = 'own' . ClassName::fromString(get_class($model))->shortName();
			unset($this->model->unboxBean()->{$own}[$model->id()]);
		}
	}

	public function getOneToMany($related_class)
	{
		$this->validateAssociationOrFail();
		
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

	/**
	 * @todo Throw exception in case of undeclared association
	 */
	protected function validateAssociationOrFail()
	{
		// $annotations = $this->model->getClassAnnotations()->grepNamespace('rel');
	}

	private function solveRelatedClass($related_class_name)
	{
		$RelatedClass = ClassName::fromString($related_class_name);

		if(!$RelatedClass->isAbsolute())
		{
			$ModelClassName = ClassName::fromString(get_class($this->model));
			$RelatedClass = $ModelClassName->parent()->join($RelatedClass);
		}

		return $RelatedClass;
	}
}
