<?php

class FormBlowout {

    // Data
    var $d = array();


    function fillFromDbRow($r) {
        $this->d['form_blowout_id'] = $r['form_blowout_id'];
        $this->d['cmat_year'] = $r['cmat_year'];
        $this->d['form_id'] = $r['form_id'];
        $this->d['gender_id'] = $r['gender_id'];
        $this->d['level_id'] = $r['level_id'];
        $this->d['age_group_id'] = $r['age_group_id'];
        $this->d['event_id'] = $r['event_id'];
        $this->d['ring_configuration_id'] = $r['ring_configuration_id'];
    }


    function addCompetitorCount($cc) {
        $this->d['competitor_count'] = $cc;
    }


    function getData($fb) {
        return $fb->d;
    }
}

?>
