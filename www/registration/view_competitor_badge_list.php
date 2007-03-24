<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';

    // Basic competitor info
    $q0 = 'SELECT c.competitor_id, c.first_name, c.last_name, g.comment AS gender'
        . ' FROM  cmat_annual.competitor c'
        . ' INNER JOIN cmat_enum.gender g ON (c.gender_id = g.gender_id)';
    // Individual events for a competitor
    $q1 = 'SELECT s.competitor_id, e.event_code, f.name AS form'
        . ' FROM cmat_annual.scoring s'
        . ' INNER JOIN cmat_annual.form_blowout fb ON (s.form_blowout_id = fb.form_blowout_id)'
        . ' INNER JOIN cmat_annual.event e ON (fb.event_id = e.event_id)'
        . ' INNER JOIN cmat_enum.form f ON (fb.form_id = f.form_id)'
        . ' WHERE s.competitor_id IS NOT NULL'
        . ' ORDER BY s.competitor_id, e.event_code';
    // Group events for a compoetitor
    $q2 = 'SELECT gm.member_id AS competitor_id, e.event_code, f.name AS form'
        . ' FROM cmat_annual.scoring s'
        . ' INNER JOIN cmat_annual.form_blowout fb ON (s.form_blowout_id = fb.form_blowout_id)'
        . ' INNER JOIN cmat_annual.event e ON (fb.event_id = e.event_id)'
        . ' INNER JOIN cmat_annual.group_member gm ON (s.group_id = gm.group_id)'
        . ' INNER JOIN cmat_enum.form f ON (fb.form_id = f.form_id)'
        . ' WHERE s.group_id IS NOT NULL'
        . ' ORDER BY s.group_id, e.event_code';

    $conn = Db::connect();

    // Results
    $competitors = array();
    $individualEvents = array();
    $groupEvents = array();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $competitors[$row['competitor_id']] = $row;
    }
    Db::free_result($r);

    $r = Db::query($q1);
    while ($row = Db::fetch_array($r)) {
        $cId = $row['competitor_id'];
        if (!isset($individualEvents[$cId])) {
            $individualEvents[$cId] = array();
        }
        $individualEvents[$cId][] = $row;
    }
    Db::free_result($r);

    $r = Db::query($q2);
    while ($row = Db::fetch_array($r)) {
        $cId = $row['competitor_id'];
        if (!isset($groupEvents[$cId])) {
            $groupEvents[$cId] = array();
        }
        $groupEvents[$cId][] = $row;
    }
    Db::free_result($r);

    Db::close($conn);

    // Build table rows
    $tableRows = array();
    foreach ($competitors AS $k0 => $v0) {
        $row = array();
        $row[] = $v0['competitor_id'];
        $row[] = $v0['last_name'] . ', ' . $v0['first_name'];
        $row[] = $v0['gender'];
        if ($individualEvents[$k0]) {
            foreach ($individualEvents[$k0] AS $k1 => $v1) {
                $row[] = $v1['event_code'] . ' ' . $v1['form'];
            }
        }
        if ($groupEvents[$k0]) {
            foreach ($groupEvents[$k0] AS $k1 => $v1) {
                $row[] = $v1['event_code'] . ' ' . $v1['form'];
            }
        }
        $tableRows[] = $row;
    }

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Competitor Badge List</title>
  </head>
  <body>
    <h1>Competitor Badge List</h1>
    <table>
      <thead>
        <th scope="col">Competitor ID</th>
        <th scope="col">Name</th>
        <th scope="col">Gender</th>
        <th scope="col">Event #1</th>
        <th scope="col">Event #2</th>
        <th scope="col">Event #3</th>
        <th scope="col">Event #4</th>
        <th scope="col">Event #5</th>
        <th scope="col">Event #6</th>
      </thead>
      <tbody>
<?php
    foreach ($tableRows as $k => $v) {
        echo '<tr><td>';
        echo implode('</td><td>', $v);
        echo '</td></tr>';
    }
?>
      </tbody>
    </table>
  </body>
</html>
