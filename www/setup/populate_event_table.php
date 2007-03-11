<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';

    // Check request
    $doCreate = isset($_REQUEST['create']);

    // Create stuff
    if ($doCreate) {
        // Queries
        // Get all form blow outs that do not have a real event
        $q0 = 'SELECT form_blowout_id FROM cmat_annual.form_blowout WHERE event_id = 0';
        // Create a new event
        $p1 = 'INSERT INTO cmat_annual.event'
            . ' (cmat_year, event_order, event_code) VALUES'
            . " (15, -1, '%s')";
        // Associate a form blow out to the last created event
        $p2 = 'UPDATE cmat_annual.form_blowout'
            . " SET event_id = currval('cmat_annual.event_event_id_seq')"
            . "WHERE form_blowout_id = '%s'";


        // Sorted results
        $formBlowoutList = array();


        // Database calls
       $conn = Db::connect();
        $r = Db::query($q0);
        while ($row = Db::fetch_array($r)) {
            $formBlowoutList[] = $row['form_blowout_id'];
        }
        Db::free_result($r);


        // Work
        // For every form blow out entry without a real event,
        // create a new event and link the form blow out entry
        // to it.
        Db::query('BEGIN');
        foreach ($formBlowoutList as $k => $formBlowOutId) {
            Db::query(sprintf($p1, $formBlowOutId));
            Db::query(sprintf($p2, $formBlowOutId));
        }
        Db::query('COMMIT');
        Db::close($conn);
    }

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Populate Event Table</title>
  </head>
  <body>
    <h1>Populate Event Table</h1>
<?php if ($doCreate) { ?>
    <p>
      Done!
      <?php echo count($formBlowoutList) ?> entries were added.
    </p>
<?php } else { ?>
    <p>
      Every row in the table <code>cmat_annual.form_blowout</code> should refer to an entry in <code>cmat_annual.event</code> other than the special temporary entry.
      This script will create an event entry for every form_blowout that needs one.
    </p>
    <form action="" method="post">
      <input type="submit" name="create" value="Create Entries"/>
    </form>
<?php } ?>
  </body>
</html>
