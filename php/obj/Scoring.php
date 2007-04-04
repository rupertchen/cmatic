<?php

class Scoring {

    // Data
    var $d = null;


    function fillFromDbRow($r) {
        $this->d['scoring_id'] = $r['scoring_id'];
        $this->d['cmat_year'] = $r['cmat_year'];
        $this->d['competitor_id'] = $r['competitor_id'];
        $this->d['competitor_first_name'] = $r['competitor_first_name'];
        $this->d['competitor_last_name'] = $r['competitor_last_name'];
        $this->d['group_id'] = $r['group_id'];
        $this->d['group_name'] = $r['group_name'];
        $this->d['form_blowout_id'] = $r['form_blowout_id'];
        $this->d['judge_0'] = $r['judge_0'];
        $this->d['judge_1'] = $r['judge_1'];
        $this->d['judge_2'] = $r['judge_2'];
        $this->d['judge_3'] = $r['judge_3'];
        $this->d['judge_4'] = $r['judge_4'];
        $this->d['judge_5'] = $r['judge_5'];
        $this->d['score_0'] = $r['score_0'];
        $this->d['score_1'] = $r['score_1'];
        $this->d['score_2'] = $r['score_2'];
        $this->d['score_3'] = $r['score_3'];
        $this->d['score_4'] = $r['score_4'];
        $this->d['score_5'] = $r['score_5'];
        $this->d['time'] = $r['time'];
        $this->d['time_deduction'] = $r['time_deduction'];
        $this->d['other_deduction'] = $r['other_deduction'];
        $this->d['final_score'] = $r['final_score'];
        $this->d['final_placement'] = $r['final_placement'];
        $this->d['scored_at'] = $r['scored_at'];
        $this->d['picked_up_medal'] = $r['picked_up_medal'];
        $this->d['merited_score'] = $r['merited_score'];
        $this->d['ring_leader'] = $r['ring_leader'];
        $this->d['head_judge'] = $r['head_judge'];
        $this->d['is_dropped'] = $r['is_dropped'];
    }


    function getData($s) {
        return $s->d;
    }
}

?>
