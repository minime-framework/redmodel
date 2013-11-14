<?php
namespace Minime\RedModel\Fixtures;

/**
 * @redmodel
 */
class ValidationFixture extends \Minime\RedModel\Model
{
  /**
   * @redmodel.column
   * @redmodel.validate.cpf {cpf not available}
   */
  protected $cpf;

  /**
   * @redmodel.column
   * @redmodel.validate.noWhitespace.length 1, 15
   */
  protected $between;

  /**
   * @redmodel.column
   */
  protected $empty;

}
