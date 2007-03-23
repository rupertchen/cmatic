<?php
    include '../inc/php_header.inc';

    // Imports
    require_once 'util/Db.php';
    require_once 'util/Json.php';
    require_once 'obj/EventScoring.php';
    require_once 'obj/FormBlowout.php';
    require_once 'obj/Scoring.php';

    // Request parameters
    $eventId = $_REQUEST['e'];

    // Fetch data
    $q0 = null;
    $q1 = null;
    $q2 = null;
    $q3 = null;
    if (strlen($eventId) > 0) {
        // Get a specific event
        $q0 = 'SELECT * FROM cmat_annual.event'
            . " WHERE event_id = $eventId";
        $q1 = 'SELECT fb.*, f.ring_configuration_id'
            . ' FROM cmat_annual.form_blowout fb'
            . ' INNER JOIN cmat_enum.form f ON (fb.form_id = f.form_id)'
            . " WHERE event_id = $eventId";
        $q2 = 'SELECT s.form_blowout_id, count(*)'
            . ' FROM cmat_annual.scoring s, cmat_annual.form_blowout fb'
            . ' WHERE s.form_blowout_id = fb.form_blowout_id'
            . " AND fb.event_id = $eventId"
            . ' GROUP BY s.form_blowout_id';
        $q3 = 'SELECT s.*, c.first_name AS competitor_first_name, c.last_name AS competitor_last_name, g.name AS group_name'
            . ' FROM cmat_annual.form_blowout fb,'
            . ' cmat_annual.scoring s'
            . ' LEFT OUTER JOIN cmat_annual.competitor c ON (s.competitor_id = c.competitor_id)'
            . ' LEFT OUTER JOIN cmat_annual."group" g ON (s.group_id = g.group_id)'
            . ' WHERE s.form_blowout_id = fb.form_blowout_id'
            . " AND fb.event_id = $eventId";
    } else {
        exit;
    }
    $eventScoring = new EventScoring();
    $formBlowoutSet = array();

    $conn = Db::connect();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $eventScoring->fillFromDbRow($row);
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
        $formBlowoutSet[$row['form_blowout_id']]->addCompetitorCount($row['count']);
    }
    Db::free_result($r);

    $r = Db::query($q3);
    while ($row = Db::fetch_array($r)) {
        $tmp = new Scoring();
        $tmp->fillFromDbRow($row);
        $scoringList[] = $tmp;
    }
    Db::free_result($r);

    Db::close($conn);


    // Add form blowouts to event scoring
    foreach ($formBlowoutSet as $k => $v) {
        $eventScoring->addFormBlowout($v);
    }

    // Add scorings to event scoring
    foreach ($scoringList as $k => $v) {
        $eventScoring->addScoring($v);
    }

    header('Content-type: text/plain');
    include '../inc/php_footer.inc';

    echo Json::encode($eventScoring->d);
?>
