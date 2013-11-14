<?php

namespace Minime\RedModel;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test   
   */
  public function isValid()
  { 
    $data = [
      # with message
      '232.254.584-81'=> ['cpf' => '{cpf not available}'],
      '2013-11-14'  => ['date' => '{date not valid}'],
      'abc'         => ['regex' => '/[a-z]/'],
      # no message
      'foo bar'     => ['string.notEmpty' => ''],
      # multi validation
      150           => ['numeric.positive.between' => '1, 256']
    ];

    foreach ($data as $value => $rules) {      
      $this->assertTrue((new Validation)->setRules($rules)->isValid($value));
    }
  }

  /**
   * @test   
   */
  public function notAvailable()
  {
    $data = [      
      '232.254.584-04' => ['cpf' => '{cpf not available}'],
      '2013-15-06' => ['date' => ''],
      98 => ['numeric.positive.between' => '100, 256']
    ];

    foreach ($data as $value => $rules) {      
      $this->assertFalse((new Validation)->setRules($rules)->isValid($value));
    }
  }

  /**
   * @test
   * @expectedException \Respect\Validation\Exceptions\ComponentException
   */
  public function InvalidValidation()
  {
    $rule = ['notfound' => '{cpf not available}'];
    (new Validation)->setRules($rule)->isValid('bar');
  }

  /**
   * @test
   * @expectedException PHPUnit_Framework_Error
   */
  public function setRulesInvalidArgument()
  {
    (new Validation)->setRules(null);
  }

  /**
   * @test
   */
  public function getErrors()
  {
    $rule = ['regex' => '/[a-z]/, {foo-bar}'];

    $validation = (new Validation)->setRules($rule);
    $validation->isValid(14);    
    $this->assertCount(1, $validation->getErrors());    

    # egual message
    foreach ($validation->getErrors() as $key => $value) {
      $this->assertEquals($value, 'foo-bar');
    }

    $validation = (new Validation)->setRules($rule);
    $validation->isValid('bar');
    $this->assertCount(0, $validation->getErrors());    
  }
}
