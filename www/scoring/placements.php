<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';

    $conn = Db::connect();

    $isSave = isset($_REQUEST["save"]);
    if ($isSave) {
        $scoringIds = $_REQUEST['medalPickup'];
        $updateQuery = 'UPDATE cmat_annual.scoring'
            . ' SET picked_up_medal = true '
            . ' WHERE scoring_id IN (' . implode(', ', $scoringIds) . ')';
        Db::query($updateQuery);
    }

    $q0 = 'SELECT s.scoring_id, s.competitor_id, c.first_name, c.last_name, s.group_id, g.name AS group_name, f.name AS form_name, final_placement, s.final_score, picked_up_medal'
        . ' FROM cmat_annual.scoring s'
        . ' LEFT OUTER JOIN cmat_annual.competitor c ON (s.competitor_id = c.competitor_id)'
        . ' LEFT OUTER JOIN cmat_annual."group" g ON (s.group_id = g.group_id)'
        . ' INNER JOIN cmat_annual.form_blowout fb ON (s.form_blowout_id = fb.form_blowout_id)'
        . ' INNER JOIN cmat_enum.form f ON (fb.form_id = f.form_id)'
        . ' WHERE final_placement < 4'
        . ' ORDER BY c.last_name, c.first_name, s.form_blowout_id, s.final_placement';

    $placementListNeed = array();
    $placementListGot = array();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        if ('t' == $row['picked_up_medal']) {
            $placementListGot[] = $row;
        } else {
            $placementListNeed[] = $row;
        }
    }
    Db::free_result($r);
    Db::close($conn);

    // Request parameters
    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Placement</title>
    <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="../css/scoring.css"/>
  </head>
  <body>
    <h1>Need medals</h1>
    <form action="placements.php" method="post">
      <input type="submit" name="save" value="Save!"/>
      <table border="1" cellpadding="1" cellspacing="1">
        <thead>
          <tr><th>Competitor</th><th>Event</th><th>Final Score</th><th>Place</th><th>Medal</th></tr>
        </thead>
        <tbody>
<?php
foreach ($placementListNeed as $k => $v) {
    $trFormat = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><input name="medalPickup[]" value="%s" type="checkbox"/></td></tr>' . "\n";
    $name = '';
    if ($v['competitor_id']) {
        $name = $v['last_name'] . ', ' . $v['first_name'] . '(' . $v['competitor_id'] . ')';
    } else {
        $name = $v['group_name'] . '(' . $v['group_id'] . ')';
    }
    echo sprintf($trFormat,
        $name,
        $v['form_name'],
        $v['final_score'],
        $v['final_placement'],
        $v['scoring_id']);
}
?>
        </tbody>
      </table>
      <input type="submit" name="save" value="Save!"/>
    </form>
    <h1>Got Medals</h1>
      <table border="1" cellpadding="1" cellspacing="1" style="background-color: #666">
        <thead>
          <tr><th>Competitor</th><th>Event</th><th>Final Score</th><th>Place</th></tr>
        </thead>
        <tbody>
<?php
foreach ($placementListGot as $k => $v) {
    $trFormat = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td></td></tr>' . "\n";
    $name = '';
    if ($v['competitor_id']) {
        $name = $v['last_name'] . ', ' . $v['first_name'] . '(' . $v['competitor_id'] . ')';
    } else {
        $name = $v['group_name'] . '(' . $v['group_id'] . ')';
    }
    echo sprintf($trFormat,
        $name,
        $v['form_name'],
        $v['final_score'],
        $v['final_placement']);
}
?>
        </tbody>
      </table>
  </body>
</html>
