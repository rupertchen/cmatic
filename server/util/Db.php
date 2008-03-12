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
        'code' => 'event_code',
        'ringId' => 'ring_id',
        'order' => 'ring_order',
        'numCompetitors' => 'num_competitors'
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
    'competitor' => array(
        'id' => 'competitor_id',
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'sexId' => 'sex_id',
        'age' => 'age',
        'divisionId' => 'division_id',
        'weight' => 'weight',
        'email' => 'email',
        'phone1' => 'phone_1',
        'phone2' => 'phone_2',
        'streetAddress' => 'street_address',
        'city' => 'city',
        'state' => 'state',
        'postalCode' => 'postal_code',
        'country' => 'country',
        'school' => 'school',
        'coach' => 'coach',
        'emergencyContactName' => 'emergency_contact_name',
        'emergencyContactRelation' => 'emergency_contact_relation',
        'emergencyContactPhone' => 'emergency_contact_phone',
        'isEarlyRegistration' => 'is_early_registration',
        'isDiscountRegistration' => 'is_discount_registration',
        'amountPaid' => 'amount_paid',
        'isConfirmed' => 'is_confirmed',
        'comments' => 'comments'
    ),
    'group' => array(
        'id' => 'group_id',
        'name' => 'name',
        'eventId' => 'event_id'
    ),
    'groupMember' => array(
        'id' => 'group_member_id',
        'groupId' => 'group_id',
        'competitorId' => 'competitor_id'
    ),
    'scoring' =>array(
        'id' => 'scoring_id',
        'eventId' => 'event_id',
        'competitorId' => 'competitor_id',
        'groupId' => 'group_id',
        'judge0' => 'judge_0',
        'judge1' => 'judge_1',
        'judge2' => 'judge_2',
        'judge3' => 'judge_3',
        'judge4' => 'judge_4',
        'judge5' => 'judge_5',
        'score0' => 'score_0',
        'score1' => 'score_1',
        'score2' => 'score_2',
        'score3' => 'score_3',
        'score4' => 'score_4',
        'score5' => 'score_5',
        'time' => 'seconds',
        'timeDeduction' => 'time_deduction',
        'otherDeduction' => 'other_deduction',
        'finalScore' => 'final_score',
        'placement' => 'placement'
    )
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
     * TODO: Comment this
     */
    public static function getIdSeqName($apiName) {
        return CmaticSchema::getTypeDbTable($apiName) . '_' . CmaticSchema::getFieldDbColumn($apiName, 'id') . '_seq';
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
     * @param $conn {PDO Connection}
     * @param $onlyNulls {boolean} True to only update if the code is currently NULL
     */
    public static function updateEventCodes($conn, $onlyNulls) {
        // This snippet of SQL is intended to be run within an update where the
        // event db table is available.
        // This could be better, but in Postgres 8.0, we can't alias the update
        // table. This looks to be changed in 8.2.
        $eventDbTable = CmaticSchema::getTypeDbTable('event');
        $selectEventSql = sprintf('select upper(d.short_name) || upper(s.short_name) || upper(a.short_name) || upper(f.short_name) as "event_code"'
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
        $conn->query(sprintf('update %s set event_code = (%s)%s', CmaticSchema::getTypeDbTable('event'), $selectEventSql, $whereClause));
        return true;
    }


    /**
     * Updates all num competitors
     *
     * @param $conn {PDO Connection}
     */
    public static function updateNumCompetitors($conn) {
        // get counts for all events
        $scoringTable = CmaticSchema::getTypeDbTable('scoring');
        $eventTable = CmaticSchema::getTypeDbTable('event');
        $r = $conn->query("select event_id, count(*) from $scoringTable group by event_id");
        $countResultSet = $r->fetchAll(PDO::FETCH_ASSOC);

        // Reset all counts to 0
        $conn->query("update $eventTable set num_competitors = 0");
        // Set all new counts
        foreach ($countResultSet as $row) {
            $conn->query(sprintf("update $eventTable set num_competitors = %d where event_id = %d", $row['count'], $row['event_id']));
        }
    }
}


?>
