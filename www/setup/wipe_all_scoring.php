<?php
    include '../inc/php_header.inc';

    require_once 'util/Db.php';

    // Check request
    $doWipe = isset($_REQUEST['wipe']);

    // Create stuff
    if ($doWipe) {
        // Queries
        // Wipe event table
        $q0 = 'UPDATE cmat_annual.event SET'
            . ' is_done = false';
        // Wipe scoring table
        $q1 = 'UPDATE cmat_annual.scoring SET'
            . ' ring_leader = null'
            . ', head_judge = null'
            . ', score_0 = null'
            . ', score_1 = null'
            . ', score_2 = null'
            . ', score_3 = null'
            . ', score_4 = null'
            . ', score_5 = null'
            . ', judge_0 = null'
            . ', judge_1 = null'
            . ', judge_2 = null'
            . ', judge_3 = null'
            . ', judge_4 = null'
            . ', judge_5 = null'
            . ', time = null'
            . ', merited_score = null'
            . ', time_deduction = null'
            . ', other_deduction = null'
            . ', final_score = null'
            . ', final_placement = null'
            . ', scored_at = null';


        // Database calls
        $conn = Db::connect();
        Db::query('BEGIN');
        Db::query($q0);
        Db::query($q1);
        Db::query('COMMIT');
        Db::close($conn);
    }

    include '../inc/php_footer.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Wipe Scoring Entries</title>
  </head>
  <body>
    <h1>Wipe Scoring Entries</h1>
<?php if ($doWipe) { ?>
    <p>
      Done!
    </p>
<?php } else { ?>
    <p>
      This will wipe <strong>all</strong> scoring entries from the database.
      You probably <strong>do not want to do this</strong>.
    </p>
    <form action="" method="post">
      <input type="submit" name="wipe" value="Wipe Scoring"/>
    </form>
<?php } ?>
  </body>
</html>
