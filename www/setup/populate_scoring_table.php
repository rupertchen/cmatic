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
            . ' AND g.group_id = gm.group_id'
            . ' AND r.competitor_id = gm.member_id'
            . ' AND r.form_id = g.form_id'
            . ' AND fb.cmat_year = g.cmat_year'
            . ' AND fb.form_id = g.form_id'
            . ' AND fb.gender_id = 3'
            . ' AND fb.level_id = 0'
            . ' AND fb.age_group_id = 0';

        // Get the duplicated group scoring entries
        $q1b = 'SELECT scoring_id, group_id, form_blowout_id'
            . ' FROM cmat_annual.scoring'
            . ' WHERE group_id IS NOT NULL';

        // Get the duplicated group scoring entries we will keep
        $q1c = 'SELECT DISTINCT ON (form_blowout_id, group_id) scoring_id, group_id, form_blowout_id'
            . ' FROM cmat_annual.scoring'
            . ' WHERE group_id IS NOT NULL'
            . ' ORDER BY form_blowout_id, group_id';

        // Get form blowout that fits each push hands registration entry
        $q2 = 'SELECT r.registration_id, r.competitor_id, c.cmat_year, fb.form_blowout_id'
            . ' FROM cmat_annual.registration r, cmat_annual.competitor c, cmat_annual.form_blowout fb, cmat_enum.form f'
            . ' WHERE r.scoring_id IS NULL'
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

        // Create a new group event scoring row (per member, this needs to be merged)
        $p2 = 'INSERT INTO cmat_annual.scoring'
            . ' (cmat_year, group_id, form_blowout_id)'
            . ' VALUES'
            . ' (%d, %d, %d)';

        // Create a new push hands event scoring row
        $p3 = 'INSERT INTO cmat_annual.scoring'
            . ' (cmat_year, competitor_id, form_blowout_id)'
            . ' VALUES'
            . ' (%d, %d, %d)';

        // Point group reg entries to a single scoring row per group
        $p4 = 'UPDATE cmat_annual.registration'
            . ' SET scoring_id = %d'
            . ' WHERE scoring_id = %d';

        // Delete scoring row
        $p5 = 'DELETE FROM cmat_annual.scoring'
            . ' WHERE scoring_id = %d';


        // Sorted results
        $individualScoringList = array();
        $groupScoringList = array();
        $pushHandsScoringList = array();
        $dupeGroupScoringList = array();
        $dupeGroupScoringKeepList = array();


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
        Db::free_result($r);


        // Work
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

        // Get rid of dupes by first remapping, then killing of extraneous rows
        $r = Db::query($q1b);
        while ($row = Db::fetch_array($r)) {
            $dupeGroupScoringList[] = $row;
        }
        Db::free_result($r);
        $r = Db::query($q1c);
        while ($row = Db::fetch_array($r)) {
            $dupeGroupScoringKeepList[$row['form_blowout_id'] . '_' . $row['group_id']] = $row['scoring_id'];
        }
        Db::free_result($r);
        foreach ($dupeGroupScoringList as $k => $v) {
            $newScoringId = $dupeGroupScoringKeepList[$v['form_blowout_id'].'_'.$v['group_id']];
            $oldScoringId = $v['scoring_id'];
            if ($newScoringId != $oldScoringId) {
                Db::query(sprintf($p4, $newScoringId, $oldScoringId));
                Db::query(sprintf($p5, $oldScoringId));
            }
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
      <ul>
        <li><?php echo count($dupeGroupScoringList); ?> potential duplicate group scoring entries merged into <?php echo count($dupeGroupScoringKeepList); ?></li>
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
