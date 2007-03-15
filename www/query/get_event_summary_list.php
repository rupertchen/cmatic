<?php
    include '../inc/php_header.inc';

    // Imports
    require_once 'util/Db.php';
    require_once 'util/Json.php';
    require_once 'obj/EventSummary.php';
    require_once 'obj/FormBlowout.php';

    // Request parameters
    $eventId = $_REQUEST['e'];
    $ringId = $_REQUEST['r'];

    // Fetch data
    $q0 = null;
    $q1 = null;
    $q2 = null;
    if (strlen($eventId) > 0) {
        // Get a specific event
        $q0 = 'SELECT * FROM cmat_annual.event'
            . " WHERE event_id = $eventId";
        $q1 = 'SELECT * FROM cmat_annual.form_blowout'
            . " WHERE event_id = $eventId";
        $q2 = 'SELECT s.form_blowout_id, count(*)'
            . ' FROM cmat_annual.scoring s, cmat_annual.form_blowout fb'
            . ' WHERE s.form_blowout_id = fb.form_blowout_id'
            . " AND fb.event_id = $eventId"
            . ' GROUP BY s.form_blowout_id';
    } else if (strlen($ringId) > 0) {
        $q0 = 'SELECT * FROM cmat_annual.event'
            . " WHERE ring_id = $ringId";
        $q1 = 'SELECT fb.*'
            . ' FROM cmat_annual.form_blowout fb, cmat_annual.event e'
            . ' WHERE fb.event_id = e.event_id'
            . " AND ring_id = $ringId";
        $q2 = 'SELECT s.form_blowout_id, count(*)'
            . ' FROM cmat_annual.scoring s, cmat_annual.form_blowout fb, cmat_annual.event e'
            . ' WHERE s.form_blowout_id = fb.form_blowout_id'
            . ' AND fb.event_id = e.event_id'
            . " AND e.ring_id = $ringId"
            . ' GROUP BY s.form_blowout_id';
    } else {
        // Get all events
        $q0 = 'SELECT * FROM cmat_annual.event';
        $q1 = 'SELECT * FROM cmat_annual.form_blowout';
        $q2 = 'SELECT form_blowout_id, count(*)'
           . ' FROM cmat_annual.scoring'
           . ' GROUP BY form_blowout_id';
    }
    $eventSet = array();
    $formBlowoutSet = array();
    $competitorCountList = array();

    $conn = Db::connect();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $tmp = new EventSummary();
        $tmp->fillFromDbRow($row);
        $eventSet[$row['event_id']] = $tmp;
    }
    Db::free_result($r);

    $r = Db::query($q1);
    while ($row = Db::fetch_array($r)) {
        $tmp = new FormBlowout();
        $tmp->fillFromDbRow($row);
        $formBlowoutSet[$row['form_blowout_id']] = $tmp;
    }
    Db::free_result($r);

    $r = Db::query($q2);
    while ($row = Db::fetch_array($r)) {
        $competitorCountList[$row['form_blowout_id']] = $row['count'];
    }
    Db::free_result($r);

    Db::close($conn);


    // Add counts to form blowouts
    foreach ($competitorCountList as $formBlowoutId => $competitorCount) {
        $formBlowoutSet[$formBlowoutId]->addCompetitorCount($competitorCount);
    }

    // Add form blowouts to event summaries
    foreach ($formBlowoutSet as $formBlowoutId => $v) {
        $eventSet[$v->d['event_id']]->addFormBlowout($v);
    }

    $eventList = array();
    foreach ($eventSet as $k => $v) {
        $eventList[] = $v;
    }

    header('Content-type: text/plain');
    include '../inc/php_footer.inc';

    echo Json::encode(array_map(array('EventSummary', 'getData'), $eventList));
?>
