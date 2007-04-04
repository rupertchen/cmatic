<?php

require_once '../util/Db.php';

// Get events that have scores that aren't push hands
$listQuery = 'SELECT DISTINCT e.event_code, e.event_id, f.name AS form_name, f.form_id'
    . ' FROM cmat_annual.scoring s'
    . ' INNER JOIN cmat_annual.form_blowout fb ON (s.form_blowout_id = fb.form_blowout_id)'
    . ' INNER JOIN cmat_annual.event e ON (fb.event_id = e.event_id)'
    . ' INNER JOIN cmat_enum.form f ON (fb.form_id = f.form_id)'
    . ' WHERE s.cmat_year = 15'
    . ' AND fb.cmat_year = 15'
    . ' AND e.cmat_year = 15'
    . ' AND s.is_dropped = false'
    . ' AND fb.form_id NOT IN (43, 44, 45, 46, 47, 48)'
    . ' ORDER BY e.event_code';

$detailQuery = 'SELECT s.score_0, s.score_1, s.score_2, s.score_3, s.score_4, s.score_5,'
    . ' s.time, s.time_deduction, s.other_deduction, merited_score, s.final_score, s.final_placement,'
    . ' c.competitor_id, c.first_name AS competitor_first_name, c.last_name AS competitor_last_name,'
    . ' g.group_id, g.name AS group_name,'
    . ' fb.*'
    . ' FROM cmat_annual.scoring s'
    . ' INNER JOIN cmat_annual.form_blowout fb ON (s.form_blowout_id = fb.form_blowout_id)'
    . ' LEFT OUTER JOIN cmat_annual.competitor c on (c.competitor_id = s.competitor_id)'
    . ' LEFT OUTER JOIN cmat_annual."group" g on (g.group_id = s.group_id)'
    . ' WHERE s.cmat_year = 15'
    . ' AND fb.cmat_year = 15'
    . ' AND s.is_dropped = false'
    . ' ORDER BY s.final_placement, s.competitor_id';

// Patterns for HTML
$HTML_HEADER_PATTERN = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html><head><title>%s</title><link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\"/></head><body>";
$HTML_FOOTER_PATTERN = "</body></html>\n";
$EVENT_LIST_TR_PATTERN = '<tr class="dataRow"><th scope="row" class="data eventCode">%s</th><td class="data eventName"><a href="e%d.html" title="%s results">%s</a></td></tr>';
$EVENT_DETAIL_TR_PATTERN = '<tr class="dataRow"><td class="data placement">%d</td><td class="data">%s</td><td class="data points">%s</td><td class="data points">%s</td><td class="data points">%s</td><td class="data points">%s</td><td class="data points">%s</td><td class="data points">%s</td><td class="data points">%s</td><td class="data time">%s</td><td class="data points">%s</td><td class="data points">%s</td><td class="data points">%s</td></tr>';

// Results destination directory
$RESULTS_DIR = 'results/';
$STYLE_CSS_FILE_NAME = 'style.css';
$EVENT_LIST_FILE_NAME = 'index.html';
$EVENT_DETAIL_FILE_NAME_PATTERN = 'e%d.html';

// Queries
$eventList = array();
$eventDetail = array();
$conn = Db::connect();
$r = Db::query($listQuery);
while ($row = Db::fetch_array($r)) {
    $eventList[] = $row;
}
Db::free_result($r);
$r = Db::query($detailQuery);
while ($row = Db::fetch_array($r)) {
    $eventId = $row['event_id'];
    if (!isset($eventDetail[$eventId])) {
        $eventDetail[$eventId] = array();
    }
    $eventDetail[$eventId][] = $row;
}
Db::free_result($r);
Db::close($conn);

// Write CSS
echo "\nCreating linked resources\n";
echo "\t$STYLE_CSS_FILE_NAME\n";
$styleCss = array();
$styleCss[] = 'h1 { border-bottom: 2px solid #333; font-variant: small-caps; padding-left: 25px;}';
$styleCss[] = '.dataHeader { background-color: #333; color: #FFF; font-variant: small-caps; }';
$styleCss[] = '.dataHeader, .dataRow td, .dataRow th { border: 1px solid #333; }';
$styleCss[] = '.dataHeader, .data { padding: 2px 3px; }';
$styleCss[] = '.eventCode, .points, .time { font-family: "Courier New", monospace; font-size: 80%; text-align: center;}';
$styleCss[] = '.eventName { padding-left: 15px; }';
$styleCss[] = '.placement { text-align: right; font-weight: bold; padding-right: 10px; }';
$styleCss[] = 'table { border-collapse: collapse; }';
$handle = fopen($RESULTS_DIR . $STYLE_CSS_FILE_NAME, 'w');
foreach ($styleCss as $k => $v) {
  fwrite($handle, $v);
  fwrite($handle, "\n");
}
fclose($handle);

