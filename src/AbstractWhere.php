<?php

namespace Harp\Query;

use Harp\Query\SQL;
use InvalidArgumentException;
/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
abstract class AbstractWhere extends AbstractOrderLimit
{
    /**
     * @var SQL\Join[]|null
     */
    protected $join;

    /**
     * @var SQL\Condition[]|null
     */
    protected $where;

    /**
     * @return SQL\Join[]|null
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @param SQL\Join[] $join
     */
    public function setJoin(array $join)
    {
        $this->join = $join;

        return $this;
    }

    /**
     * @return AbstractWhere $this
     */
    public function clearJoin()
    {
        $this->join = null;

        return $this;
    }

    /**
     * @return SQL\Condition[]|null
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param SQL\Condition[] $where
     */
    public function setWhere(array $where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @return AbstractWhere $this
     */
    public function clearWhere()
    {
        $this->where = null;

        return $this;
    }

    public function join($table, $condition, $type = null)
    {
        $table = new SQL\Aliased($table);
        $this->join []= new SQL\Join($table, $condition, $type);

        return $this;
    }

    public function joinAliased($table, $alias, $condition, $type = null)
    {
        $table = new SQL\Aliased($table, $alias);
        $this->join []= new SQL\Join($table, $condition, $type);

        return $this;
    }

    public function where($column, $value)
    {
        if (is_array($value)) {
            throw new InvalidArgumentException('Use "whereIn" for array conditions');
        }

        $this->where []= new SQL\ConditionIs($column, $value);

        return $this;
    }

    public function whereLike($column, $value)
    {
        $this->where []= new SQL\ConditionLike($column, $value);

        return $this;
    }

    public function whereIn($column, array $values)
    {
        $this->where []= new SQL\ConditionIn($column, $values);

        return $this;
    }

    public function whereNot($column, $values)
    {
        $this->where []= new SQL\ConditionNot($column, $values);

        return $this;
    }

    public function whereRaw($condition, array $parameters = null)
    {
        $this->where []= new SQL\Condition($condition, $parameters);

        return $this;
    }
}
