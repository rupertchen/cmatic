<?php

class Group {

    // Data
    var $d = array('member'=>array());


    function fillFromDbRow($r) {
        $this->d['cmat_year'] = $r['cmat_year'];
        $this->d['group_id'] = $r['group_id'];
        $this->d['name'] = $r['name'];
        $this->d['form_id'] = $r['form_id'];
    }


    function addMember($m) {
        $this->d['member'][] = $m->d;
    }


    function getData($g) {
        return $g->d;
    }
}

?>
