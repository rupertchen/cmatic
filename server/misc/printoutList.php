<?php
require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';


// Get data
$eventTable = CmaticSchema::getTypeDbTable('event');
$conn = PdoHelper::getPdo();
$r = $conn->query("select event_id, event_code, ring_id, ring_order from $eventTable where ring_id > 0 and ring_id <= 8 order by ring_id, ring_order");
$eventRs = $r->fetchAll(PDO::FETCH_ASSOC);
$conn = null;

// Organize information
$schedule = array(array(), array(), array(), array(), array(), array(), array(), array(), array());
foreach ($eventRs as $row) {
    $schedule[$row['ring_id']][] = array($row['event_id'], $row['event_code']);
}

// Output
foreach ($schedule as $ring) {
    print '<ol style="float: left, width: 75px; margin: 5px;">';
    foreach ($ring as $event) {
        print '<li><a href="printoutEvent.php?id=' . $event[0] . '">' . $event[1] . '</a></li>';
    }
    print '</ol>';
}

?>
