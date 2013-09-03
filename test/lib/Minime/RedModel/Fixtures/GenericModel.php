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
	 * @column @unique @null
	 * @type
	 * @default <value>
	 * @validation.regex /regexp/
	 * @validation.max-length <integer>
	 * @validation.min-length <integer>
	 * @validation.max <integer>
	 * @validation.min <integer>
	 * @validation.
	 */
		protected $name;

}