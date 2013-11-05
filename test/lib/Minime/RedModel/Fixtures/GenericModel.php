<?php

namespace Minime\RedModel\Fixtures;

/**
 * @entity
 * @table foo
 * @timestamps
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