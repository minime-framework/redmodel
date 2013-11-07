<?php

namespace Minime\RedModel;

use \Minime\RedModel\Fixtures\Associations\Book;
use \Minime\RedModel\Fixtures\Associations\Author;
use \Minime\RedModel\Fixtures\Associations\Page;
use \Minime\RedModel\Fixtures\Associations\UnrelatedModel;
use \R;

class AssociateManyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        R::setup();
        R::setStrictTyping( false );
        // R::log('/tmp/redbean.sql');
        // R::debug();
    }

    public function tearDown()
    {
        R::nuke();
    }

    /**
     * @test
     */
    public function associateMany()
    {
        $book = new Book;

        $page1 = new Page;
        $page2 = new Page;
        $page3 = new Page;

        $book_id = $book->associations()->associateMany([$page1, $page2])->save();
        $this->assertCount(2, R::load('book', $book_id)->ownPage);

        $book->associations()->associateMany([$page3])->save();
        $this->assertCount(3, R::load('book', $book_id)->ownPage);
    }

    /**
     * @test
     * @depends associateMany
     */
    public function retrieveMany()
    {
        $book = new Book;

        $page1 = new Page;
        $page2 = new Page;
        $page3 = new Page;

        $book->associations()->associateMany([$page1, $page2, $page3])->save();
        $this->assertCount(3, $book->associations()->retrieveMany('Page'));
        $this->assertCount(3, $book->associations()->retrieveMany('\Minime\RedModel\Fixtures\Associations\Page'));
    }

    /**
     * @test
     * @depends associateMany
     * @depends retrieveMany
     */
    public function unassociateMany()
    {
        $book = new Book;

        $page1 = new Page;
        $page2 = new Page;

        $book->associations()->associateMany([$page1, $page2])->save();

        $book->associations()->unassociateMany([$page1])->save();
        $this->assertCount(1, $book->associations()->retrieveMany('Page'));
        $this->assertCount(1, $book->associations()->retrieveMany('\Minime\RedModel\Fixtures\Associations\Page'));

        $book->associations()->unassociateMany([$page2])->save();
        $this->assertCount(0, $book->associations()->retrieveMany('Page'));
        $this->assertCount(0, $book->associations()->retrieveMany('\Minime\RedModel\Fixtures\Associations\Page'));
    }

    /**
     * @test
     * @expectedException \Minime\RedModel\InvalidAssociationException
     */
    public function invalidHasManyAssociation()
    {
        $book = new Book();
        $unrelated = new UnrelatedModel();
        $book->associations()->associateMany([$unrelated]);
    }

    /**
     * @test
     * @expectedException \Minime\RedModel\InvalidAssociationException
     */
    public function invalidHasManyAssociationRetrieval()
    {
        $book = new Book();
        $book->associations()->retrieveMany('UnrelatedModel');
    }
}