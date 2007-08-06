<?php
include '../inc/php_header.inc';

// Imports
require_once 'util/Db.php';
require_once 'util/Json.php';
require_once 'obj/Competitor.php';
require_once 'obj/Registration.php';

// Request parameters
$competitorId = $_REQUEST['c'];

// Fetch data
$q0 = null;
$q1 = null;
$CMAT_YEAR = $conf['CMAT_YEAR'];
if (strlen($competitorId) == 0) {
    // Get all competitors
    $q0 = 'SELECT *'
        . ' FROM cmat_annual.competitor'
        . ' WHERE cmat_year = ' . $CMAT_YEAR
        . ' ORDER BY last_name, first_name';
    $q1 = 'SELECT *'
        . ' FROM cmat_annual.registration'
        . ' WHERE cmat_year = ' . $CMAT_YEAR
        . ' ORDER BY form_id';
} else {
    // Get a specific competitor
    $q0 = "SELECT * FROM cmat_annual.competitor WHERE competitor_id = '$competitorId' AND cmat_year = $CMAT_YEAR ORDER BY last_name, first_name";
    $q1 = "SELECT * FROM cmat_annual.registration WHERE competitor_id = '$competitorId' AND cmat_year = $CMAT_YEAR ORDER BY form_id";
}
$competitorSet = array();
$registrationList = array();

$conn = Db::connect();

// Collect competitors
$r = Db::query($q0);
while ($row = Db::fetch_array($r)) {
    $tmp = new Competitor();
    $tmp->fillFromDbRow($row);
    $competitorSet[$row['competitor_id']] = $tmp;
}
Db::free_result($r);

// Collect registrations
$r = Db::query($q1);
while ($row = Db::fetch_array($r)) {
    $tmp = new Registration();
    $tmp->fillFromDbRow($row);
    $registrationList[] = $tmp;
}
Db::free_result($r);

Db::close($conn);

// Add registrations to competitors
foreach ($registrationList as $k => $v) {
    $competitorSet[$v->d['competitor_id']]->addRegistration($v);
}

$competitorList = array();
foreach ($competitorSet as $k => $v) {
    $competitorList[] = $v;
}

header('Content-type: text/plain');
include '../inc/php_footer.inc';

echo Json::encode(array_map(array('Competitor', 'getData'), $competitorList));
?>
