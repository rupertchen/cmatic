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
$conn = PdoHelper::getPdo();
$r = $conn->query("select s.competitor_id as id, (c.last_name || ', ' || c.first_name) as name from $scoringTable s, $competitorTable c where s.competitor_id = c.competitor_id and s.event_id = $_REQUEST[id] order by performance_order");
$scoringRs = $r->fetchAll(PDO::FETCH_ASSOC);

if (0 == count($scoringRs)) {
    $groupTable = CmaticSchema::getTypeDbTable('group');
    $r = $conn->query("select '' as id, g.name as name from $scoringTable s, $groupTable g where s.group_id = g.group_id and s.event_id = $_REQUEST[id] order by performance_order");
    $scoringRs = $r->fetchAll(PDO::FETCH_ASSOC);
}

$r = $conn->query("select * from $eventTable where event_id = $_REQUEST[id]");
$eventRs = $r->fetchAll(PDO::FETCH_ASSOC);

$conn = null;

$EVENT_CODE_TITLE = 'Ring ' . $eventRs[0]['ring_id'] . ': ' . $eventRs[0]['event_code'];;
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
