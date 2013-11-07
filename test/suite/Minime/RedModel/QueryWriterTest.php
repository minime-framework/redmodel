<?php

namespace Minime\RedModel;

use \R;

class QueryWriterTest extends \PHPUnit_Framework_TestCase
{
    private $writer;

    public function setUp()
    {
        R::setup();
        R::setStrictTyping( false );
        $this->writer = new QueryWriter( 'person' );
    }

    public function tearDown()
    {
        $this->writer = null;
        R::nuke();
    }

    /**
     * @test
     */
    public function findBySQLAndGetSQL()
    {
        foreach (R::dispense( 'person', 3 ) as $bean) {
            R::store($bean);
        };

        $this->assertCount(2, $this->writer->findBySQL( ' select * from person where 1=1 limit 2 offset 1 ' ));
        $this->assertCount(2, $this->writer->findBySQL( ' select * from person where 1=1 limit 2 offset 1 ' ), true);
        $this->assertEquals( 'select * from person where 1=1 limit 2 offset 1', $this->writer->getSQL());
    }

    /**
     * @test
     */
    public function execute()
    {
        foreach (R::dispense( 'person', 3 ) as $bean) {
            R::store($bean);
        };

        $this->assertEquals(3, $this->writer->execute( ' delete from person ' ));
    }

    /**
     * @test
     */
    public function connection()
    {
        foreach (R::dispense( 'person', 3 ) as $bean) {
            R::store($bean);
        };

        $adapter = R::$toolbox->getDatabaseAdapter();
        $this->assertCount(3, $this->writer->connection($adapter)->findBySQL( ' select * from person ' ));
    }

