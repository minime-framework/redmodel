<?php
namespace Minime\RedModel\Fixtures;

/**
 * @redmodel
 */
class ValidationFixture extends \Minime\RedModel\Model
{
    /**
     * @redmodel.column
     * @redmodel.validate.string json ["<< not is string"]
     */
    protected $name;

    /**
     * @redmodel.column
     * @redmodel.validate.numeric
     */
    protected $numeric;

    /**
     * @redmodel.column
     */
    protected $data;

}
