<?php
require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';

if (!$_REQUEST['id']) {
    print 'missing event code';
    exit;
}


function makeCmatId($id) {
    return sprintf('CMAT16%04d', $id);
}


function makeName($first, $last) {
    return "$last, $first";
}


// Get data
$scoringTable = CmaticSchema::getTypeDbTable('scoring');
$competitorTable = CmaticSchema::getTypeDbTable('competitor');
$eventTable = CmaticSchema::getTypeDbTable('event');
$divisionTable = CmaticSchema::getTypeDbTable('division');
$ageTable = CmaticSchema::getTypeDbTable('ageGroup');
$sexTable = CmaticSchema::getTypeDbTable('sex');
$formTable = CmaticSchema::getTypeDbTable('form');
$conn = PdoHelper::getPdo();
$r = $conn->query("select s.competitor_id as id, (c.last_name || ', ' || c.first_name) as name from $scoringTable s, $competitorTable c where s.competitor_id = c.competitor_id and s.event_id = $_REQUEST[id] order by performance_order");
$scoringRs = $r->fetchAll(PDO::FETCH_ASSOC);

if (0 == count($scoringRs)) {
    $groupTable = CmaticSchema::getTypeDbTable('group');
    $r = $conn->query("select '' as id, g.name as name from $scoringTable s, $groupTable g where s.group_id = g.group_id and s.event_id = $_REQUEST[id] order by performance_order");
    $scoringRs = $r->fetchAll(PDO::FETCH_ASSOC);
}

//TODO: Clean this up
$r = $conn->query("select ring_id, event_code, d.long_name as division_name, a.long_name as age_name, s.long_name as sex_name, f.long_name as form_name from $eventTable e LEFT JOIN $divisionTable d ON (d.division_id=e.division_id) JOIN $ageTable a ON (a.age_group_id = e.age_group_id) JOIN $sexTable s ON s.sex_id=e.sex_id JOIN $formTable f ON f.form_id = e.form_id  where event_id = $_REQUEST[id]");
$eventRs = $r->fetchAll(PDO::FETCH_ASSOC);



$conn = null;

$EVENT_CODE_TITLE = 'Ring ' . $eventRs[0]['ring_id'] . ': ' . $eventRs[0]['event_code'] . ' -- <span style="font-size: 18px">' . $eventRs[0]['division_name'] . ' ' . $eventRs[0]['sex_name'] . ' ' . $eventRs[0]['age_name'] . ' ' . $eventRs[0]['form_name'] . '</span>';;

// Output
print <<<EOD
<h1>$EVENT_CODE_TITLE</h1>
<table border="1" cellpadding="2" cellspacing="2">
    <thead>
        <th>Order</th>
        <th>Id</th>
        <th>Name</th>
        <th>Score 1</th>
        <th>Score 2</th>
        <th>Score 3</th>
        <th>Score 4</th>
        <th>Score 5</th>
        <th>Score 6</th>
        <th>Time</th>
        <th>Deduction</th>
        <th>Time Deduction</th>
        <th>Final Score</th>
        <th>Placement</th>
    </thead>
    <tbody>
EOD;

foreach ($scoringRs as $row) {
    $id = makeCmatId($row['id']);
    $name = $row['name'];
    print '<tr><td>&nbsp;</td><td>' . makeCmatId($row['id']) . '</td><td>' . $name . '</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
}

print <<<EOD
    </tbody>
</table>
EOD;
?>
