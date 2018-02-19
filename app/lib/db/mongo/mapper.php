<?php

/*

    Copyright (c) 2009-2015 F3::Factory/Bong Cosca, All rights reserved.

    This file is part of the Fat-Free Framework (http://fatfreeframework.com).

    This is free software: you can redistribute it and/or modify it under the
    terms of the GNU General Public License as published by the Free Software
    Foundation, either version 3 of the License, or later.

    Fat-Free Framework is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with Fat-Free Framework.  If not, see <http://www.gnu.org/licenses/>.

*/

namespace db\mongo;

//! MongoDB mapper
class mapper extends \DB\Cursor
{
    //! MongoDB wrapper
        protected $db;
    protected //! Mongo collection
        $collection;
    protected //! Mongo document
        $document = array();
    protected //! Mongo cursor
        $cursor;

    /**
     *   Return database type.
     *
     *   @return string
     **/
    public function dbtype()
    {
        return 'Mongo';
    }

    /**
     *   Return TRUE if field is defined.
     *
     *   @return bool
     *
     *   @param $key string
     **/
    public function exists($key)
    {
        return array_key_exists($key, $this->document);
    }

    /**
     *   Assign value to field.
     *
     *   @return scalar|FALSE
     *
     *   @param $key string
     *   @param $val scalar
     **/
    public function set($key, $val)
    {
        return $this->document[$key] = $val;
    }

    /**
     *   Retrieve value of field.
     *
     *   @return scalar|FALSE
     *
     *   @param $key string
     **/
    public function &get($key)
    {
        if ($this->exists($key)) {
            return $this->document[$key];
        }
        user_error(sprintf(self::E_Field, $key), E_USER_ERROR);
    }

    /**
     *   Delete field.
     *
     *   @return NULL
     *
     *   @param $key string
     **/
    public function clear($key)
    {
        unset($this->document[$key]);
    }

    /**
     *   Convert array to mapper object.
     *
     *   @return \DB\Mongo\Mapper
     *
     *   @param $row array
     **/
    protected function factory($row)
    {
        $mapper = clone($this);
        $mapper->reset();
        foreach ($row as $key => $val) {
            $mapper->document[$key] = $val;
        }
        $mapper->query = array(clone($mapper));
        if (isset($mapper->trigger['load'])) {
            \Base::instance()->call($mapper->trigger['load'], $mapper);
        }

        return $mapper;
    }

    /**
     *   Return fields of mapper object as an associative array.
     *
     *   @return array
     *
     *   @param $obj object
     **/
    public function cast($obj = null)
    {
        if (!$obj) {
            $obj = $this;
        }

        return $obj->document;
    }

    /**
     *   Build query and execute.
     *
     *   @return \DB\Mongo\Mapper[]
     *
     *   @param $fields string
     *   @param $filter array
     *   @param $options array
     *   @param $ttl int
     **/
    public function select($fields = null, $filter = null, array $options = null, $ttl = 0)
    {
        if (!$options) {
            $options = array();
        }
        $options += array(
            'group' => null,
            'order' => null,
            'limit' => 0,
            'offset' => 0,
        );
        $fw = \Base::instance();
        $cache = \Cache::instance();
        if (!($cached = $cache->exists($hash = $fw->hash($this->db->dsn().
            $fw->stringify(array($fields, $filter, $options))).'.mongo',
            $result)) || !$ttl || $cached[0] + $ttl < microtime(true)) {
            if ($options['group']) {
                $grp = $this->collection->group(
                    $options['group']['keys'],
                    $options['group']['initial'],
                    $options['group']['reduce'],
                    array(
                        'condition' => $filter,
                        'finalize' => $options['group']['finalize'],
                    )
                );
                $tmp = $this->db->selectcollection(
                    $fw->get('HOST').'.'.$fw->get('BASE').'.'.
                    uniqid(null, true).'.tmp'
                );
                $tmp->batchinsert($grp['retval'], array('w' => 1));
                $filter = array();
                $collection = $tmp;
            } else {
                $filter = $filter ?: array();
                $collection = $this->collection;
            }
            $this->cursor = $collection->find($filter, $fields ?: array());
            if ($options['order']) {
                $this->cursor = $this->cursor->sort($options['order']);
            }
            if ($options['limit']) {
                $this->cursor = $this->cursor->limit($options['limit']);
            }
            if ($options['offset']) {
                $this->cursor = $this->cursor->skip($options['offset']);
            }
            $result = array();
            while ($this->cursor->hasnext()) {
                $result[] = $this->cursor->getnext();
            }
            if ($options['group']) {
                $tmp->drop();
            }
            if ($fw->get('CACHE') && $ttl) {
                // Save to cache backend
                $cache->set($hash, $result, $ttl);
            }
        }
        $out = array();
        foreach ($result as $doc) {
            $out[] = $this->factory($doc);
        }

        return $out;
    }

