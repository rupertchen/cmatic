<?php

require_once '../util/Db.php';

define('CMATIC_SPLIT', '<cmatic_split>');

/**
 * Output header
 */
function printHeader() {
    // Output header
    print <<<EOD
<?php

/**
* This is a generated class.
*/
class CompetitorInfoData {

    public static function get() {
        return unserialize(
        // Begin generated data

EOD;
}


/**
 * Output footer
 */
function printFooter() {
    // Output footer
    print <<<EOD

        // End generated data
        );
        ;
    }
}

?>
EOD;
}


/**
 * Output array
 */
function printData() {
    $data = array(
        'events' => _buildEventsArray(),
        'competitors' => _buildCompetitorsArray()
    );

    $foo = array();
    foreach (explode(CMATIC_SPLIT, chunk_split($serialized_data = serialize($data), 80, CMATIC_SPLIT)) as $line) {
        $foo[] = str_replace('"', '\"', $line);
    }
    print '"';
    print implode("\"\n. \"", $foo);
    print '"';
}


function _buildEventsArray() {
    $eventTable = CmaticSchema::getTypeDbTable('event');
    $divisionTable = CmaticSchema::getTypeDbTable('division');
    $sexTable = CmaticSchema::getTypeDbTable('sex');
    $ageGroupTable = CmaticSchema::getTypeDbTable('ageGroup');
    $formTable = CmaticSchema::getTypeDbTable('form');
    $scoringTable = CmaticSchema::getTypeDbTable('scoring');

    $conn = PdoHelper::getPdo();
    $r = $conn->query("select e.event_id as event_id, e.event_code as event_code, d.long_name as division, s.long_name as sex, a.long_name as age, f.long_name as form"
        . " from $eventTable e, $divisionTable d, $sexTable s, $ageGroupTable a, $formTable f"
        . " where e.division_id = d.division_id and e.sex_id = s.sex_id and e.age_group_id = a.age_group_id and e.form_id = f.form_id");
    $resultSet = $r->fetchAll(PDO::FETCH_ASSOC);
    $conn = null;

    $ret = array();
    foreach ($resultSet as $row) {
        $ret[$row['event_id']] = array('code' => $row['event_code'],
                                       'name' => trim(str_replace('N/A', '', "$row[division] $row[sex] $row[age] $row[form]")));
    }

    return $ret;
}


function _buildCompetitorsArray() {
    $competitorTable = CmaticSchema::getTypeDbTable('competitor');
    $sexTable = CmaticSchema::getTypeDbTable('sex');
    $divisionTable = CmaticSchema::getTypeDbTable('division');
    $scoringTable = CmaticSchema::getTypeDbTable('scoring');
    $groupTable = CmaticSchema::getTypeDbTable('group');
    $groupMemberTable = CmaticSchema::getTypeDbTable('groupMember');

    $competitorQuery = 'select'
        . ' c.competitor_id, c.last_name, c.first_name, c.age, c.weight, c.amount_paid,'
        . ' c.email, c.phone_1, c.phone_2, c.street_address, c.city, c.state, c.postal_code,'
        . ' c.country, c.school, c.coach, c.emergency_contact_name, c.emergency_contact_relation, c.emergency_contact_phone,'
        . ' s.long_name as sex, d.long_name as division'
        . " from $competitorTable c, $sexTable s, $divisionTable d"
        . ' where c.sex_id = s.sex_id and c.division_id = d.division_id';

    $conn = PdoHelper::getPdo();
    $r = $conn->query($competitorQuery);
    $competitorResultSet = $r->fetchAll(PDO::FETCH_ASSOC);

    $ret = array();

    // set competitor info
    foreach ($competitorResultSet as $row) {
        $ret[$row['competitor_id']] = array('last_name' => $row['last_name'],
                                            'first_name' => $row['first_name'],
                                            'sex' => $row['sex'],
                                            'age' => $row['age'],
                                            'division' => $row['division'],
                                            'weight' => $row['weight'],
                                            'amount_paid' => $row['amount_paid'],
                                            'email' => $row['email'],
                                            'phone_1' => $row['phone_1'],
                                            'phone_2' => $row['phone_2'],
                                            'street_address' => $row['street_address'],
                                            'city' => $row['city'],
                                            'state' => $row['state'],
                                            'zip' => $row['postal_code'],
                                            'country' => $row['country'],
                                            'school' => $row['school'],
                                            'instructor' => $row['coach'],
                                            'emergency_contact_name' => $row['emergency_contact_name'],
                                            'emergency_contact_relation' => $row['emergency_contact_relation'],
                                            'emergency_contact_phone' => $row['emergency_contact_phone'],
                                            'individual_events' => array(),
                                            'group_events' => array());
    }

    // individual and group performances
    $eventQuery = "select event_id, competitor_id, group_id from $scoringTable order by competitor_id";
    $r = $conn->query($eventQuery);
    $eventResultSet = $r->fetchAll(PDO::FETCH_ASSOC);

    // groups
    $groupQuery = "select group_id, name from $groupTable";
    $r = $conn->query($groupQuery);
    $groupResultSet = $r->fetchAll(PDO::FETCH_ASSOC);

    // group members
    $groupMemberQuery = "select group_id, competitor_id from $groupMemberTable order by group_id";
    $r = $conn->query($groupMemberQuery);
    $groupMemberResultSet = $r->fetchAll(PDO::FETCH_ASSOC);

    // build group->name map
    $groupNameMap = array();
    foreach ($groupResultSet as $row) {
        $groupNameMap[$row['group_id']] = $row['name'];
    }

    // build group->member map
    $groupMemberMap = array();
    foreach($groupMemberResultSet as $row) {
        if (!$groupMemberMap[$row['group_id']]) {
            $groupMemberMap[$row['group_id']] = array();
        }
        $groupMemberMap[$row['group_id']][] = $row['competitor_id'];
    }

    // add events
    foreach ($eventResultSet as $row) {
        if ($row['competitor_id']) {
            // individual event
            $ret[$row['competitor_id']]['individual_events'][] = $row['event_id'];
        } else if ($row['group_id']) {
            // group event
            foreach ($groupMemberMap[$row['group_id']] as $competitorId) {
                $ret[$competitorId]['group_events'][strval($row['event_id'])] = $groupNameMap[$row['group_id']];
            }
        }
    }

    return $ret;
}


printHeader();
printData();
printFooter();
?>