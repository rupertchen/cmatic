<?php
    include '../inc/php_header.inc';

    // Imports
    require_once 'util/Db.php';
    require_once 'util/Json.php';

    // Request parameters
    $eventId = intval($_REQUEST['event_id']);
    $ringConfigurationId = intval($_REQUEST['ring_configuration_id']);

    // Queries
    // Mark the event as done
    $q0a = 'UPDATE cmat_annual.event'
        . ' SET is_done = true'
        . " WHERE event_id = $eventId";
    $q0b = 'UPDATE cmat_annual.event'
        . ' SET finalized_at = CURRENT_TIMESTAMP'
        . " WHERE event_id = $eventId"
        . ' AND finalized_at IS NULL';
    // Get scoring details for the event
    $q1 = 'SELECT s.*'
        . ' FROM cmat_annual.form_blowout fb'
        . ' INNER JOIN cmat_annual.scoring s ON (s.form_blowout_id = fb.form_blowout_id)'
        . " WHERE fb.event_id = $eventId";
    $p2 = 'UPDATE cmat_annual.scoring SET'
        . ' final_placement = %d'
        . ' WHERE scoring_id = %d';

    $rawScores = array();
    $finalPlacements = array();

    $conn = Db::connect();

    // Save data
    Db::query($q0a);
    Db::query($q0b);

    // Query for scores
    $r = Db::query($q1);
    while ($row = Db::fetch_array($r)) {
        $rawScores[$row['scoring_id']] = $row;
    }
    Db::free_result($r);

    // Compute placement
    if (7 != $ringConfigurationId) {
        $finalScores = array();
        $tb1s = array();
        $tb2s = array();
        $tb3s = array();
        $scoringIds = array();
        foreach ($rawScores as $k => $v) {
            if ('t' == $v['is_dropped']) {
                // Dropped people have all 0 scores as far as placement is concerned
                $scores = array(0);
                $v['final_score'] = 0;
            } else {
                // Just include scores that aren't 0 or null
                $scores = array();
                $dbColumns = array('score_0', 'score_1', 'score_2', 'score_3', 'score_4');
                foreach ($dbColumns as $k1 => $v1) {
                    $singleScore = $v[$v1];
                    if (0 != $singleScore) {
                        $scores[] = $singleScore;
                    }
                }
            }


            $min = min($scores);
            $max = max($scores);
            $avgOuter = ($min + $max) / 2;

            $finalScores[$k] = $v['final_score'];
            $tb1s[$k] = abs($avgOuter - $v['final_score']);
            $tb2s[$k] = $avgOuter;
            $tb3s[$k] = $min;
            $scoringIds[] = $v['scoring_id'];
        }
        array_multisort(
            $finalScores, SORT_DESC, SORT_NUMERIC,
            $tb1s, SORT_ASC, SORT_NUMERIC,
            $tb2s, SORT_DESC, SORT_NUMERIC,
            $tb3s, SORT_ASC, SORT_NUMERIC,
            $scoringIds, SORT_ASC, SORT_NUMERIC
        );

        // Handle placement and continued ties
        $prevScoreString = '';
        $prevPlace = 1;
        for ($i = 0; $i < count($scoringIds); $i++) {
            $currScoreString = $finalScores[$i] . '-' . $tb1s[$i] . '-' . $tb2s[$i] . '-' . $tb3s[$i];
            $currPlace = ($currScoreString == $prevScoreString) ? $prevPlace : ($i + 1);
            $finalPlacements[$scoringIds[$i]] = $currPlace;
            $prevPlace = $currPlace;
            $prevScoreString = $currScoreString;
        }
    } else {
        $finalScores = array();
        foreach ($rawScores as $k => $v) {
            $finalScores[$k] = $v['final_score'];
            $scoringIds[] = $v['scoring_id'];
        }
        array_multisort(
            $finalScores, SORT_DESC, SORT_NUMERIC,
            $scoringIds, SORT_ASC, SORT_NUMERIC
        );

        // Handle placement and continued ties
        $prevScoreString = '';
        $prevPlace = 1;
        for ($i = 0; $i < count($scoringIds); $i++) {
            $currScoreString = $finalScores[$i] . '';
            $currPlace = ($currScoreString == $prevScoreString) ? $prevPlace : ($i + 1);
            $finalPlacements[$scoringIds[$i]] = $currPlace;
            $prevPlace = $currPlace;
            $prevScoreString = $currScoreString;
        }
    }

    // Save placement
    Db::query('BEGIN');
    foreach ($finalPlacements as $scoringId => $place) {
        Db::query(sprintf($p2, $place, $scoringId));
    }
    Db::query('COMMIT');
    Db::close($conn);



    header('Content-type: text/plain');
    include '../inc/php_footer.inc';

    print("true");
?>
