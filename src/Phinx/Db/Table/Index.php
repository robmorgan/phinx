<?php
/**
 * Phinx
 *
 * (The MIT license)
 * Copyright (c) 2015 Rob Morgan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated * documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package    Phinx
 * @subpackage Phinx\Db
 */
namespace Phinx\Db\Table;

class Index
{
    /**
     * @var string
     */
    const UNIQUE = 'unique';

    /**
     * @var string
     */
    const INDEX = 'index';

    /**
     * @var string
     */
    const FULLTEXT = 'fulltext';

    /**
     * @var string
     */
    const USING = 'using';

    /**
     * @var string
     */
    const WHERE = 'where';

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var string
     */
    protected $type = self::INDEX;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var integer
     */
    protected $limit = null;

    /**
     * @var string
     */
    protected $using = null;

    /**
     * @var string
     */
    protected $where = null;

    /**
     * Sets the index columns.
     *
     * @param array $columns
     * @return Index
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Gets the index columns.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Sets the index type.
     *
     * @param string $type
     * @return Index
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Gets the index type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the index name.
     *
     * @param string $name
     * @return Index
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the index name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the index limit.
     *
     * @param integer $limit
     * @return Index
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Gets the index limit.
     *
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Gets the format type e.g. btree, hash, gist, gin...
     *
     * @return string
     */
    public function getUsing()
    {
        return $this->using;
    }

    /**
     * Sets the format type e.g. btree, hash, gist, gin...
     *
     * @param string $using
     *
     * @return Index
     */
    public function setUsing($using)
    {
        $this->using = $using;
        return $this;
    }

    /**
     * Gets the where part of the index
     *
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Sets the where part of the index
     *
     * @param string $where
     *
     * @return Index
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }



    /**
     * Utility method that maps an array of index options to this objects methods.
     *
     * @param array $options Options
     * @throws \RuntimeException
     * @return Index
     */
    public function setOptions($options)
    {
        // Valid Options
        $validOptions = array('type', 'unique', 'name', 'limit', 'using', 'where');
        foreach ($options as $option => $value) {
            if (!in_array($option, $validOptions, true)) {
                throw new \RuntimeException(sprintf('"%s" is not a valid index option.', $option));
            }

            // handle $options['unique']
            if (strcasecmp($option, self::UNIQUE) === 0) {
                if ((bool) $value) {
                    $this->setType(self::UNIQUE);
                }
                continue;
            }

            $method = 'set' . ucfirst($option);
            $this->$method($value);
        }
        return $this;
    }
}
