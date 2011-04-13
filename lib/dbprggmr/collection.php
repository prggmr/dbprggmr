<?php
namespace dbprggmr;
/**
 *  Copyright 2010 Nickolas Whiting
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *
 * @author  Nickolas Whiting  <me@nwhiting.com>
 * @package  dbprggmr
 * @copyright  Copyright (c), 2011 Nickolas Whiting
 */

/**
 * dbprggmr is a prggmr event object which aims at providing a simple
 * to use interface for interacting with PHP's PDO object and enhancing
 * functionality by adding event driven calls.
 */
class Collection extends \prggmr\Singleton
{
    /**
     * Collection of dbprggmr\Database objects
     * @var  array
     */
    protected $_connections = null;

    /**
     * Default connection to use when multiple connections exist
     * @var  string
     */
    protected $_default = \prggmr::GLOBAL_DEFAULT;

    /**
     * Add a new Database object to the collection.
     * If none currently exist it will be set as the default.
     *
     * @param  object  $db \dbprggmr\Database object
     * @param  string  $id  identifier
     *
     * @return  void
     */
    public function add(\dbprggmr\Database $db, $id = null)
    {
        if (null == $id) {
            if ($this->_default == \prggmr::GLOBAL_DEFAULT) {
                $id = \prggmr::GLOBAL_DEFAULT;
            } else {
                $f = false;
                while(!$f) { $id = str_random(8); if (!isset($this->_connections[$id])) $f = true; }
            }
        }

        $this->_default = $id;
        $this->_connections[$id] = $db;
    }

    /**
     * Returns the database connection asked, if the id is left blank
     * the default connection is returned.
     *
     * @param  string  $id  Id of connection to retrieve.
     *         leave blank to get default.
     *
     * @return  object  dbprggmr\Database, false if invalid connection id
     */
    public function get($id = null)
    {
        if (null === $id) {
            $id = $this->_default;
        }

        if ($this->has($id)) {
            return $this->_connections[$id];
        }
        return false;
    }

    /**
     * Tests wether a connection exists.
     *
     * @param  string  $id  Id of connection.
     *
     * @return  boolean  True if exists, False otherwise
     */
    public function has($id)
    {
        return array_key_exists($id, $this->_connections);
    }
}