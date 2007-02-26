<?php
    include '../inc/php_header.inc';


    // Imports
    require_once 'util/Db.php';


    // Request parameters
    $competitorId = $_REQUEST['competitor_id'];
    $isNew = 0 == strlen($competitorId);
    $regList = isset($_REQUEST['reg']) ? $_REQUEST['reg'] : array();
    $previousRegList = isset($_REQUEST['previousReg']) ? $_REQUEST['previousReg'] : array();

    $regInsert = array_diff($regList, $previousRegList);
    $regUpdate = array_diff($regList, $regInsert);


    // Db stuff
    $conn = Db::connect();
    Db::query('BEGIN');

    // Upsert competitor information
    $p0 = null;
    $q0 = null;
    if ($isNew) {
        // Insert new competitor
        $p0 = 'INSERT INTO cmat_annual.competitor ('
            . 'cmat_year, first_name, last_name, birthdate, gender_id, level_id, age_group_id, email, registration_date_id, registration_type_id, submission_format_id, payment_method_id'
            . ') VALUES ('
            . "15, '%s', '%s', '%s', %d, %d, %d, %s, %d, %d, %d, %d"
            . ')';
    } else {
        // Update
        $p0 = 'UPDATE cmat_annual.competitor SET'
            . " first_name = '%s', last_name='%s', birthdate='%s', gender_id=%d, level_id=%d, age_group_id=%d, email='%s', registration_date_id=%d, registration_type_id=%d, submission_format_id=%d, payment_method_id=%d"
            . " WHERE competitor_id = '$competitorId'";
    }
    $q0 = sprintf($p0, $_REQUEST['first_name'], $_REQUEST['last_name'], $_REQUEST['birthdate_year'].'-'.$_REQUEST['birthdate_month'].'-'.$_REQUEST['birthdate_date'], $_REQUEST['gender_id'], $_REQUEST['level_id'], $_REQUEST['age_group_id'], $_REQUEST['email'], $_REQUEST['registration_date_id'], $_REQUEST['registration_type_id'], $_REQUEST['submission_format_id'], $_REQUEST['payment_method_id']);
    Db::query($q0);

    if ($isNew) {
	$q0a = "SELECT currval('cmat_annual.competitor_competitor_id_seq') AS competitor_id";
	$r = Db::query($q0a);
	$row = Db::fetch_array($r);
	$competitorId = $row['competitor_id'];
    }

    // Delete removed registrations
    //
    // HACK: This is weird, but if regList is empty, then all of
    // registrations for the competitor should be deleted. Doing
    // a NOT IN of a set that doesn't exist will do just that.
    // No forms have the id -1, so this effectively makes gets
    // us the query we want.
    $regList2 = (count($regList) > 0) ? $regList : array(-1);
    $p1 = 'DELETE FROM cmat_annual.registration'
        . ' WHERE competitor_id = %d'
        . ' AND form_id NOT IN (%s)';
    $q1 = sprintf($p1, $competitorId, implode(',', $regList2));
    Db::query($q1);


    // Insert registrations
    if (count($regInsert) > 0) {
        $regInsertRows = array();
        foreach ($regInsert as $k => $v) {
            $isPaidVal = ('t' == $_REQUEST['isPaid_' . $v]) ? 'TRUE' : 'FALSE';
            $regInsertRows[] = implode(', ', array('SELECT ' . $competitorId, $v, $isPaidVal));
        }
        $q2 = 'INSERT INTO cmat_annual.registration (competitor_id, form_id, is_paid) '
            . implode(' UNION ', $regInsertRows);
        Db::query($q2);
    }


    // Update registrations
    if (count($regUpdate) > 0) {
        $regUpdatePaidRows = array();
        $regUpdateUnpaidRows = array();
        foreach ($regUpdate as $k => $v) {
            if ('t' == $_REQUEST['isPaid_' . $v]) {
                $regUpdatePaidRows[] = $v;
            } else {
                $regUpdateUnpaidRows[] = $v;
            }
        }
        // Update for paid registrations
        if (count($regUpdatePaidRows) > 0) {
            $p3 = 'UPDATE cmat_annual.registration SET is_paid = TRUE WHERE competitor_id = %d AND form_id IN (%s)';
            $q3 = sprintf($p3, $competitorId, implode(', ', $regUpdatePaidRows));
            Db::query($q3);
        }

        // Update for unpaid registrations
        if (count($regUpdateUnpaidRows) > 0) {
            $p4 = 'UPDATE cmat_annual.registration SET is_paid = FALSE WHERE competitor_id = %d AND form_id IN (%s)';
            $q4 = sprintf($p4, $competitorId, implode(', ', $regUpdateUnpaidRows));
            Db::query($q4);
        }
    }

    Db::query('COMMIT');
    Db::close($conn);
    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
  </head>
  <body>
    Competitor data saved!
<?php
    if (DEBUG_MODE) {
	print("<h1>Debug Info</h1>\n");
        print("<pre>\n");
        print_r($_REQUEST);
        print("Reg List");
        print_r($regList);
        print("Previous Reg List");
        print_r($previousRegList);
        print("Reg Insert");
        print_r($regInsert);
        print("Reg Update");
        print_r($regUpdate);
        print("q0: ".$q0."\n");
        print("q1: ".$q1."\n");
        print("q2: ".$q2."\n");
        print("q3: ".$q3."\n");
        print("q4: ".$q4."\n");
        print("</pre>\n");
    }
?>
  </body>
</html>
