<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';

    // Check request
    $doReset = isset($_REQUEST['reset']);

    // Create stuff
    if ($doReset) {
        // Queries
        // Get all the form_blowout information
        $q0 = 'SELECT * FROM cmat_annual.form_blowout WHERE cmat_year = 15 ORDER BY event_id';
	$p1 = "UPDATE cmat_annual.event SET event_code = '%s' WHERE event_id = %d";


        // Sorted results
        $eventList = array();


        // Database calls
        $conn = Db::connect();
        $r = Db::query($q0);
        while ($row = Db::fetch_array($r)) {
            $eventId = $row['event_id'];
            if (!isset($eventList[$eventId])) {
                $eventListList[$eventId] = array();
            }
            $eventList[$eventId][] = $row;
        }
        Db::free_result($r);


        // Work
        // For every event, its name will be built based on what
        // characteristics it has.
        Db::query('BEGIN');
        foreach ($eventList as $eventId => $v) {
            $tmpDivisions = array();
            $tmpGenders = array();
            $tmpAgeGroups = array();
            $tmpForms = array();
            foreach ($v as $k => $v2) {
                $tmpDivisions[] = $v2['level_id'];
                $tmpGenders[] = $v2['gender_id'];
                $tmpAgeGroups[] = $v2['age_group_id'];
                $tmpForms[] = $v2['form_id'];
            }
            $tmpDivisions = array_unique($tmpDivisions);
            $tmpGenders = array_unique($tmpGenders);
            $tmpAgeGroups = array_unique($tmpAgeGroups);
            $tmpForms = array_unique($tmpForms);
            $newEventCode = TextUtils::makeEventCode($tmpDivisions, $tmpGenders, $tmpAgeGroups, $tmpForms);
	    Db::query(sprintf($p1, $newEventCode, $eventId));
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
    <title>Reset Event Codes</title>
  </head>
  <body>
    <h1>Reset Event Codes</h1>
<?php if ($doReset) { ?>
    <p>
      Done!
      <?php echo count($eventList) ?> event codes were reset.
    </p>
<?php } else { ?>
    <p>
      Event codes uniquely identify an event.
      It should encode information regarding the event such as gender(s), age group(s), division, form.
      This will reset all the event codes of this year's events to the defaults (and there really isn't a good reason to not use the defaults).
    </p>
    <form action="" method="post">
      <input type="submit" name="reset" value="Reset Event Codes"/>
    </form>
<?php } ?>
  </body>
</html>
