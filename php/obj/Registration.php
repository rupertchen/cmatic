<?php

class Registration {

    // Data
    var $d = array();

    function fillFromDbRow($r) {
        $this->d['registration_id'] = $r['registration_id'];
        $this->d['competitor_id'] = $r['competitor_id'];
        $this->d['form_id'] = $r['form_id'];
        $this->d['is_paid'] = $r['is_paid'];
    }

    function getData($r) {
        return $r->d;
    }
}

?>
