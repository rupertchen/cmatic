<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';


    // Queries
    // Check for potential duplicate competitors (similar names with the same birthday)
    $q0 = 'SELECT'
        . ' c0.competitor_id AS competitor_id0, c0.first_name AS first_name0, c0.last_name AS last_name0,'
        . ' c1.competitor_id AS competitor_id1, c1.first_name AS first_name1, c1.last_name AS last_name1'
        . ' FROM cmat_annual.competitor c0, cmat_annual.competitor c1'
        . ' WHERE c0.competitor_id != c1.competitor_id'
        . ' AND c0.birthdate = c1.birthdate'
        . " AND (c0.last_name ILIKE '%' || c1.last_name || '%'"
        . " OR c0.first_name ILIKE '%' || c1.first_name || '%')";


    // Sorted results
    $dupeCompetitors = array();


    // Database calls
    $conn = Db::connect();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $c0 = array('id' => $row['competitor_id0'], 'first_name' => $row['first_name0'], 'last_name' => $row['last_name0']);
        $c1 = array('id' => $row['competitor_id1'], 'first_name' => $row['first_name1'], 'last_name' =>$row['last_name1']);
        if ($c0['id'] > $c1['id']) {
            // Reorder if necessary
            $t = $c0;
            $c0 = $c1;
            $c1 = $t;
        }
        $dupeCompetitors[$c0['id'] . $c1['id']] = array($c0, $c1);
    }
    Db::free_result($r);
    Db::close($conn);


    // Checks
    // Same birthday with similar first name or similar last name
    $fail0 = array();
    $p0 = 'Competitor %d and %d may be duplicates. [%s, %s] vs [%s, %s]';
    foreach ($dupeCompetitors as $k => $v) {
        $c0 = $v[0];
        $c1 = $v[1];
        $fail0[] = sprintf($p0, $c0['id'], $c1['id'], $c0['last_name'], $c0['first_name'], $c1['last_name'], $c1['first_name']);
    }

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Scrutiny: Check for Potential Duplicates</title>
  </head>
  <body>
    <h1>Scrutiny: Check for Potential Duplicates</h1>
    <h2>Competitors with the same birthdays and similar last name or similar first names might be dupes (or not).</h2>
<?php TextUtils::printFailures($fail0); ?>
  </body>
</html>
