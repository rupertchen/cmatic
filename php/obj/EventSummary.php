<?php

class EventSummary {

    // Data
    var $d = array('form_blowout'=>array());


    function fillFromDbRow($r) {
        $this->d['event_id'] = $r['event_id'];
        $this->d['cmat_year'] = $r['cmat_year'];
        $this->d['event_code'] = $r['event_code'];
        $this->d['ring_id'] = $r['ring_id'];
        $this->d['event_order'] = $r['event_order'];
        $this->d['is_done'] = $r['is_done'];
        $this->d['finalized_at'] = $r['finalized_at'];
    }


    function addFormBlowout($fb) {
        $this->d['form_blowout'][] = $fb->d;
    }


    function getData($es) {
        return $es->d;
    }
}

?>
