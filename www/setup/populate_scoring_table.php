<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';

    // Check request
    $doCreate = isset($_REQUEST['create']);

    // Create stuff
    if ($doCreate) {
        // Queries
        // Get form blow out that fits each registration entry
	$q0 = 'SELECT r.registration_id, r.competitor_id, c.cmat_year, fb.form_blowout_id, c.level_id, c.gender_id, c.age_group_id, fb.age_group_id, r.form_id'
	    . ' FROM cmat_annual.registration r, cmat_annual.competitor c, cmat_annual.form_blowout fb'
	    . ' WHERE r.scoring_id is null'
            . ' AND c.cmat_year = 15'
            . ' AND r.competitor_id = c.competitor_id'
            . ' AND fb.cmat_year = c.cmat_year'
            . ' AND fb.form_id = r.form_id'
            . ' AND fb.gender_id = c.gender_id'
            . ' AND fb.level_id = c.level_id'
            . ' AND (fb.age_group_id = c.age_group_id OR (c.age_group_id = 8 AND fb.age_group_id in (4, 5)) OR (c.age_group_id = 9 AND fb.age_group_id in (5, 6)) OR (c.age_group_id = 10 AND fb.age_group_id in (6, 7)))';
        // Create a new scoring row
        $p1 = 'INSERT INTO cmat_annual.scoring'
            . ' (cmat_year, competitor_id, form_blowout_id)'
            . ' VALUES'
            . ' (%d, %d, %d)';
        // Link registration row to new scoring row
        $p2 = 'UPDATE cmat_annual.registration'
            . " SET scoring_id = currval('cmat_annual.scoring_scoring_id_seq')"
            . ' WHERE registration_id = %d';


        // Sorted results
        $individualScoringList = array();


        // Database calls
        $conn = Db::connect();
        $r = Db::query($q0);
        while ($row = Db::fetch_array($r)) {
            $individualScoringList[] = $row;
        }
        Db::free_result($r);


        // Work
        // For every event, its name will be built based on what
        // characteristics it has.
        Db::query('BEGIN');
        foreach ($individualScoringList as $k => $v) {
            // Create scoring row
	    Db::query(sprintf($p1, $v['cmat_year'], $v['competitor_id'], $v['form_blowout_id']));
            // Link registration row to scoring row
            Db::query(sprintf($p2, $v['registration_id']));
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
    <title>Populate Scoring Table</title>
  </head>
  <body>
    <h1>Populate Scoring Table</h1>
<?php if ($doCreate) { ?>
    <p>
      Done!
      <?php echo count($individualScoringList); ?> Individual scoring rows added.
      <pre><?php print_r($individualScoringList);?></pre>
    </p>
<?php } else { ?>
    <p>
      There is one scoring entry for every entity (competitor or group) that gets scored in some event.
      For individual events, there is exactly one scoring entry per registration entry.
      For group events, there is exactly one scoring entry per group.
    </p>
    <form action="" method="post">
      <input type="submit" name="create" value="Populate Scoring Table"/>
    </form>
<?php } ?>
  </body>
</html>
