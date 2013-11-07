<?php

namespace Minime\RedModel\Fixtures;

/**
 * @redmodel
 */
class QueryModel extends \Minime\RedModel\Model
{
    /**
     * @column
     */
    protected $name;

    /**
     * @column
     */
    protected $count;
}
