<?php

require_once '../util/Db.php';
require_once '../util/Ex.php';
require_once '../util/TextUtils.php';

/*
$buffer = array();
$test = array(array('placement' => 1, 'name' => 'GI Joe', 'score1' => '4.5', 'score2' => '5.6', 'score3' => '6.3', 'score4' => '1.5', 'score5' => '6.3', 'score6' => '', 'time' => '1:23', 'otherDeduction' => '0.4', 'timeDeduction' => '0.2', 'finalScore' => '5.53'),
              array('placement' => 2, 'name' => 'Ninja', 'score1' => '3.4', 'score2' => '1.2', 'score3' => '6.4', 'score4' => '1.3', 'score5' => '1.3', 'score6' => '1.6', 'time' => '2:12', 'otherDeduction' => '0.2', 'timeDeduction' => '0.1', 'finalScore' => '6.53'),
              array('placement' => 3, 'name' => 'Fluff', 'score1' => '10', 'score2' => '0', 'score3' => '3.4', 'score4' => '6.3', 'score5' => '5.5', 'score6' => '7.5', 'time' => '5:12', 'otherDeduction' => '0', 'timeDeduction' => '0', 'finalScore' => '7.0'));
getEventHtml($buffer, 'AMA30', 'Advanced Male Adult Long Fist', 'foo.css', $test);
print implode("\n", $buffer);
*/


// fetch all events
$eventTable = CmaticSchema::getTypeDbTable('event');
$divisionTable = CmaticSchema::getTypeDbTable('division');
$sexTable = CmaticSchema::getTypeDbTable('sex');
$ageGroupTable = CmaticSchema::getTypeDbTable('ageGroup');
$formTable = CmaticSchema::getTypeDbTable('form');
$scoringTable = CmaticSchema::getTypeDbTable('scoring');
$competitorTable = CmaticSchema::getTypeDbTable('competitor');
$groupTable = CmaticSchema::getTypeDbTable('group');

$conn = PdoHelper::getPdo();
$r = $conn->query("SELECT e.event_id, e.event_code, d.long_name AS division_name, s.long_name AS sex_name, a.long_name AS age_group_name, f.long_name AS form_name"
        . " FROM $eventTable e, $divisionTable d, $sexTable s, $ageGroupTable a, $formTable f"
        . " WHERE e.division_id = d.division_id"
        . " AND e.age_group_id = a.age_group_id"
        . " AND e.sex_id = s.sex_id"
        . " AND e.form_id = f.form_id"
        . " AND e.num_competitors > 0"
        . " ORDER BY event_code");
$eventRs = $r->fetchAll(PDO::FETCH_ASSOC);

$r = $conn->query("SELECT competitor_id, last_name, first_name FROM $competitorTable");
$competitorRs = $r->fetchAll(PDO::FETCH_ASSOC);
$competitorNames = array();
foreach ($competitorRs as $row) {
    $competitorNames[$row['competitor_id']] = formatCompetitorName($row['last_name'], $row['first_name']);
}

$r = $conn->query("SELECT group_id, name FROM $groupTable");
$groupRs = $r->fetchAll(PDO::FETCH_ASSOC);
$groupNames = array();
foreach ($groupRs as $row) {
    $groupNames[$row['group_id']] = $row['name'];
}

// Create output files
$events = array();
foreach ($eventRs as $event) {
    $eventId = $event['event_id'];
    $eventCode = $event['event_code'];
    $eventName = formatEventName($event['division_name'], $event['sex_name'], $event['age_group_name'], $event['form_name']);

    // save info for later
    $events[] = array('code' => $eventCode, 'name' => $eventName);

    // get scoring details of event
    $r = $conn->query("SELECT placement, score_0, score_1, score_2, score_3, score_4, score_5, time_text, time_deduction, other_deduction, final_score, group_id, competitor_id"
            . " FROM $scoringTable"
            . " WHERE event_id = $eventId"
            . " AND final_score > 0"
            . " ORDER BY placement");
    $scoringRs = $r->fetchAll(PDO::FETCH_ASSOC);
    $scores = array();
    foreach ($scoringRs as $scoring) {
        $scores[] = array('placement' => $scoring['placement'],
                          'name' => $scoring['competitor_id'] ? $competitorNames[$scoring['competitor_id']] : $groupNames[$scoring['group_id']],
                          'score1' => $scoring['score_0'],
                          'score2' => $scoring['score_1'],
                          'score3' => $scoring['score_2'],
                          'score4' => $scoring['score_3'],
                          'score5' => $scoring['score_4'],
                          'score6' => $scoring['score_5'],
                          'time' => $scoring['time_text'],
                          'timeDeduction' => $scoring['time_deduction'],
                          'otherDeduction' => $scoring['other_deduction'],
                          'finalScore' => $scoring['final_score']);
    }

    $buffer = array();
    getEventHtml($buffer, $eventCode, $eventName, 'style.css', $scores);

    $handle = fopen("output/$eventCode.html", 'w');
    fwrite($handle, implode("\n", $buffer));
    fclose($handle);
    print "generated: $eventCode<br />";
}


