<?php

require_once 'util/TextUtils.php';
require_once 'util/Json.php';

class Competitor {

    // Data
    var $d = array('registration'=>array());


    /**
     * Params:
     * $r - associative array of data
     */
    function fillFromDbRow($r) {
        $this->d['competitor_id'] = $r['competitor_id'];
        $this->d['cmat_year'] = $r['cmat_year'];
        $this->d['first_name'] = $r['first_name'];
        $this->d['last_name'] = $r['last_name'];
        $this->d['birthdate'] = $r['birthdate'];
        $this->d['gender_id'] = $r['gender_id'];
        $this->d['level_id'] = $r['level_id'];
        $this->d['age_group_id'] = $r['age_group_id'];
        $this->d['registration_date_id'] = $r['registration_date_id'];
        $this->d['registration_type_id'] = $r['registration_type_id'];
        $this->d['submission_format_id'] = $r['submission_format_id'];
    }


    /**
     * Params:
     * $f - id of a form
     */
    function addRegistration($r) {
/*
	print("********\n");
	print("\tAdd!\n");
	print_r($r->d);
	print("\tCurrent Reg\n");
	print_r($this->d['registration']);
*/
        $this->d['registration'][] = $r->d;
/*
	print("\tNew Reg\n");
	print_r($this->d['registration']);
*/
    }


    /**
     *
     */
    function getData($c) {
        return $c->d;
    }
}

?>
