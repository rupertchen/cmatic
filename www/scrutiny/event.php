<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';


    // Queries
    // Get events and the number of competitors competing in it (if at least 1)
    $q0 = 'SELECT e.event_id, e.event_code, e.ring_id, count(*) AS num_competitors'
        . ' FROM cmat_annual.event e, cmat_annual.scoring s, cmat_annual.form_blowout fb'
        . ' WHERE s.form_blowout_id = fb.form_blowout_id'
        . ' AND fb.event_id = e.event_id'
        . ' GROUP BY e.event_id, e.event_code, e.ring_id';

    // Get events that have no competitors competing in them
    $q1 = 'SELECT event_id, event_code, ring_id'
        . ' FROM cmat_annual.event'
        . ' WHERE event_id'
        . ' IN ('
        . '(SELECT event_id'
        . ' FROM cmat_annual.event)'
        . ' EXCEPT '
        . '(SELECT DISTINCT fb.event_id'
        . ' FROM cmat_annual.scoring s, cmat_annual.form_blowout fb'
        . ' WHERE s.form_blowout_id = fb.form_blowout_id))';


    // Sorted results
    $eventHasCompetitorsList = array();
    $eventHasNoCompetitorsList = array();

    // Database calls
    $conn = Db::connect();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $eventHasCompetitorsList[] = $row;
    }
    Db::free_result($r);

    $r = Db::query($q1);
    while ($row = Db::fetch_array($r)) {
        $eventHasNoCompetitorsList[] = $row;
    }
    Db::free_result($r);

    Db::close($conn);


    // Checks
    $fail0 = array();
    $p0 = 'Event %d(%s) has %d competitor(s), but is not put in a ring.';
    foreach ($eventHasCompetitorsList as $k => $v) {
        if ($v['ring_id'] < 1) {
            $fail0[] = sprintf($p0, $v['event_id'], $v['event_code'], $v['num_competitors']);
        }
    }

    $fail1 = array();
    $p1 = 'Event %d(%s) has no competitors, but is scheduled to run';
    foreach ($eventHasNoCompetitorsList as $k => $v) {
        if ($v['ring_id'] > -1) {
            $fail1[] = sprintf($p1, $v['event_id'], $v['event_code']);
        }
    }

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Scrutiny: Check Event Entries</title>
  </head>
  <body>
    <h1>Scrutiny: Check Event Entries</h1>
    <h2>All events that have at least one competitor should be scheduled for a ring.</h2>
<?php TextUtils::printFailures($fail0); ?>
    <h2>All events that have no competitors should be placed in Ring -1.</h2>
<?php TextUtils::printFailures($fail1); ?>
  </body>
</html>
