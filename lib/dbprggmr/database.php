<?php
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

define('DBRGGMR_VERSION', 'RC1');

/**
 * dbprggmr is a prggmr event object which aims at providing a simple
 * to use interface for interacting with PHP's PDO object and enhancing
 * functionality by adding event driven calls.
 */
class Database extends \prggmr\Event
{
    /**
     * PDO Object
     * @var  object
     */
    protected $_pdo = null;

    /**
     * Fetch mode to use when fetching results.
     * @var  integer
     */
    const FETCH_MODE = PDO::FETCH_OBJ;

    /**
     * Constructs a new dbprggmr object.
     *
     * @param  string  $dsn  Connection DSN string.
     * @param  string  $username  The username to use for the connection.
     * @param  string  $password  The password to use for the connection.
     * @param  array  $options  An array of key->value driver options.
     *
     * @return  void
     */
    public function __construct($dsn, $username = null, $password = null,
                                $options = null)
    {
        try {
            $this->_pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Runs a sql query and returns the results, optionally dispatching pre and
     * post events.
     *
     * @param  string  $sql  SQL Query to run.
     * @param  array  $vars  Array of variables to assign.
     * @param  string  $event  Name of event to trigger a post and pre event.
     *
     * @return  object PDOStatement
     */
    public function query($sql, $vars = null, $event = null)
    {
        // disallow result stacking
        $this->setResultsStackable(false);
        if (null !== $event) {
            $this->setListener('pre_'.$event);
            $this->trigger(array(
                $sql, $vars
            ));
            $res = $this->getResults();
            if (is_array($res)) {
                extract($res, EXTR_OVERWRITE);
            }
        }

        $prep = $this->_pdo->prepare($sql);
        $prep->setFetchMode(FETCH_MODE);
        try {
            $prep->execute($vars);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        if (null !== $event) {
            $this->setListener('post_'.$event);
            $this->trigger(array(
                clone $prep
            ));
        }

        return $prep;
    }

   /**
     * Runs an sql query and returns all results found.
     *
     * @param  string  $sql  SQL Query to run.
     * @param  array  $vars  Array of variables to assign.
     * @param  string  $event  Name of event to trigger a post and pre event.
     *
     * @return  mixed  Array when results returned, False otherwise
     */
    public function fetchAll($sql, $vars = null, $event = null)
    {
        $query = $this->query($sql, $vars, $event);
        return $query->fetchAll();
    }

    /**
     * Runs an sql query and returns the first result.
     *
     * @param  string  $sql  SQL Query to run.
     * @param  array  $vars  Array of variables to assign.
     * @param  string  $event  Name of event to trigger a post and pre event.
     *
     * @return  mixed  Array when results returned, False otherwise
     */
    public function fetchOne($sql, $vars = null, $event = null)
    {
        $query = $this->query($sql, $vars, $event);
        return $query->fetchRow();
    }
}