    /**
     *   Return records that match criteria.
     *
     *   @return \DB\Mongo\Mapper[]
     *
     *   @param $filter array
     *   @param $options array
     *   @param $ttl int
     **/
    public function find($filter = null, array $options = null, $ttl = 0)
    {
        if (!$options) {
            $options = array();
        }
        $options += array(
            'group' => null,
            'order' => null,
            'limit' => 0,
            'offset' => 0,
        );

        return $this->select(null, $filter, $options, $ttl);
    }

    /**
     *   Count records that match criteria.
     *
     *   @return int
     *
     *   @param $filter array
     *   @param $ttl int
     **/
    public function count($filter = null, $ttl = 0)
    {
        $fw = \Base::instance();
        $cache = \Cache::instance();
        if (!($cached = $cache->exists($hash = $fw->hash($fw->stringify(
            array($filter))).'.mongo', $result)) || !$ttl ||
            $cached[0] + $ttl < microtime(true)) {
            $result = $this->collection->count($filter ?: array());
            if ($fw->get('CACHE') && $ttl) {
                // Save to cache backend
                $cache->set($hash, $result, $ttl);
            }
        }

        return $result;
    }

    /**
     *   Return record at specified offset using criteria of previous
     *   load() call and make it active.
     *
     *   @return array
     *
     *   @param $ofs int
     **/
    public function skip($ofs = 1)
    {
        $this->document = ($out = parent::skip($ofs)) ? $out->document : array();
        if ($this->document && isset($this->trigger['load'])) {
            \Base::instance()->call($this->trigger['load'], $this);
        }

        return $out;
    }

    /**
     *   Insert new record.
     *
     *   @return array
     **/
    public function insert()
    {
        if (isset($this->document['_id'])) {
            return $this->update();
        }
        if (isset($this->trigger['beforeinsert']) &&
            \Base::instance()->call($this->trigger['beforeinsert'],
                array($this, array('_id' => $this->document['_id']))) === false) {
            return $this->document;
        }
        $this->collection->insert($this->document);
        $pkey = array('_id' => $this->document['_id']);
        if (isset($this->trigger['afterinsert'])) {
            \Base::instance()->call($this->trigger['afterinsert'],
                array($this, $pkey));
        }
        $this->load($pkey);

        return $this->document;
    }

    /**
     *   Update current record.
     *
     *   @return array
     **/
    public function update()
    {
        $pkey = array('_id' => $this->document['_id']);
        if (isset($this->trigger['beforeupdate']) &&
            \Base::instance()->call($this->trigger['beforeupdate'],
                array($this, $pkey)) === false) {
            return $this->document;
        }
        $this->collection->update(
            $pkey, $this->document, array('upsert' => true));
        if (isset($this->trigger['afterupdate'])) {
            \Base::instance()->call($this->trigger['afterupdate'],
                array($this, $pkey));
        }

        return $this->document;
    }

    /**
     *   Delete current record.
     *
     *   @return bool
     *
     *   @param $filter array
     **/
    public function erase($filter = null)
    {
        if ($filter) {
            return $this->collection->remove($filter);
        }
        $pkey = array('_id' => $this->document['_id']);
        if (isset($this->trigger['beforeerase']) &&
            \Base::instance()->call($this->trigger['beforeerase'],
                array($this, $pkey)) === false) {
            return false;
        }
        $result = $this->collection->
            remove(array('_id' => $this->document['_id']));
        parent::erase();
        if (isset($this->trigger['aftererase'])) {
            \Base::instance()->call($this->trigger['aftererase'],
                array($this, $pkey));
        }

        return $result;
    }

    /**
     *   Reset cursor.
     *
     *   @return NULL
     **/
    public function reset()
    {
        $this->document = array();
        parent::reset();
    }

    /**
     *   Hydrate mapper object using hive array variable.
     *
     *   @return NULL
     *
     *   @param $var array|string
     *   @param $func callback
     **/
    public function copyfrom($var, $func = null)
    {
        if (is_string($var)) {
            $var = \Base::instance()->get($var);
        }
        if ($func) {
            $var = call_user_func($func, $var);
        }
        foreach ($var as $key => $val) {
            $this->document[$key] = $val;
        }
    }

    /**
     *   Populate hive array variable with mapper fields.
     *
     *   @return NULL
     *
     *   @param $key string
     **/
    public function copyto($key)
    {
        $var = &\Base::instance()->ref($key);
        foreach ($this->document as $key => $field) {
            $var[$key] = $field;
        }
    }

    /**
     *   Return field names.
     *
     *   @return array
     **/
    public function fields()
    {
        return array_keys($this->document);
    }

    /**
     *   Return the cursor from last query.
     *
     *   @return object|NULL
     **/
    public function cursor()
    {
        return $this->cursor;
    }

    /**
     *   Retrieve external iterator for fields.
     *
     *   @return object
     **/
    public function getiterator()
    {
        return new \ArrayIterator($this->cast());
    }

    /**
     *   Instantiate class.
     *
     *   @param $db object
     *   @param $collection string
     **/
    public function __construct(\DB\Mongo $db, $collection)
    {
        $this->db = $db;
        $this->collection = $db->selectcollection($collection);
        $this->reset();
    }
}
