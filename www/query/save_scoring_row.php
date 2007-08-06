<?php
    include '../inc/php_header.inc';

    // Imports
    require_once 'util/Db.php';
    require_once 'util/Json.php';

    // Request parameters
    $scoringId = intval($_REQUEST['scoring_id']);
    $numJudges = intval($_REQUEST['num_judges']);
    $judges = array();
    $scores = array();
    for ($i = 0; $i < $numJudges; $i++) {
        $judges[$i] = rawurldecode($_REQUEST['judge_' . strval($i)]);
        $scores[$i] = floatval($_REQUEST['score_' . strval($i)]);
    }
    $ringLeader = rawurldecode($_REQUEST['ring_leader']);
    $headJudge = rawurldecode($_REQUEST['head_judge']);
    $time = floatval($_REQUEST['time']);
    $meritedScore = floatval($_REQUEST['merited_score']);
    $timeDeduct = floatval($_REQUEST['time_deduction']);
    $otherDeduct = floatval($_REQUEST['other_deduction']);
    $finalScore = floatval($_REQUEST['final_score']);

    // Save data
    $judgesAndScoresUpdate = array();
    $pScoreUpdate = "score_%d = %f";
    for ($i = 0; $i < $numJudges; $i++) {
        // TODO: For now, we're never saving the judges info.
        // This should change so that we don't override initial settings?
        // Or perhaps, given certain defaults, they won't be overwritten?
        $scoresUpdate[] = sprintf($pScoreUpdate, $i, $scores[$i]);
    }
    $CMAT_YEAR = $conf['CMAT_YEAR'];
    $p0 = 'UPDATE cmat_annual.scoring SET'
        . ' ' . implode(', ', $scoresUpdate)
        . ", ring_leader = '$ringLeader'"
        . ", head_judge = '$headJudge'"
        . ', time = %f'
        . ', merited_score = %f'
        . ', time_deduction = %f'
        . ', other_deduction = %f'
        . ', final_score = %f'
        . ' WHERE scoring_id = %d'
        . " AND cmat_year = $CMAT_YEAR";
    $p1 = 'UPDATE cmat_annual.scoring SET'
        . ' scored_at = CURRENT_TIMESTAMP'
        . ' WHERE scoring_id = %d'
        . ' AND scored_at IS NULL'
        . " AND cmat_year = $CMAT_YEAR";
    $conn = Db::connect();
    $updateQuery = sprintf($p0, $time, $meritedScore, $timeDeduct, $otherDeduct, $finalScore, $scoringId);
    Db::query($updateQuery);
    Db::query(sprintf($p1, $scoringId));
    Db::close($conn);

    header('Content-type: text/plain');
    include '../inc/php_footer.inc';

    print("true");
?>
