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
     * @redmodel.column    
     */
        protected $name;

}
