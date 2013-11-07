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
    public function reportErrors()
    {
        $fixture = new ValidationFixture();
         $fixture->name(null);
         $fixture->numeric(null);
         $fixture->save();
         $this->assertInstanceOf('Minime\RedModel\ErrorsBag', $fixture->reportErrors());
    }

    /**
     * @test
     */
    public function saveIsValid()
    {
        $fixture = new ValidationFixture();
        $fixture->name('first name');
        $fixture->numeric(rand(1,9));
        $fixture->data(null);
        $this->assertInternalType('int', $fixture->save());
    }
}
