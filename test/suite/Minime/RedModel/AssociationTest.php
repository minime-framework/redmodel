<?php

namespace Minime\RedModel;

use \Minime\RedModel\Fixtures\Associations\Book;
use \Minime\RedModel\Fixtures\Associations\Author;
use \Minime\RedModel\Fixtures\Associations\Page;
use \Minime\RedModel\Fixtures\Associations\UnrelatedModel;
use \R;

class AssociationTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		R::setup();
		R::setStrictTyping( false );
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

		$book->associateMany([$page1, $page2])->save();
		$this->assertCount(2, $book->retrieveMany('Page'));

		$book->associateMany([$page3])->save();
		$this->assertCount(3, $book->retrieveMany('Page'));
	}

	/**
	 * @test
	 * @depends associateMany
	 */
	public function unassociateMany()
	{
		$book = new Book;

		$page1 = new Page;
		$page2 = new Page;

		$book->associateMany([$page1, $page2])->save();

		$book->unassociateMany([$page1])->save();
		$this->assertCount(1, $book->retrieveMany('Page'));

		$book->unassociateMany([$page2])->save();
		$this->assertCount(0, $book->retrieveMany('Page'));
	}

	/**
	 * @_test_
	 * @expectedException \Minime\RedModel\InvalidAssociationException
	 */
	public function invalidHasManyAssociation()
	{
		$a = new Book();
		$z = new UnrelatedModel();
		$a->associateMany($z);
	}
}