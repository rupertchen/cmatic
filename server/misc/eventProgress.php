<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';

$eventTable = CmaticSchema::getTypeDbTable('event');
$formTable = CmaticSchema::getTypeDbTable('form');

// Get data
$conn = PdoHelper::getPdo();
$r = $conn->query("select e.event_code, e.num_competitors, e.ring_id, e.ring_order, f.long_name as form_name"
    . " from $eventTable e, $formTable f"
    . " where e.form_id = f.form_id"
    . " and is_finished = false"
    . " order by ring_id, ring_order");
$eventsRs = $r->fetchAll(PDO::FETCH_ASSOC);

$conn = null;

// Organize information
$schedule = array();
foreach ($eventsRs as $row) {
    $ringId = $row['ring_id'];
    if (!isset($schedule[$ringId])) {
        $schedule[$ringId] = array();
    }
    array_push($schedule[$ringId], $row);
}

print <<<EOD
<html>
<head>
    <meta http-equiv="refresh" content="30">
    <title>Tentative Event Schedule</title>
    <style type="text/css">
        .title {
            text-align: center;
        }

        .schedule {
            width: 100%;
        }

        .schedule-ring {
            vertical-align: top;
        }

        .event {
            border: 1px solid black;
            background-color: #EEE;
            margin: 4px 2px;
            overflow: hidden;
            font-size: 14px;
            padding: 2px 4px;
        }

        .event-code {
            font-weight: bold;
            font-family: "courier new", monospace;
        }

        .event-size {
        }

        .event-form {
            font-size: 75%;
        }
    </style>
</head>
<body>
<h1 class="title">Tentative Event Schedule</h1>
<p>
    Format is EVENT_CODE (APPROXIMATE_COMPETITOR_COUNT): FORM_NAME.
    Events that have finished are not shown.
</p>
<table class="schedule">
    <thead>
        <th>Ring 1</th>
        <th>Ring 2</th>
        <th>Ring 3</th>
        <th>Ring 4</th>
        <th>Ring 5</th>
        <th>Ring 6</th>
        <th>Ring 7</th>
        <th>Ring 8</th>
    </thead>
    <tbody>
        <tr>
EOD;

for ($ringId = 1; $ringId <= 8; $ringId++) {
    print '<td class="schedule-ring">';
    $ringSchedule = $schedule[$ringId];
    if ($ringSchedule) {
        foreach ($schedule[$ringId] as $event) {
            $height =
            printf('<div class="event" style="height: %dem"><span class="event-code">%s</span> <span class="event-size">(%s)</span>: <span class="event-form">%s</span></div>',
                max(floatval($event['num_competitors']) * 0.5, 1),
                $event['event_code'],
                $event['num_competitors'],
                $event['form_name']);
        }
    } else {
        print '<div class="event">No Events</div>';
    }
    print '</td>';
}

print <<<EOD
        </tr>
    </tbody>
</table>
</body>
</html>
EOD;

?>
