<?php

namespace Minime\RedModel;

use R;

class QueryWriter
{
    private $adapter;
    private $sqlHelper;

    private $distinct     = false;
    private $where        = false;
    private $order        = false;
    private $deny         = false;

    private $limit        = false;
    private $offset       = false;
    private $limitValue   = 1;
    private $offsetValue  = 1;

    private $grouping     = [];
    private $group        = false;

    private $joining      = [];
    private $join         = false;

    private $having       = [];
    private $have         = false;

    private $attributes   = [];
    private $conditions   = [];
    private $ordination   = [];

    private $table;

    public function __construct( $table )
    {
        if( !isset($table) )
        {
            throw new \InvalidArgumentException( 'Undefined table name..' );
        }
        else
        {
            $this->table = trim($table);
        }
        $this->sqlHelper = R::$f;
        $toolbox         = R::$toolbox;
        $this->adapter   = $toolbox->getDatabaseAdapter();

        return $this;
    }

    public function connection( $adapter = null )
    {
        (!isset($adapter)) ? $this->adapter = $adapter : null;
        return $this;
    }

    public function getSQL()
    {
        return strtolower( trim($this->adapter->getSQL()) );
    }

############################################# FinderMethods

    private function beginCapture()
    {
        $this->sqlHelper->begin();
    }

    private function assignTable()
    {
        $this->sqlHelper->from( $this->table );
    }

    private function bodySelectClause()
    {
        if( count($this->attributes) > 0 )
        {
            if($this->distinct)
            {
                $this->sqlHelper->select( ' DISTINCT ' . join($this->attributes, ',') );
                $this->distinct = false;
            }
            else
            {
                $this->sqlHelper->select(join($this->attributes, ','));
            }
        }
        else
        {
            $this->sqlHelper->select( $this->table . '.*' );
        }
    }

    private function whererWithArrayValues( $conditions )
    {
        foreach ($conditions as $key => $value)
        {
            if(empty($key))
            {
                throw new \InvalidArgumentException( 'Attribute is empty.' );
            }
            if(is_array($value))
            {
                if(count($value) < 1)
                {
                    throw new \InvalidArgumentException( 'Values is empty.' );
                }
                if($this->deny)
                {
                    $this->sqlHelper->addSQL( " AND $key NOT IN " );
                }
                else
                {
                    $this->sqlHelper->addSQL( " AND $key IN " );
                }
                $this->sqlHelper->open()->addSQL(R::genSlots($value))->close();
                $this->put($value);
            }
            else
            {
                if($this->deny)
                {
                    $this->sqlHelper->addSQL( " AND $key <> ? " );
                }
                else
                {
                    $this->sqlHelper->addSQL( " AND $key = ? " );
                }

                $this->put([$value]);
            }

        }
    }

    private function conditionsQuery()
    {
        if( $this->where )
        {
            if( is_array($this->conditions[0]) )
            {
                $this->sqlHelper->where( '1=1' );
                $this->whererWithArrayValues( $this->conditions[0] );
            }
            else
            {
                $this->sqlHelper->where( array_shift($this->conditions) );
                $this->put( $this->conditions );
            }
            $this->where = false;
            $this->deny  = false;
        }
    }

    private function ordenationQuery()
    {
        if( $this->order )
        {
            $this->sqlHelper->addSQL( ' ORDER BY ' . join(',', $this->ordination));
            $this->order = false;
        }
    }

    private function limitationQuery()
    {
        if( $this->limit )
        {
            if( $this->adapter->getDatabase()->getDatabaseType() == 'oracle' )
            {
                $this->where( " ROWNUM <= $this->limitValue " );
            }
            else
            {
                $this->sqlHelper->addSQL( " LIMIT $this->limitValue " );
            }
            $this->limit = false;
        }
    }

    private function offsetQuery()
    {
        if( $this->offset )
        {
            if( $this->adapter->getDatabase()->getDatabaseType() == 'oracle' )
            {
                throw new \InvalidArgumentException( 'Offset not implemented for oracle' );
            }
            else
            {
                $this->sqlHelper->addSQL( " OFFSET $this->offsetValue " );
            }
            $this->offset = false;
        }
    }

    private function groupingQuery()
    {
        if( $this->group )
        {
            $this->sqlHelper->addSQL( ' GROUP BY ' . join(',', $this->grouping) );
            $this->group = false;
        }
    }

    private function havingQuery()
    {
        if( $this->have )
        {
            $this->sqlHelper->addSQL( ' HAVING ' . array_shift($this->having) );
            $this->put( $this->having );
            $this->have = false;
        }
    }

