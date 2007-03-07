<?php

class TextUtils {

  function cleanTextForSql($text) {
    return addslashes(trim($text));
  }


  function cleanEmail($email) {
    return strtolower(trim($email));
  }


  function htmlize($str) {
    return htmlentities($str, ENT_QUOTES, 'UTF-8');
  }


  function bitsToArray($str) {
    $l = strlen($str);
    $ret = array();
    for ($i = 0; $i < $l; $i++) {
      $ret[] = $str{$i};
    }
    return $ret;
  }


  function pgBooleanToText($b) {
    $ret;
    if ('t' == $b) {
      $ret = 'Yes';
    } else {
      $ret = 'No';
    }
    return $ret;
  }


  function numToLanguage($n) {
    switch ($n) {
    case 0:
      return "English";
    case 1:
      return "Mandarin";
    case 2:
      return "Cantonese";
    default:
      return "Error: Unknown";
    }
  }


  function numToFoodRestrictions($f) {
    switch ($f) {
    case 0:
      return "Special";
    case 1:
      return "Vegetarian";
    case 2:
      return "Vegan";
    default:
      return "Error: Unknown";
    }
  }


  function postVarToString($v) {
    return ($v) ? '1' : '0';
  }


  function makeFullName($last, $first, $middle) {
    $ret = sprintf('%s, %s %s', $last, $first, $middle);
    if (',' == trim($ret)) {
      $ret = '';
    }
    return $ret;
  }


  /*
   * Convert YNM (1:Yes, 0:No, 2:Maybe) integers to text.
   */
  function numToYnmText($n) {
    switch($n) {
    case 0:
      return 'No';
    case 1:
      return 'Yes';
    case 2:
      return 'Maybe';
    default:
      return 'N/A';
    }
  }


  function numToStyle($n) {
    switch($n) {
    case 0:
      return 'Traditional';
    case 1:
      return 'Contemporary';
    case 2:
      return 'Internal';
    case 3:
      return 'Push Hands';
    }
  }


  function numToLevel($n) {
    switch ($n) {
    case 0:
      return 'B';
    case 1:
      return 'I';
    case 2:
      return 'A';
    case 3:
      return 'X';
    }
  }


  function numToAge($style, $n) {
    $isInternal = 2 == $style;
    switch ($n) {
    case 0:
      return '<7';
    case 1:
      return '8-12';
    case 2:
      return '13-17';
    case 3:
      return ($isInternal) ? '18-52' : '18-35';
    case 4:
      return ($isInternal) ? '>53' : '>36';
    }
  }


    /**
     * Print out failure strings for HTML with pre tags.
     */
    function printFailures($failures) {
        if (count($failures) > 0) {
            echo "<pre>\n";
            foreach ($failures as $k => $v) {
                echo $v;
                echo "\n";
            }
            echo "</pre>\n";
        } else {
            echo "Passed.\n";
        }
    }


    /**
     * Convert division id to text
     */
    function convertDivisionIdToChar($id) {
        $m = array(0 => 'N', 1 => 'B', 2 => 'I', 3 => 'A');
        return $m[$id];
    }


    /**
     * Convert gender id to text
     */
    function convertGenderIdToChar($id) {
        $m = array(1 => 'M', 2 => 'F', 3 => 'C');
        return $m[$id];
    }


    /**
     * Convert age id to text
     */
    function convertAgeGroupIdToChar($id) {
        $m = array(
	    0 => 'N',
  	    1 => 'Y',
	    2 => 'C',
	    3 => 'T',
	    4 => 'A',
	    5 => 'A',
	    6 => 'S',
	    7 => 'S');
  	return $m[$id];
    }

    /**
     * Convert form id to text
     */
    function convertFormidToChar($id) {
        return str_pad($id, 2, '0', STR_PAD_LEFT);
    }


    /**
     * Create an event code based on event characteristics.
     */
    function makeEventCode($divisions, $genders, $ageGroups, $forms) {
        sort($divisions);
	sort($genders);
	sort($ageGroups);
	sort($forms);
        $cDivisions = array_map(array('TextUtils', 'convertDivisionIdToChar'), $divisions);
	$cGenders = array_map(array('TextUtils', 'convertGenderIdToChar'), $genders);
	$cAgeGroups = array_map(array('TextUtils', 'convertAgeGroupIdToChar'), $ageGroups);
	$cForms = array_map(array('TextUtils', 'convertFormIdToChar'), $forms);
        // If more than one gender, it must be combined
        if (count($cGenders) > 1) {
            $cGenders = array(convertGenderIdToChar(3));
        }
        return join('', $cDivisions) . join('', $cGenders) . join('', $cAgeGroups) . join('-', $cForms);
    }
}

?>

