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

    /**
     * Updates all event codes
     * TODO: Put this into CmaticSchema?
     * @param $c {PDO Connection}
     * @param $onlyNulls {boolean} True to only update if the code is currently NULL
     */
    public static function updateEventCodes($c, $onlyNulls) {
        // This snippet of SQL is intended to be run within an update where the
        // event db table is available.
        // This could be better, but in Postgres 8.0, we can't alias the update
        // table. This looks to be changed in 8.2.
        $eventDbTable = CmaticSchema::getTypeDbTable('event');
        $selectEventSql = sprintf('select d.short_name || s.short_name || a.short_name || f.short_name as "event_code"'
            . ' from %s d, %s s, %s a, %s f'
            . ' where %s.division_id = d.division_id'
            . ' and %s.sex_id = s.sex_id'
            . ' and %s.age_group_id = a.age_group_id'
            . ' and %s.form_id = f.form_id',
            CmaticSchema::getTypeDbTable('division'),
            CmaticSchema::getTypeDbTable('sex'),
            CmaticSchema::getTypeDbTable('ageGroup'),
            CmaticSchema::getTypeDbTable('form'),
            $eventDbTable, $eventDbTable, $eventDbTable, $eventDbTable
            );
        $whereClause = $onlyNulls ? ' where event_code is null' : '';
        $c->query(sprintf('update %s set event_code = (%s)%s', CmaticSchema::getTypeDbTable('event'), $selectEventSql, $whereClause));
        return true;
    }
}


?>
