<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';
    require_once 'util/TextUtils.php';

    // Check request
    $doCreate = isset($_REQUEST['create']);

    // Create stuff
    if ($doCreate) {
        // Queries
        // Get form blowout that fits each individual event registration entry
	$q0 = 'SELECT r.registration_id, r.competitor_id, c.cmat_year, fb.form_blowout_id'
	    . ' FROM cmat_annual.registration r, cmat_annual.competitor c, cmat_annual.form_blowout fb, cmat_enum.form f'
	    . ' WHERE r.scoring_id IS NULL'
            . ' AND r.is_paid = true'
            . ' AND c.cmat_year = 15'
            . ' AND f.is_group = false'
            . ' AND f.ring_configuration_id <> 1'
            . ' AND r.competitor_id = c.competitor_id'
            . ' AND r.form_id = f.form_id'
            . ' AND fb.cmat_year = c.cmat_year'
            . ' AND fb.form_id = r.form_id'
            . ' AND fb.gender_id = c.gender_id'
            . ' AND fb.level_id = c.level_id'
            . ' AND (fb.age_group_id = c.age_group_id OR (c.age_group_id = 8 AND fb.age_group_id in (4, 5)) OR (c.age_group_id = 9 AND fb.age_group_id in (5, 6)) OR (c.age_group_id = 10 AND fb.age_group_id in (6, 7)))';

        // Get form blowout that fits each group event registration entry
        $q1 = 'SELECT r.registration_id, g.group_id, g.cmat_year, fb.form_blowout_id'
            . ' FROM cmat_annual.registration r, cmat_annual."group" g, cmat_annual.group_member gm, cmat_annual.form_blowout fb'
            . ' WHERE g.cmat_year = 15'
            . ' AND r.scoring_id IS NULL'
            . ' AND r.is_paid = true'
            . ' AND g.group_id = gm.group_id'
            . ' AND r.competitor_id = gm.member_id'
            . ' AND r.form_id = g.form_id'
            . ' AND fb.cmat_year = g.cmat_year'
            . ' AND fb.form_id = g.form_id'
            . ' AND fb.gender_id = 3'
            . ' AND fb.level_id = 0'
            . ' AND fb.age_group_id = 0';

        // Get form blowout that fits each push hands registration entry
        $q2 = 'SELECT r.registration_id, r.competitor_id, c.cmat_year, fb.form_blowout_id'
            . ' FROM cmat_annual.registration r, cmat_annual.competitor c, cmat_annual.form_blowout fb, cmat_enum.form f'
            . ' WHERE r.scoring_id IS NULL'
            . ' AND r.is_paid = true'
            . ' AND f.ring_configuration_id = 1'
            . ' AND r.form_id = f.form_id'
            . ' AND r.competitor_id = c.competitor_id'
            . ' AND fb.cmat_year = c.cmat_year'
            . ' AND fb.form_id = r.form_id'
            . ' AND fb.gender_id = c.gender_id'
            . ' AND fb.level_id = 0'
            . ' AND fb.age_group_id = 0';

        // Create a new individual event scoring row
        $p0 = 'INSERT INTO cmat_annual.scoring'
            . ' (cmat_year, competitor_id, form_blowout_id)'
            . ' VALUES'
            . ' (%d, %d, %d)';

        // Link registration row to new scoring row
        $p1 = 'UPDATE cmat_annual.registration'
            . " SET scoring_id = currval('cmat_annual.scoring_scoring_id_seq')"
            . ' WHERE registration_id = %d';

        // Create a new group event scoring row
        $p2 = 'INSERT INTO cmat_annual.scoring'
            . ' (cmat_year, group_id, form_blowout_id)'
            . ' VALUES'
            . ' (%d, %d, %d)';

        // Create a new push hands event scoring row
        $p3 = 'INSERT INTO cmat_annual.scoring'
            . ' (cmat_year, competitor_id, form_blowout_id)'
            . ' VALUES'
            . ' (%d, %d, %d)';



        // Sorted results
        $individualScoringList = array();
        $groupScoringList = array();
        $pushHandsScoringList = array();


        // Database calls
        $conn = Db::connect();

        $r = Db::query($q0);
        while ($row = Db::fetch_array($r)) {
            $individualScoringList[] = $row;
        }
        Db::free_result($r);

        $r = Db::query($q1);
        while ($row = Db::fetch_array($r)) {
            $groupScoringList[] = $row;
        }
        Db::free_result($r);

	$r = Db::query($q2);
        while ($row = Db::fetch_array($r)) {
            $pushHandsScoringList[] = $row;
        }


        // Work
        // For every event, its name will be built based on what
        // characteristics it has.
        Db::query('BEGIN');
        foreach ($individualScoringList as $k => $v) {
            // Create scoring row
	    Db::query(sprintf($p0, $v['cmat_year'], $v['competitor_id'], $v['form_blowout_id']));
            // Link registration row to new scoring row
            Db::query(sprintf($p1, $v['registration_id']));
	}
        foreach ($groupScoringList as $k => $v) {
            // Create scoring row
            Db::query(sprintf($p2, $v['cmat_year'], $v['group_id'], $v['form_blowout_id']));
            // Link registration row to new scoring row
            Db::query(sprintf($p1, $v['registration_id']));
        }
        foreach ($pushHandsScoringList as $k => $v) {
            // Create scoring row
            Db::query(sprintf($p3, $v['cmat_year'], $v['competitor_id'], $v['form_blowout_id']));
            // Link registration row to new scoring row
            Db::query(sprintf($p1, $v['registration_id']));
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
      <ul>
        <li><?php echo count($individualScoringList); ?> individual scoring rows added.</li>
        <li><?php echo count($groupScoringList); ?> group scoring rows added.</li>
        <li><?php echo count($pushHandsScoringList); ?> push hands scoring rows added.</li>
      </ul>
    </p>
<?php } else { ?>
    <p>
      There is one scoring entry for every entity (competitor or group) that gets scored in some event.
      For individual events, there is exactly one scoring entry per registration entry.
      For group events, there is exactly one scoring entry per group.
    </p>
    <p>
      Only registration rows that are not already linked to a scoring row and have paid are populated.
    </p>
    <form action="" method="post">
      <input type="submit" name="create" value="Populate Scoring Table"/>
    </form>
<?php } ?>
  </body>
</html>
