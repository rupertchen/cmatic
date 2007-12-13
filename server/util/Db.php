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


$CMATIC['typeApiNameToDbTableMap'] = array(
    'ageGroup' => $CMATIC['conf']['app']['tablePrefix'] . 'config_age_group',
    'division' => $CMATIC['conf']['app']['tablePrefix'] . 'config_division',
    'event' => $CMATIC['conf']['app']['tablePrefix'] . 'config_event',
    'form' => $CMATIC['conf']['app']['tablePrefix'] . 'config_form',
    'sex' => $CMATIC['conf']['app']['tablePrefix'] . 'config_sex',
    'competitor' => $CMATIC['conf']['app']['tablePrefix'] . 'reg_competitor',
    'group' => $CMATIC['conf']['app']['tablePrefix'] . 'reg_group',
    'groupMember' => $CMATIC['conf']['app']['tablePrefix'] . 'reg_group_member',
    'scoring' => $CMATIC['conf']['app']['tablePrefix'] . 'result_scoring',
);

$CMATIC['fieldApiNameToDbColumnMap'] = array (
    'ageGroup' => array(
        'id' => 'age_group_id',
        'shortName' => 'short_name',
        'longName' => 'long_name'
    ),
    'division' => array(
        'id' => 'division_id',
        'shortName' => 'short_name',
        'longName' => 'long_name'
    ),
    'event' => array(
        'id' => 'event_id',
        'divisionId' => 'division_id',
        'sexId' => 'sex_id',
        'ageGroupId' => 'age_group_id',
        'formId' => 'form_id',
        'code' => 'event_code'
    ),
    'form' => array(
        'id' => 'form_id',
        'shortName' => 'short_name',
        'longName' => 'long_name'
    ),
    'sex' => array(
        'id' => 'sex_id',
        'shortName' => 'short_name',
        'longName' => 'long_name'
    ),
    'competitor' => array(),
    'group' => array(),
    'groupMember' => array(),
    'scoring' =>array()
);

// TODO: Maybe these classes should all be merged into one class filled with statics
class CmaticSchema {

    /**
     * Retrieve the DB table name given the name of the API type
     */
    public static function getTypeDbTable($apiName) {
        // TODO: Throw an exception if null
        global $CMATIC;
        return $CMATIC['typeApiNameToDbTableMap'][$apiName];
    }


    /**
     * Retrieve the DB column name given the names of the API type and field
     */
    public static function getFieldDbColumn($type, $field) {
        // TODO: Throw an exception if null at any point
        global $CMATIC;
        return $CMATIC['fieldApiNameToDbColumnMap'][$type][$field];
    }


    /**
     * Retrieve the fields exposed in the API
     */
    public static function getAllFieldsForType($type) {
        global $CMATIC;
        return $CMATIC['fieldApiNameToDbColumnMap'][$type];
    }

}


?>
