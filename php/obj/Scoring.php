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
        $this->d['judge_00'] = $r['judge_00'];
        $this->d['judge_01'] = $r['judge_01'];
        $this->d['judge_02'] = $r['judge_02'];
        $this->d['judge_03'] = $r['judge_03'];
        $this->d['judge_04'] = $r['judge_04'];
        $this->d['judge_05'] = $r['judge_05'];
        $this->d['scoring_00'] = $r['scoring_00'];
        $this->d['scoring_01'] = $r['scoring_01'];
        $this->d['scoring_02'] = $r['scoring_02'];
        $this->d['scoring_03'] = $r['scoring_03'];
        $this->d['scoring_04'] = $r['scoring_04'];
        $this->d['scoring_05'] = $r['scoring_05'];
        $this->d['time'] = $r['time'];
        $this->d['time_deduction'] = $r['time_deduction'];
        $this->d['other_deduction'] = $r['other_deduction'];
        $this->d['final_score'] = $r['final_score'];
        $this->d['final_placement'] = $r['final_placement'];
        $this->d['scored_at'] = $r['scored_at'];
        $this->d['picked_up_medal'] = $r['picked_up_medal'];
        $this->d['tie_breaker'] = $r['tie_breaker'];
    }


    function getData($s) {
        return $s->d;
    }
}

?>
