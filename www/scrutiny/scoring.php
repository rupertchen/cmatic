<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';


    // Queries
    // Get registration rows that are missing a corresponding scoring row
    $q0 = 'SELECT * FROM cmat_annual.registration WHERE is_paid = true AND scoring_id IS NULL';

    // Get registration rows that have a scoring row, but maybe shouldn't
    $q1 = 'SELECT * FROM cmat_annual.registration WHERE is_paid = false AND scoring_id IS NOT NULL';

    // Get registration and scoring rows
    $q2 = 'SELECT r.competitor_id, r.form_id AS r_form_id, c.gender_id AS c_gender_id, fb.form_blowout_id, fb.form_id AS fb_form_id, fb.gender_id AS fb_gender_id'
        . ' FROM cmat_annual.registration r, cmat_annual.scoring s, cmat_annual.competitor c, cmat_annual.form_blowout fb'
        . ' WHERE r.competitor_id = c.competitor_id'
        . ' AND r.scoring_id = s.scoring_id'
        . ' AND s.form_blowout_id = fb.form_blowout_id';


    // Sorted results
    $paidRegNotInScoring = array();
    $unpaidRegInScoring = array();
    $regAndRelatedScoring = array();


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

    $r = Db::query($q2);
    while ($row = Db::fetch_array($r)) {
        $regAndRelatedScoring[] = $row;
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

    $fail2 = array();
    $p2a = 'Form mismatch: Competitor %d is registered for form %d, but is placed in form %d (in scoring table).';
    $p2b = 'Gender mismatch: Competitor %d is registered for form %d, but the competitor is gender %d when the form_blowout is gender %d';
    foreach ($regAndRelatedScoring as $k => $v) {
        if ($v['r_form_id'] != $v['fb_form_id']) {
            $fail2[] = sprintf($p2a, $v['competitor_id'], $v['r_form_id'], $v['fb_form_id']);
        }
        if ($v['fb_gender_id'] != 3 && $v['c_gender_id'] != $v['fb_gender_id']) {
            $fail2[] = sprintf($p2b, $v['competitor_id'], $v['r_form_id'], $v['c_gender_id'], $v['fb_gender_id']);
        }
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
    <h2>Competitors should be placed in events that match their registration and gender.</h2>
<?php TextUtils::printFailures($fail2); ?>
  </body>
</html>
