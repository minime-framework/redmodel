<?php

namespace Minime\RedModel;

class ValidationManagerTest extends \PHPUnit_Framework_TestCase
{

    protected $ValidationManager;

    public function setUp()
    {
        $this->ValidationManager = new ValidationManager;
    }

    /**
     * @test
     */
    public function notMessage()
    {
        $rules = ['int'=> ['not string']];
        $this->ValidationManager->setRules($rules);
        $this->assertFalse($this->ValidationManager->isValid('string'));
        $this->assertCount(1, $this->ValidationManager->getErrors());
    }

    /**
     * @test
     */
    public function isValidTrue()
    {
        $rules = [
            45 => [
                'max' 	=> [50, '<< {{name}} not interge'],
                'min' 	=> [10, '<< {{name}} not min']
            ],
            12.2 => [
                'float' => ['<< {{name}} not float'],
                'numeric'=>['<< {{name}} not interge']
            ],
            'string text' => [
                'string' => []
            ]
        ];
        foreach ($rules as $input => $rule) {
            $this->ValidationManager->setRules($rule);
            $this->assertTrue($this->ValidationManager->isValid($input));
        }
    }

    /**
     * @test
     * @dataProvider provedorValidateFalse
     */
    public function isValidFalse($value, $rules)
    {
        $this->ValidationManager->setRules($rules);
        $this->assertFalse($this->ValidationManager->isValid($value));
    }

    /**
     * @test
     * @expectedException \PHPUnit_Framework_Error
     * @dataProvider provedorRules
     */
    public function setRules($rules)
    {
        $validator = $this->ValidationManager;
        $validator->setRules($rules);
    }

    /**
     * @test
     * @dataProvider provedorValidateFalse
     */
    public function getErrors($value, $rules)
    {
        $this->ValidationManager->setRules($rules);
        $this->ValidationManager->isValid($value);
        $errors = $this->ValidationManager->getErrors();
        # count
        $this->assertCount(1, $errors);
        $this->assertInternalType('array', $errors);
    }

    /**
     * @test
     * @expectedException \Respect\Validation\Exceptions\ComponentException
     */
    public function invalidMethod()
    {
        $rules = ['method'	=>	['<< Is string'] ];
        $this->ValidationManager->setRules($rules);
        $this->ValidationManager->isValid('value1');
    }

    public function provedorRules()
    {
        return [ [null], [''], [false] ];
    }

    public function provedorValidateFalse()
    {
        return [
            [
                null, [
                    'string'=> ['<< {{name}} not string']
                ]
            ],[
                '2013', [
                    'between' => [1988, 1990, '<< {{name}} not between']
                ]
            ],[
                '', [
                    'notEmpty' => ['<< {{name}} is empty']
                ]
            ]
        ];
    }

}