    /**
     * @test
     */
    public function selectAllWithDistinct()
    {
        foreach (R::dispense( 'person' , 3 ) as $bean) {
            $bean->first_name = 'vinicius';
            $bean->last_name  = 'carvalho';
            R::store($bean);
        };

        $this->writer->select();
        $this->assertCount(3, $this->writer->all());

        $this->writer->select( 'first_name, last_name' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->select( 'first_name', 'last_name' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->select( 'count(*)' );
        $this->assertCount(1, $this->writer->all());

        $this->writer->select( 'first_name' )->distinct();
        $this->assertCount(1, $this->writer->all());

        $this->writer->select( 'first_name', 'last_name' )->distinct();
        $this->assertCount(1, $this->writer->all());

        $this->writer->select( 'first_name' )->distinct();
        $this->writer->distinct( false );
        $this->assertCount(3, $this->writer->all());

        $this->writer->select( 'first_name' )->distinct()->distinct( false );
        $this->assertCount(3, $this->writer->all());
    }

    /**
     * @test
     */
    public function where()
    {
        foreach (R::dispense( 'person', 3 ) as $bean) {
            $bean->first_name = 'vinicius';
            $bean->last_name  = 'carvalho';
            R::store($bean);
        };

        $this->writer->where( 'first_name = ?' );
        $this->assertCount(0, $this->writer->all());

        $this->writer->where( 'first_name = "vinicius"' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->where( 'first_name = ?', 'vinicius' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->where( 'first_name = ? AND last_name = ?', 'vinicius', 'carvalho' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->where( 'first_name = ?' )->put([ 'vinicius' ]);
        $this->assertCount(3, $this->writer->all());

        $this->writer->where( 'first_name = ? AND last_name = ?' )->put( 'vinicius', 'carvalho' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->where( 'first_name = ? AND last_name = ?' )->put( 'vinicius' )->put( 'carvalho' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->where([ 'first_name' => 'vinicius', 'last_name' => 'carvalho'])->where( false );
        $this->assertCount(3, $this->writer->all());

        $this->writer->where([ 'last_name' => 'carvalho' ]);
        $this->assertCount(3, $this->writer->all());

        $this->writer->where([ 'id' => [1, 3] ]);
        $this->assertCount(2, $this->writer->all());
    }

    /**
     * @test
     */
    public function finders()
    {
        foreach (R::dispense( 'person', 10 ) as $bean) {
            $bean->first_name = 'vinicius';
            $bean->last_name  = 'carvalho';
            R::store($bean);
        };

        $this->assertCount(1, $this->writer->find( 10 ));
        $this->assertCount(2, $this->writer->find( 1, 10 ));
        $this->assertCount(2, $this->writer->find([ 1, 10 ]));

        $this->assertCount(10, $this->writer->findBy( 'first_name', 'vinicius' ));
        $this->assertCount(10, $this->writer->findBy([ 'first_name' => 'vinicius', 'last_name' => 'carvalho' ]));

        $this->assertTrue($this->writer->exists([ 'first_name' => 'vinicius', 'last_name' => 'carvalho' ]));
    }

    /**
     * @test
     */
    public function notConditions()
    {
        foreach (R::dispense( 'person', 3) as $bean) {
            $bean->first_name = 'vinicius';
            R::store($bean);
        };

        $bean = R::dispense( 'person');
        $bean->first_name = 'julia';
        R::store($bean);

        $this->writer->not()->where([ 'first_name' => 'vinicius' ]);
        $this->assertCount(1, $this->writer->all());

        $this->writer->not()->where([ 'id' => [1, 3] ]);
        $this->assertCount(2, $this->writer->all());

        $this->assertCount(3, $this->writer->not()->find( 3 ));
        $this->assertCount(2, $this->writer->not()->find( 1, 3 ));
        $this->assertCount(2, $this->writer->not()->find([ 1, 3 ]));
        $this->assertCount(1, $this->writer->not()->findBy([ 'first_name' => 'vinicius' ]));
        $this->assertCount(1, $this->writer->not()->findBy( 'first_name', 'vinicius' ));
    }

    /**
     * @test
     */
    public function firstAndLast()
    {
        list($p1,$p2,$p3) = R::dispense( 'person', 3 );
        $p1->first_name = 'vinicius';
        R::store($p1);

        $p2->first_name = 'julia';
        R::store($p2);

        $p3->first_name = 'alana';
        R::store($p3);

        $this->assertEquals( 'vinicius', $this->writer->first()[ 'first_name' ] );
        $this->assertCount(2, $this->writer->select()->first(2));

        $this->assertEquals( 'alana', $this->writer->last()[ 'first_name' ] );
        $this->assertCount(2, $this->writer->select()->last(2));

        $this->assertEquals( 'julia', $this->writer->where( 'id > 1 and id < 3' )->limit(1)->order( 'id asc' )->first()[ 'first_name' ] );
    }

    /**
     * @test
     */
    public function calculations()
    {
        $i = 18;
        foreach (R::dispense( 'person', 3 ) as $bean) {
            $bean->first_name = 'vinicius';
            $bean->age = $i++;
            R::store($bean);
        };

        $this->assertEquals(2.0, $this->writer->average('id'));
        $this->assertEquals(18, $this->writer->minimum('age'));
        $this->assertEquals(20, $this->writer->maximum('age'));
        $this->assertEquals(6, $this->writer->sum('id'));

        $this->assertEquals(3, $this->writer->count());
        $this->assertEquals(0, $this->writer->where(['first_name' => 'carvalho'])->count());
    }

    /**
     * @test
     */
    public function LimitAndOffset()
    {
        foreach (R::dispense( 'person', 3 ) as $bean) {
            R::store($bean);
        };
        $this->writer->limit(2);
        $this->assertCount(2, $this->writer->all());

        $this->writer->limit(1)->offset(1);
        $this->assertCount(1, $this->writer->all());
    }

    /**
     * @test
     */
    public function ordination()
    {
        foreach (R::dispense( 'person', 3 ) as $bean) {
            $bean->first_name = 'vinicius';
            $bean->last_name  = 'carvalho';
            R::store($bean);
        };

        $this->writer->order( 'first_name' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( 'first_name DESC' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( 'first_name ASC' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( 'first_name ASC, last_name DESC' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( 'first_name ASC', 'last_name DESC' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( 'first_name ASC', 'last_name DESC' )->order( false );
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( ' ? ASC ' )->put([ 'first_name' ]);
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( ' ? ASC ', ' ? DESC ' )->put( 'first_name', 'last_name' );
        $this->assertCount(3, $this->writer->all());

        $this->writer->order( '? ASC, ? DESC' )->put( 'first_name' )->put( 'last_name' );
        $this->assertCount(3, $this->writer->all());
    }

    /**
     * @test
     */
    public function groupingAndCondition()
    {
        foreach (R::dispense( 'person', 5 ) as $bean) {
            $bean->created_at = '2013-02-19';
            $bean->price  = 10;
            R::store($bean);
        }

        $this->assertCount(1, $this->writer->select( ' date(created_at) as ordered_date, sum(price) as total_price ' )->group( ' date(created_at) ' )->all());
        $this->assertCount(1, $this->writer->select( ' date(created_at) as ordered_date, sum(price) as total_price ' )->group( ' date(created_at) ' )->having( ' sum(price) = 50 ')->all());
        $this->assertCount(1, $this->writer->select( ' date(created_at) as ordered_date, sum(price) as total_price ' )->group( ' date(created_at) ' )->having( ' sum(price) = ? ', 50 )->all());
    }

    /**
     * @test
     */
    public function joining()
    {
        $user            = R::dispense('user');
        $address         = R::dispense('address');
        $address->user   = $user;
        R::store($address);

        $this->writer = (new QueryWriter( ' user ' ))->joins( ' left outer join address on address.user_id = user.id ' );
        $this->assertCount(1, $this->writer->all());
        $this->assertEquals( 'select user.* from user left outer join address on address.user_id = user.id', $this->writer->getSQL() );

        $this->writer = (new QueryWriter( ' user ' ))->joins( ' address ' );
        $this->assertCount(1, $this->writer->all());
        $this->assertEquals( 'select user.* from user inner join address on address.user_id = user.id', $this->writer->getSQL());

        $contact         = R::dispense('contact');
        $contact->user   = $user;
        R::store($contact);

        $this->writer = (new QueryWriter( ' user ' ))->joins( ' address ', ' contact ' );
        $this->assertCount(1, $this->writer->all());
        $this->assertEquals( 'select user.* from user inner join address on address.user_id = user.id inner join contact on contact.user_id = user.id', $this->writer->getSQL() );

        $employee     = R::dispense('employee');
        $employee->user = $user;

        $department   = R::dispense('department');
        $employee->department = $department;
        R::store($employee);

        $this->writer = (new QueryWriter( ' user ' ))->joins( [' employee ' => ' department '] );
        $this->assertCount(1, $this->writer->all());
        $this->assertEquals( 'select user.* from user inner join employee on employee.user_id = user.id inner join department on employee.department_id = department.id', $this->writer->getSQL() );

        $this->writer = (new QueryWriter( ' user ' ))->joins( ' address ', [' employee ' => ' department '] );
        $this->assertCount(1, $this->writer->all());
        $this->assertEquals( 'select user.* from user inner join address on address.user_id = user.id inner join employee on employee.user_id = user.id inner join department on employee.department_id = department.id', $this->writer->getSQL() );

        // TODO:
        // Post->joins('category', 'comments')
        # => SELECT posts.* FROM posts
        # => INNER JOIN categories ON posts.category_id = categories.id
        # => INNER JOIN comments ON comments.post_id = posts.id

        // TODO:
        // $this->writer = (new QueryWriter( ' person ' ))->joins( [ ' user ' => [[' employee ' => ' department '], ' address ']] );
        # => SELECT person.* FROM person
        # => INNER JOIN user ON user.person_id = person.id
        # => INNER JOIN employee ON employee.user_id = user.id
        # => INNER JOIN department ON employee.department_id = department.id
        # => INNER JOIN address ON address.user_id = user.id
    }
}
