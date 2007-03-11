<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';


    // Queries
    // Get registration rows that are missing a corresponding scoring row
    $q0 = 'SELECT * FROM cmat_annual.registration WHERE is_paid = true AND scoring_id IS NULL';

    // Get registration rows that have a scoring row, but maybe shouldn't
    $q1 = 'SELECT * FROM cmat_annual.registration WHERE is_paid = false AND scoring_id IS NOT NULL';


    // Sorted results
    $paidRegNotInScoring = array();
    $unpaidRegInScoring = array();


    // Database calls
    $conn = Db::connect();

    $r = Db::query($q0);
    while ($row = Db::fetch_array($r)) {
        $paidRegNotInScoring[$row['registration_id']] = array($row['competitor_id'], $row['form_id']);
    }
    Db::free_result($r);

    $r = Db::query($q1);
    while ($row = Db::fetch_array($r)) {
        $unpaidRegInScoring[$row['registration_id']] = array($row['competitor_id'], $row['form_id']);
    }
    Db::free_result($r);

    Db::close($conn);


    // Checks
    $fail0 = array();
    $p0 = 'Competitor %d has paid for form %d, but is not in the scoring table';
    foreach ($paidRegNotInScoring as $registrationId => $v) {
        $fail0[] = sprintf($p0, $v[0], $v[1]);
    }

    $fail1 = array();
    $p1 = 'Competitor %d has not paid for form %d, but is in the scoring table';
    foreach ($unpaidRegInScoring as $registrationId => $v) {
        $fail1[] = sprintf($p0, $v[0], $v[1]);
    }


    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Scrutiny: Check Scoring Entries</title>
  </head>
  <body>
    <h1>Scrutiny: Check Scoring Entries</h1>
    <h2>All paid registrations should have a scoring row.</h2>
<?php TextUtils::printFailures($fail0); ?>
    <h2>Unpaid registrations should not have a scoring row (usually).</h2>
<?php TextUtils::printFailures($fail1); ?>
  </body>
</html>