// Write event list page
echo "\nWriting HTML pages to $RESULTS_DIR\n";
echo "\t$EVENT_LIST_FILE_NAME\n";
$eventListBody = array();
$eventListBody[] = sprintf($HTML_HEADER_PATTERN, "Event List");
$eventListBody[] = '    <h1>Results: Event List</h1>';
$eventListBody[] = '    <table border="0" cellpadding="0" cellspacing="0">';
$eventListBody[] = '      <thead>';
$eventListBody[] = '        <tr>';
$eventListBody[] = '          <th scope="col" class="dataHeader">Event Code</th>';
$eventListBody[] = '          <th scope="col" class="dataHeader">Event Name</th>';
$eventListBody[] = '        </tr>';
$eventListBody[] = '      </thead>';
$eventListBody[] = '      <tbody>';
foreach ($eventList as $k => $v) {
  $eventListBody[] = sprintf($EVENT_LIST_TR_PATTERN, $v['event_code'], $v['event_id'], $v['event_code'], $v['form_name']);
}
$eventListBody[] = '      </tbody>';
$eventListBody[] = '    </table>';
$eventListBody[] = sprintf($HTML_FOOTER_PATTERN);

$handle = fopen($RESULTS_DIR . $EVENT_LIST_FILE_NAME, 'w');
foreach($eventListBody as $k => $v) {
  fwrite($handle, $v);
  fwrite($handle, "\n");
}
fclose($handle);

// Write each event page
foreach($eventList as $k => $v) {
  echo "\t$eventDetailFileName\n";
  $eventDetailFileName = sprintf($EVENT_DETAIL_FILE_NAME_PATTERN, $v['event_id']);
  $eventName = $v['event_code'] . ': ' . $v['form_name'];
  $eventDetailBody = array();
  $eventDetailBody[] = sprintf($HTML_HEADER_PATTERN, $eventName);
  $eventDetailBody[] = "<h1>$eventName</h1>";
  $eventDetailBody[] = '<table border="0" cellpadding="0" cellspacing="0">';
  $eventDetailBody[] = '<thead>';
  $eventDetailBody[] = '<tr>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Placement</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Name</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Score:1</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Score:2</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Score:3</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Score:4</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Score:5</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Score:6</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Merited Score</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Time</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Time Deduction</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Other Deduction</th>';
  $eventDetailBody[] = '<th scope="col" class="dataHeader">Final Score</th>';
  $eventDetailBody[] = '</tr>';
  $eventDetailBody[] = '</thead>';
  $eventDetailBody[] = '<tbody>';
  foreach ($eventDetail[$v['event_id']] as $k1 => $v1) {
    $place = $v1['final_placement'];
    $name = (0 == strlen($v1['group_name'])) ? ($v1['competitor_first_name'] . " " . $v1['competitor_last_name']) : $v1['group_name'];
    $score_0 = floatval($v1['score_0']);
    $score_1 = floatval($v1['score_1']);
    $score_2 = floatval($v1['score_2']);
    $score_3 = floatval($v1['score_3']);
    $score_4 = floatval($v1['score_4']);
    $score_5 = floatval($v1['score_5']);
    $score_0 = (0 == $score_0) ? '--' : number_format($score_0, 2);
    $score_1 = (0 == $score_1) ? '--' : number_format($score_1, 2);
    $score_2 = (0 == $score_2) ? '--' : number_format($score_2, 2);
    $score_3 = (0 == $score_3) ? '--' : number_format($score_3, 2);
    $score_4 = (0 == $score_4) ? '--' : number_format($score_4, 2);
    $score_5 = (0 == $score_5) ? '--' : number_format($score_5, 2);
    $mScore = number_format(floatval($v1['merited_score']), 2);
    $time = intval($v1['time']/60) . ':' . str_pad(intval($v1['time']%60), 2, '0', STR_PAD_LEFT);
    $tDeduct = number_format($v1['time_deduction'], 2);
    $oDeduct = number_format($v1['other_deduction'], 2);
    $fScore = number_format($v1['final_score'], 2);

    $eventDetailBody[] = sprintf($EVENT_DETAIL_TR_PATTERN, $place, $name,
      $score_0, $score_1, $score_2, $score_3, $score_4, $score_5, $mScore,
      $time, $tDeduct, $oDeduct, $fScore);
  }
  $eventDetailBody[] = '</tbody>';
  $eventDetailBody[] = '</table>';
  $eventDetailBody[] = sprintf($HTML_FOOTER_PATTERN);

  $handle = fopen($RESULTS_DIR . $eventDetailFileName, 'w');
  foreach($eventDetailBody as $k1 => $v1) {
    fwrite($handle, $v1);
    fwrite($handle, "\n");
  }
  fclose($handle);
}
echo "\nDone.\n";
?>
