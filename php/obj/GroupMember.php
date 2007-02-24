<?php

class GroupMember {

    // Data
    var $d = array();


    function fillFromDbRow($r) {
        $this->d['group_id'] = $r['group_id'];
        $this->d['member_id'] = $r['member_id'];
        $this->d['first_name'] = $r['first_name'];
        $this->d['last_name'] = $r['last_name'];
    }


    function getData($g) {
        return $g->d;
    }
}

?>
