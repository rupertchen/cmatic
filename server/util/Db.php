<?php
// This is a global variable (yes, shame on me) that contains all
// other globals for the app. It is the *ONLY* global variable.
$CMATIC = array();
require_once '../.cmatic_conf.php';

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

    public static function getPdo() {
        global $CMATIC;
        $conf = $CMATIC['conf'];
        $pdo = new PDO(PdoHelper::getPgsqlDsn($conf['db']['host'], $conf['db']['port'], $conf['db']['db']),
                $conf['db']['user'], $conf['db']['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}


$CMATIC['apiNameToDbTableMap'] = array(
    'ageGroup' => $CMATIC['conf']['app']['tablePrefix'] . 'config_age_group'
);

// TODO: Maybe these classes should all be merged into one class filled with statics
class CmaticSchema {

    /**
     * Retrieve the DB table name given the API name of the object
     */
    public static function getDbName($apiName) {
        // TODO: Throw an exception if null
        global $CMATIC;
        return $CMATIC['apiNameToDbTableMap'][$apiName];
    }
}


?>
