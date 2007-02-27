<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';

    $recipient = $_REQUEST['r'];
    $competitorId = $_REQUEST['c'];

    // Competitor info
    $p0 = 'SELECT c.*, g.comment AS gender, l.comment AS level, ag.comment AS age_group'
        . ' FROM cmat_annual.competitor c, cmat_enum.gender g, cmat_enum.level l, cmat_enum.age_group ag'
        . ' WHERE c.competitor_id = %d'
        . ' AND c.gender_id = g.gender_id'
        . ' AND c.level_id = l.level_id'
        . ' AND c.age_group_id = ag.age_group_id';

    // Registration info
    $p1 = 'SELECT r.*, f.name AS form'
        . ' FROM cmat_annual.registration r, cmat_enum.form f'
        . ' WHERE r.competitor_id = %d'
        . ' AND r.form_id = f.form_id';

    // DB
    $cInfo = null;
    $rInfo = array();
    $conn = Db::connect();

    $r = Db::query(sprintf($p0, $competitorId));
    $cInfo = Db::fetch_array($r);
    Db::free_result($r);

    $r = Db::query(sprintf($p1, $competitorId));
    while ($row = Db::fetch_array($r)) {
        $regInfo[] = $row;
    }
    Db::free_result($r);

    Db::close($conn);

    $subjectPattern = '[cmat15] Confirmation for competitor %d - %s, %s';
    $messagePattern = 'This a confirmation that we have received a registration form for %s %s for CMAT15.'
        . ' If there are any errors please contact the registration committee at cmat15_registration@calwushu.com.'
        . ' Please note that competitors are not guaranteed a place in the tournament until we have received payment.'
        . "\n";
    $cDetailPattern = "\nFirst Name: %s\nLast Name: %s\nGender: %s\nLevel: %s\nAge Group: %s\n";
    $rDetailPattern = "Event: %s (%s)\n";

    $subject = sprintf($subjectPattern, $cInfo['competitor_id'], $cInfo['last_name'], $cInfo['first_name']);
    $message = sprintf($messagePattern, $cInfo['first_name'], $cInfo['last_name']);
    $cDetail = sprintf($cDetailPattern, $cInfo['first_name'], $cInfo['last_name'], $cInfo['gender'], $cInfo['level'], $cInfo['age_group']);
    $rDetails = array();
    foreach ($regInfo as $k => $v) {
        $paid = ($v['is_paid'] == 't') ? 'paid' : '**NOT PAID**';
        $rDetails[] = sprintf($rDetailPattern, $v['form'], $paid);
    }
    if (count($rDetails) == 0) {
        $rDetails[] = "Not registered for any events.\n";
    }
    $headers = "From: cmat15_registration@calwushu.com\r\n"
        . "Reply-To: cmat15_registration@calwushu.com\r\n"
        . "X-Mailer: PHP/" . phpversion();

    $entireMsg = $message . $cDetail . "\n" . join('', $rDetails);

    $sentMail = mail($recipient, $subject, $entireMsg, $headers);

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Mail Confirmation Status</title>
  </head>
  <body>
    <h1><?php
  if ($sentMail) {
    echo "Mail was successfully sent to $recipient";
  } else {
    echo "Mail failed to send";
  }
?></h1>
  <h2>Subject</h2>
<?php echo $subject; ?>
  <h2>Body</h2>
  <pre>
<?php print_r ($entireMsg); ?>
  </pre>
  </body>
</html>
