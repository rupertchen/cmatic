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
    $pJASU = "judge_%d = '%s', score_%d = %f";
    for ($i = 0; $i < $numJudges; $i++) {
        $judgesAndScoresUpdate[] = sprintf($pJASU, $i, $judges[$i], $i, $scores[$i]);
    }
    $p0 = 'UPDATE cmat_annual.scoring SET'
        . ' ' . implode(', ', $judgesAndScoresUpdate)
        . ", ring_leader = '$ringLeader'"
        . ", head_judge = '$headJudge'"
        . ', time = %f'
        . ', merited_score = %f'
        . ', time_deduction = %f'
        . ', other_deduction = %f'
        . ', final_score = %f'
        . ' WHERE scoring_id = %d';
    $p1 = 'UPDATE cmat_annual.scoring SET'
        . ' scored_at = CURRENT_TIMESTAMP'
        . ' WHERE scoring_id = %d'
        . ' AND scored_at IS NULL';
    $conn = Db::connect();
    $updateQuery = sprintf($p0, $time, $meritedScore, $timeDeduct, $otherDeduct, $finalScore, $scoringId);
    Db::query($updateQuery);
    Db::query(sprintf($p1, $scoringId));
    Db::close($conn);

    header('Content-type: text/plain');
    include '../inc/php_footer.inc';

    print("true");
?>