// Create index file
$buffer = array();
$buffer[] = <<<EOD
<html>
  <head>
  </head>
  <body>
    <h1>Event Results</h1>
EOD;
foreach ($events as $event) {
    $buffer[] = sprintf('<a href="%s.html"><span class="event-code">%s</span>: %s</a><br />',
            $event['code'],
            $event['code'],
            $event['name']);
}
$buffer[] = <<<EOD
  </body>
</html>

EOD;

$handle = fopen('output/index.html', 'w');
fwrite($handle, implode("\n", $buffer));
fclose($handle);
print "generated index<br />";


// Create css file
$handle = fopen('output/style.css', 'w');
fwrite($handle, getCss());
fclose($handle);




function getCss() {
    return <<<EOD
.oddRow {
    background-color: #EEE;
}

.placement,
.score {
    text-align: right;
    padding-right: 8px;
}

.name {
    padding: 0 10px;
}

EOD;
}


/**
 * Generate the HTML for this event's score.
 *
 * @param array $buffer
 * @param string $code
 * @param string $name
 * @param string $stylesheet relative url to css style sheet
 * @param array $scores scores for each competitor given in order of placement
 * @return array
 */
function getEventHtml (&$buffer, $code, $name, $stylesheet, $scores) {
    $buffer[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

    $buffer[] = <<<EOD
<html>
  <head>
    <title>$code : $name</title>
    <link rel="stylesheet" type="text/css" media="screen" href="$stylesheet" />
  </head>
  <body>
    <h1>$code : $name</h1>
    <table>
      <thead>
        <th scope="col">Placement</th>
        <th scope="col">Name</th>
        <th scope="col">Score 1</th>
        <th scope="col">Score 2</th>
        <th scope="col">Score 3</th>
        <th scope="col">Score 4</th>
        <th scope="col">Score 5</th>
        <th scope="col">Score 6</th>
        <th scope="col">Time Deduction</th>
        <th scope="col">Other Deduction</th>
        <th scope="col">Final Score</th>
      </thead>
      <tbody>

EOD;

    $isOdd = true;
    foreach ($scores as $s) {
        // Scoring
        $buffer[] = sprintf('<tr class="%s"><td class="placement">%d</td><td class="name">%s</td><td class="score">%s</td><td class="score">%s</td><td class="score">%s</td><td class="score">%s</td><td class="score">%s</td><td class="score">%s</td><td class="score">%s</td><td class="score">%s</td><td class="score">%s</td></tr>',
                $isOdd ? 'oddRow' : 'evenRow',
                $s['placement'],
                $s['name'],
                formatScore($s['score1']),
                formatScore($s['score2']),
                formatScore($s['score3']),
                formatScore($s['score4']),
                formatScore($s['score5']),
                formatScore($s['score6']),
                //$s['time'],
                formatScore($s['timeDeduction']),
                formatScore($s['otherDeduction']),
                formatScore($s['finalScore']));
        $isOdd = !$isOdd;
    }

    $buffer[] = <<<EOD
      </tbody>
    </table>
  </body>
</html>

EOD;

    // asdf
    return $buffer;
}


function formatScore($score) {
    return sprintf("%.2f", $score);
}


function formatEventName($division, $sex, $ageGroup, $form) {
    return trim(str_replace('N/A', '', sprintf("%s %s %s %s", $division, $sex, $ageGroup, $form)));
}


function formatCompetitorName($last, $first) {
    return "$last, $first";
}