    public function joining()
    {
        if($this->join)
        {
            $lsql = [];

            if ( !is_array( $this->joining[0] ) && preg_match( '/^\s*(INNER|LEFT|RIGTH|CROSS|FULL|JOIN)\s+/i', $this->joining[0] ) )
            {
                $lsql[] = trim( $this->joining[0] );
            }
            else
            {
                foreach ($this->joining as $value) {
                    if(is_array($value))
                    {
                        while ( list($key, $val) = each($value) )
                        {
                            $destTable = trim( $key );

                            if(is_int($val))
                            {
                                $lsql[] = "INNER JOIN {$destTable} ON {$destTable}.{$this->table}_id = {$val}";
                            }
                            else
                            {
                                $targetTable = trim( $val );
                                $lsql[] = "INNER JOIN {$destTable} ON {$destTable}.{$this->table}_id = {$this->table}.id";
                                $lsql[] = "INNER JOIN {$targetTable} ON {$destTable}.{$targetTable}_id = {$targetTable}.id";
                            }
                        }
                    }
                    else
                    {
                        $destTable = trim( $value );
                        $lsql[]    = "INNER JOIN {$destTable} ON {$destTable}.{$this->table}_id = {$this->table}.id";
                    }
                }
            }
            $this->sqlHelper->addSQL( join(' ',$lsql) );
            $this->join = false;
        }
    }

    /**
     * @param string $retrieval One of these 'cell', 'row', 'col' or 'all'.
     *
     * @return mixed $result
     */
    public function all( $what = '' )
    {
        $this->beginCapture();
        $this->bodySelectClause();
        $this->assignTable();
        $this->joining();
        $this->conditionsQuery();
        $this->groupingQuery();
        $this->havingQuery();
        $this->ordenationQuery();
        $this->limitationQuery();
        $this->offsetQuery();

        return $this->sqlHelper->get( $what );
    }

    public function first( $limit = 1 )
    {
        return $this->limit( $limit )->all()[0];
    }

    public function last( $limit = 1 )
    {
        return $this->order( ' id DESC ' )->first( $limit );
    }

    public function exists( $hash )
    {
        return (count($this->findBy( $hash )) > 0);
    }

    public function find()
    {

        $value_ids = func_get_args();
        (is_array($value_ids[0])) ? $ids = $value_ids[0] : $ids = $value_ids;

        return $this->where(["id" => $ids])->all();

    }

    public function findBy()
    {

        $values = func_get_args();
        if(is_array($values[0]))
        {
            return $this->where($values[0])->all();
        }
        else
        {
            if($this->deny)
            {
                return $this->where($values[0] . ' <> ?', $values[1])->all();
            }
            else
            {
                return $this->where($values[0] . ' = ?', $values[1])->all();
            }
        }

    }

    public function findBySQL( $sql, $toBean = false )
    {

        if($toBean)
        {
            return R::convertToBeans( $this->adapter->get( $sql ) );
        }
        else
        {
            return $this->adapter->get( $sql );
        }

    }

    public function execute( $sql )
    {
        return $this->adapter->exec( $sql );
    }

############################################# QueryMethods

    public function put()
    {

        $args = func_get_args();
        (is_array($args[0])) ? $values = $args[0] : $values = $args;

        foreach ($values as $value) {
            $this->sqlHelper->put($value);
        }

        return $this;

    }

    public function select()
    {

        $this->attributes = func_get_args();
        return $this;

    }

    public function distinct( $value = true )
    {

        $this->distinct = $value;
        return $this;

    }

    public function not()
    {

        $this->deny = true;
        return $this;

    }

    public function where( $value = true )
    {

        $this->conditions = func_get_args();
        $this->where      = $value;

        return $this;

    }

    public function joins( $value = true )
    {

        $this->joining = func_get_args();
        $this->join = $value;

        return $this;

    }

    public function group( $value = true )
    {
        $this->grouping = func_get_args();
        $this->group    = $value;

        return $this;
    }

    public function having( $value = true )
    {
        $this->having = func_get_args();
        $this->have   = $value;

        return $this;
    }

    public function order( $value = true )
    {

        $this->ordination = func_get_args();
        $this->order      = $value;

        return $this;

    }

    public function limit( $limit = 1 )
    {

        $this->limitValue = $limit;
        $this->limit      = true;

        return $this;

    }

    public function offset( $limit = 1 )
    {

        $this->offsetValue = $limit;
        $this->offset      = true;

        return $this;

    }

############################################# Calculations

    public function count()
    {
        return $this->select( ' COUNT(*) AS count_all ' )->first()['count_all'];
    }

    public function minimum( $value = "id" )
    {
        return $this->select( " MIN($value) AS min_val " )->first()['min_val'];
    }

    public function maximum( $value = "id" )
    {
        return $this->select( " MAX($value) AS max_val " )->first()['max_val'];
    }

    public function sum( $value = "id" )
    {
        return $this->select( " SUM($value) AS sum_val " )->first()['sum_val'];
    }

    public function average( $value = "id" )
    {
        return $this->select( " AVG($value) AS avg_val " )->first()['avg_val'];
    }
}
