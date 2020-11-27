<?php

namespace Common\Tool;

use Phalcon\Http\Request;
use Doctrine\ORM\QueryBuilder;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class Filters implements ArrayAccess, IteratorAggregate
{

    const TYPE_TEXT = 'text';
    const TYPE_DATE = 'date';
    const TYPE_INT = 'int';
    const TYPE_CALLBACK = 'callback';
    const TYPE_HIDDEN = 'hidden';

    const COMP_EQ = '=';
    const COMP_GT = '>';
    const COMP_LT = '<';
    const COMP_GTE = '>=';
    const COMP_LTE = '<=';
    const COMP_STARTS_WITH = 'starts_with';
    const COMP_ENDS_WITH = 'ends_with';
    const COMP_CONTAINS = 'contains';

    /**
     * @var Request 
     */
    private $request;

    private $fields = [];

    private $searches = [];

    private $exactSearches = [];

    public function __construct(Request $request) 
    {
        $this->request = $request;
    }

    public function addField($key, $type = self::TYPE_TEXT, $field = null, $compOp = self::COMP_EQ)
    {   
        $this->fields[$key] = [
            'key' => $key,
            'value' => $this->request->getQuery($key, null, null),
            'type' => $type,
            'field' => $field,
            'comp_op' => $compOp
        ];

        return $this;
    }

    public function searchInFields($key, $fields = [])
    {
        $this->searches[] = [
            'key' => $key,
            'fields' => $fields,
            'searchTerm' => $this->request->getQuery($key, null, null),
        ];

        return $this;
    }

    public function exactSearchInFields($key, $fields = [])
    {
        $this->exactSearches[] = [
            'key' => $key,
            'fields' => $fields,
            'searchTerm' => $this->request->getQuery($key, null, null),
        ];

        return $this;
    }

    public function getFilters()
    {
        $filters = [];
        foreach ($this->fields as $field) {
            $filters[$field['key']] = $field['value'];
        }

        foreach($this->searches AS $search) {
            $filters[$search['key']] = $search['searchTerm'];    
        }

        foreach($this->exactSearches AS $search) {
            $filters[$search['key']] = $search['searchTerm'];    
        }

        return $filters;
    }

    public function apply(QueryBuilder $qb, $alias)
    {      
        foreach ($this->fields as $conf) {
            $field = $conf['field'];
            $value = $conf['value'];
            $key = $conf['key'];
            $compOp = $conf['comp_op'];
            $type = $conf['type'];

            if (strlen($value) === 0) {
                continue;
            }
            if ($type === self::TYPE_HIDDEN) {
                continue;
            }

            if ($type === self::TYPE_CALLBACK) {
                $field($qb, $value);
                continue;
            }

            if ($field === null ) {
                $field = $alias . '.' . $key;
            }

            if ($type === self::TYPE_INT) {
                $value = (int)trim(preg_replace('/\s+/', '', $value));
            }

            if ($type === self::TYPE_DATE) {
                $dt = \DateTime::createFromFormat('d/m/Y', $value);
                $value = $dt->format('Y-m-d');
                $field = 'DATE(' . $field . ')';
            }

            if ($compOp === self::COMP_STARTS_WITH) {
                $value = '%' . $value;
                $compOp = 'LIKE';
            }
            if ($compOp === self::COMP_ENDS_WITH) {
                $value = $value . '%';
                $compOp = 'LIKE';
            }
            if ($compOp === self::COMP_CONTAINS) {
                $value = '%' . $value . '%';
                $compOp = 'LIKE';
            }

            $criteria = strtr('{field} {comp_op} :{key}', [  
                '{field}' => $field, 
                '{comp_op}' => $compOp, 
                '{key}' => $key 
            ]);

            $qb->andWhere($criteria)
                ->setParameter(strtr(':{key}', [ '{key}' => $key ]), $value);
        }

        $counter = 0;                      
        foreach($this->searches as $search) {
            if (
                isset($search['searchTerm']) && strlen($search['searchTerm']) > 0
                && ! empty($search['fields'])
            ) {
                $parts = [];
                foreach ($search['fields'] as $field) {
                    $parts[] = sprintf('LOWER(CAST(%s AS text)) LIKE :searchTerm'.$counter, $field);
                }

                $qb->andWhere(implode(' OR ', $parts))->setParameter('searchTerm'.$counter, '%' . strtolower($search['searchTerm']) . '%');
                $counter++;
            }            
        }

        $counter = 0;                      
        foreach($this->exactSearches as $search) {
            if (
                isset($search['searchTerm']) && strlen($search['searchTerm']) > 0
                && ! empty($search['fields'])
            ) {
                $parts = [];
                foreach ($search['fields'] as $field) {
                    $parts[] = sprintf('LOWER(CAST(%s AS text)) = :searchExactTerm'.$counter, $field);
                }

                $qb->andWhere(implode(' OR ', $parts))->setParameter('searchExactTerm'.$counter, strtolower($search['searchTerm']));
                $counter++;
            }            
        }

    }

    public function offsetSet($offset, $value)           
    {
        throw new \Core\Exception\RuntimeException('Filter modifications not allowed');
    }

    public function offsetExists($offset) 
    {
        $filters = $this->getFilters();
        return isset($filters[$offset]);
    }

    public function offsetUnset($offset) 
    {
        throw new \Core\Exception\RuntimeException('Filter modifications not allowed');
    }

    public function offsetGet($offset) 
    {
        $filters = $this->getFilters();
        return $filters[$offset];
    } 

    public function getIterator() 
    {
        $filters = $this->getFilters();
        return new ArrayIterator($filters);
    }

}
