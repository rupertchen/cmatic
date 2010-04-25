<?php
require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';



function makeCmatId($id) {
    return sprintf('CMAT18%04d', $id);
}


function makeName($first, $last) {
    return "$last, $first";
}


$competitorTable = CmaticSchema::getTypeDbTable('competitor');
$scoringTable = CmaticSchema::getTypeDbTable('scoring');
$eventTable = CmaticSchema::getTypeDbTable('event');
$formTable = CmaticSchema::getTypeDbTable('form');
$groupTable = CmaticSchema::getTypeDbTable('group');
$groupMemberTable = CmaticSchema::getTypeDbTable('groupMember');

// Get data
$conn = PdoHelper::getPdo();
$r = $conn->query("select competitor_id, first_name, last_name from $competitorTable");
$competitorRs = $r->fetchAll(PDO::FETCH_ASSOC);

$r = $conn->query("select s.competitor_id as competitor_id, e.event_code as code, f.long_name as form_name"
    . " from $scoringTable s, $eventTable e, $formTable f"
    . " where s.event_id = e.event_id"
    . " and e.form_id = f.form_id"
    . " and s.competitor_id is not null");
$individualRs = $r->fetchAll(PDO::FETCH_ASSOC);

$r = $conn->query("select g.name as group_name, gm.competitor_id as competitor_id"
    . " from $groupTable g, $groupMemberTable gm"
    . " where g.group_id = gm.group_id");
$groupRs = $r->fetchAll(PDO::FETCH_ASSOC);

$conn = null;

// Organize information
$competitorIds = array();
$competitorName = array();
foreach ($competitorRs as $row) {
    $cmatId = makeCmatId($row['competitor_id']);
    $competitorIds[] = $cmatId;
    $competitorName[$cmatId] = makeName($row['first_name'], $row['last_name']);
}

$events = array();
foreach ($individualRs as $row) {
    $cmatId = makeCmatId($row['competitor_id']);
    if (!isset($events[$cmatId])) {
        $events[$cmatId] = array();
    }
    $events[$cmatId][] = "$row[code]: $row[form_name]";
}
foreach ($groupRs as $row) {
    $cmatId = makeCmatId($row['competitor_id']);
    if (!isset($events[$cmatId])) {
        $events[$cmatId] = array();
    }
    $events[$cmatId][] = "Group: $row[group_name]";
}



print <<<EOD
<table>
    <thead>
        <th>1_Id</th>
        <th>1_Name</th>
        <th>1_Event1</th>
        <th>1_Event2</th>
        <th>1_Event3</th>
        <th>1_Event4</th>
        <th>1_Event5</th>
        <th>1_Event6</th>
        <th>2_Id</th>
        <th>2_Name</th>
        <th>2_Event1</th>
        <th>2_Event2</th>
        <th>2_Event3</th>
        <th>2_Event4</th>
        <th>2_Event5</th>
        <th>2_Event6</th>
        <th>3_Id</th>
        <th>3_Name</th>
        <th>3_Event1</th>
        <th>3_Event2</th>
        <th>3_Event3</th>
        <th>3_Event4</th>
        <th>3_Event5</th>
        <th>3_Event6</th>
        <th>4_Id</th>
        <th>4_Name</th>
        <th>4_Event1</th>
        <th>4_Event2</th>
        <th>4_Event3</th>
        <th>4_Event4</th>
        <th>4_Event5</th>
        <th>4_Event6</th>
    </thead>
    <tbody>
EOD;

$width = 4;
$count = 0;
foreach ($competitorIds as $cId) {
    if (($count % $width) == 0) {
        print '<tr>';
    }
    printf('<td>%s</td><td>%s</td>', $cId, $competitorName[$cId]);
    $cEvents = $events[$cId];
    if ($cEvents) {
        foreach ($cEvents as $e) {
            print "<td>$e</td>";
        }
    }
    for ($i = 0; $i < (6 - count($cEvents)); $i++) {
        print "<td>&nbsp;</td>";
    }
    if (($count % $width) == ($width - 1)) {
        print '</tr>';
    }
    $count++;
}
// build remaining row
if (($count % $width) != 0) {
    while (($count % $width) != 0) {
        print "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
        $count++;
    }
    print "</tr>";
}

print <<<EOD
    </tbody>
</table>
EOD;

?>
