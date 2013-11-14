<?php

namespace Minime\RedModel;

use \Minime\RedModel\Fixtures\ValidationFixture;
use \R;

class ValidationModelTeste extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    R::setup();
    R::setStrictTyping( false );
  }

  public function tearDown()
  {
    R::selectDatabase('default');
    R::nuke();
  }

  /**
   * @test
   */
  public function getErrors()
  {
    $fixture = new ValidationFixture();
    $fixture->cpf(null);
    $fixture->between(null);
    $fixture->save();
    
    $this->assertInstanceOf('Minime\RedModel\ErrorsBag', $fixture->getErrors());
    $this->assertCount(2, $fixture->getErrors());
  }

  /**
   * @test
   */
  public function validateFieldsSuccess()
  {
    $fixture = new ValidationFixture();
    $fixture->cpf(68217476446);
    $fixture->between('bar');
    $this->assertTrue((TRUE == $fixture->save()));
  }

  /**
   * @test
   */
  public function validateFieldsFail()
  {
    $fixture = new ValidationFixture();
    $fixture->cpf(68217476415);
    $fixture->between('bar');
    $this->assertFalse($fixture->save());
    $this->assertCount(1, $fixture->getErrors());
  }
}
