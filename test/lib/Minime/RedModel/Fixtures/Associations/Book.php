<?php

namespace Minime\RedModel\Fixtures\Associations;

/**
 * @rel.has-many Page
 * @rel.belongs-to-many Author
 */
class Book extends \Minime\RedModel\Model
{
}