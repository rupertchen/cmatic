<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';


    // Queries
    // Check for potential duplicate competitors (similar names with the same birthday)
    $q0 = 'SELECT c0.competitor_id AS competitor_id0, c1.competitor_id AS competitor_id1'
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
        $dupeCompetitors[] = $row;
    }
    Db::free_result($r);
    Db::close($conn);

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
    <h2>Competitors should be in as many groups as they are registered for group events.</h2>
<?php TextUtils::printFailures($fail0); ?>
<pre><?php print_r($dupeCompetitors); ?></pre>
  </body>
</html>
