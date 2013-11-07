<?php

namespace Minime\RedModel;

class ErrorsBagTest extends \PHPUnit_Framework_TestCase
{

    protected $Bag;

    public function setUp()
    {
        $attributes = [
            'column1' => ['min' 	=> 'String the text', 'max'=>'String the text'],
            'column2' => ['between' => 'String the text', 'float'=>'String the text' ],
            'column3' => ['length' 	=> 'String the text', 'lowercase' => 'String the text'],
            'column4' => ['max' 	=> 'String the text'],
            'column5' => ['int' 	=> 'String the text'],
            'column6' => ['notEmpty'=> 'String the text'],
            'column7' => ['numeric' => 'String the text'],
            'column8' => ['odd' => 'String the text'],
            'column9' => ['multiple'=> null ],
            'column10' =>['negative'=> false],
            'column11' =>['consonant'=> ''],
            'column12' =>'',
            'column13' =>['alnum'],
            'column14' =>['digit'=>'String the text']
        ];
        $this->Bag = new ErrorsBag($attributes);
    }

    /**
     * @test
     * @expectedException \PHPUnit_Framework_Error
     */
    public function constructAcceptsOnlyArrays()
    {
        new ErrorsBag('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function hasIsInstanceInvalidArgumentException()
    {
        $this->Bag->has(null);
        $this->Bag->has(false);
        $this->Bag->has(true);
    }

    /**
     * @test
     */
    public function get()
    {
        # is null
        $this->assertNull($this->Bag->get(''));
        # is array
        $this->assertInternalType('array', $this->Bag->get('column3'));
        # is string
        $this->assertInternalType('string', $this->Bag->get('column12'));
    }

    /**
     * @test
     */
    public function find()
    {
        # instanceoff
        $this->assertInstanceOf('IteratorAggregate', $this->Bag->find('column4'));
        $this->assertInstanceOf('IteratorAggregate', $this->Bag->find(''));
        # null
        $this->assertNull($this->Bag->find('')->get(''));
        # string
        $this->assertInternalType('string', $this->Bag->find('column2')->get('between'));
    }

    /**
     * @test
     */
    public function has()
    {
        # True
        $this->assertTrue($this->Bag->has('column11'));
        $this->assertTrue($this->Bag->has('column12'));
        # False
        $this->assertFalse($this->Bag->has('column15'));
        $this->assertFalse($this->Bag->has(''));
        $this->assertFalse($this->Bag->has(' '));
    }

    /**
     * @test
     */
    public function export()
    {
        $this->assertInternalType('array', $this->Bag->export());
        $this->assertCount(14, $this->Bag->export());
    }
}
