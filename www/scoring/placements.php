<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';


    $isSave = isset($_REQUEST["save"]);
    if ($isSave) {
    }

    $q0 = 'SELECT s.competitor_id, c.first_name, c.last_name, f.name AS form_name, final_placement'
        . ' FROM cmat_annual.scoring s'
        . ' INNER JOIN cmat_annual.competitor c ON (s.competitor_id = c.competitor_id)'
        . ' INNER JOIN cmat_annual.form_blowout fb ON (s.form_blowout_id = fb.form_blowout_id)'
        . ' INNER JOIN cmat_enum.form f ON (fb.form_id = f.form_id)'
        . ' WHERE final_placement < 4'
        . ' AND picked_up_medal = false'
        . ' ORDER BY c.last_name, c.first_name, s.form_blowout_id, s.final_placement';

    $placementList = array();

    $conn = Db::connect();
    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $placementList[] = $row;
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
    <script type="text/javascript" src="../js/mootools.v1.00.js"></script>
    <script type="text/javascript" src="../js/html.js"></script>
    <script type="text/javascript" src="../js/cmat.js"></script>
    <script type="text/javascript" src="../js/ringside_console.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="../css/scoring.css"/>
  </head>
  <body>
    <h1>Placement</h1>
    <form action="placements.php">
      <input type="submit" name="save" value="Save!"/>
      <table>
        <thead>
          <tr><th>Competitor</th><th>Event</th><th>Place</th></tr>
        </thead>
        <tbody>
<?php
foreach ($placementList as $k => $v) {
    $trFormat = "<tr><td>%s</td><td>%s</td><td>%s</td></tr>\n";
    echo sprintf($trFormat,
        $v['last_name'] . ', ' . $v['first_name'] . '(' . $v['competitor_id'] . ')',
        $v['form_name'], $v['final_placement']);
}
?>
        </tbody>
      </table>
      <input type="submit" name="save" value="Save!"/>
    </form>
  </body>
</html>
