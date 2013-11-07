<?php

namespace Minime\RedModel;

use \Minime\RedModel\Fixtures\MultipleDatabases\DatabaseA;
use \Minime\RedModel\Fixtures\MultipleDatabases\DatabaseB;
use \Minime\RedModel\Fixtures\MultipleDatabases\DatabaseDefault;
use \R;

class MultipleDatabasesTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        R::addDatabase('database_a', 'sqlite:/tmp/database_a.sqlite');
        R::addDatabase('database_b', 'sqlite:/tmp/database_b.sqlite');
        R::setup(); // setup default database
        R::setStrictTyping( false );
    }

    public function tearDown()
    {
        R::selectDatabase('database_a');
        R::nuke('database_a');
        R::selectDatabase('database_b');
        R::nuke('database_b');
        R::selectDatabase('default');
        R::nuke('default');
    }

    /**
     * @test
     */
    public function usingMultipleDatabases()
    {
        $this->assertEquals('database_a', DatabaseA::selectDatabase());
        $this->assertEquals('database_b', DatabaseB::selectDatabase());
        $this->assertEquals('default', 	  DatabaseDefault::selectDatabase());

        $ids_db_a = [];
        $ids_db_b = [];
        $ids_db_default = [];

        $i = 3;
        while ($i--) {
            $ids_db_a[] = (new DatabaseA)->save();
            $ids_db_b[] = (new DatabaseB)->save();
            $ids_db_default[] = (new DatabaseDefault)->save();
        }

        $this->assertEquals(3, DatabaseA::writer()->count());
        $this->assertSame(range(1, 3), $ids_db_a);

        $this->assertEquals(3, DatabaseB::writer()->count());
        $this->assertSame(range(1, 3), $ids_db_b);

        $this->assertEquals(3, DatabaseDefault::writer()->count());
        $this->assertSame(range(1, 3), $ids_db_default);
    }

    /**
     * @__test__
     */
    public function syncDatabases()
    {
        $this->fail();
    }

}
