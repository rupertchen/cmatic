<?php
// TODO Make a version that can wrap PDO


/**
 * Required Options:
 * host
 * db
 * user
 * password
 */
interface DbConnection {
    public function connect();
    public function close();
    public function query($query);
    public function fetch_array($resultSet);
    public function free_result($resultSet);
}

class PostgresDbConnection implements DbConnection {

    private $_options = null;
    private $_connection = null;

    function __construct($options = array()) {
        $this->_options = $options;
    }

    function __destruct() {
        $this->close();
    }

    public function connect() {
        $this->_connection = pg_connect(sprintf('host=%s dbname=%s user=%s password=%s',
            $this->_options['host'], $this->_options['db'], $this->_options['user'], $this->_options['password']))
            or die('Could not connect: ' . pg_last_error());
    }

    public function query($q) {
        $ret = pg_query($q) or die('Query failed: ' . pg_last_error() . "::$q");
        return $ret;
    }

    public function fetch_array($r) {
        return pg_fetch_array($r, null, PGSQL_ASSOC);
    }

    public function free_result($result) {
        pg_free_result($result);
    }

    public function close() {
        if (!is_null($this->_connection)) {
            pg_close($this->_connection);
        }
    }
}

/**
 * Convenience methods for using PDO.
 * TODO:Consider renaming this class
 */
class PdoHelper {
    public static function getPgsqlDsn($host, $port, $dbName) {
        return sprintf('pgsql:host=%s port=%s dbname=%s', addslashes($host), addslashes($port), addslashes($dbName));
    }

    public static function removeComments($sql) {
        // Strip away inline comments beginning with "--" or "//"
        $sql = preg_replace('/(--|\/\/).*$/m', '', $sql);
        // Strip away new lines (and other extra whitespaces while we're at it)
        $sql = preg_replace('/\s+/', ' ', $sql);
        // Strip away block comments beginning with "/*" and ending with "*/"
        $sql = preg_replace('/\/\*.*\*\//U', ' ', $sql);
        return split(';', $sql);
    }
}

?>
