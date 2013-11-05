<?php

namespace Minime\RedModel\Fixtures;

/**
 * @redmodel
 * @redmodel.table foo
 * @redmodel.timestamps
 */
class GenericModel extends \Minime\RedModel\Model
{

	/**
	 * @redmodel.column @unique @null
	 * @type
	 * @default <value>	 
	 */
		protected $name;

}