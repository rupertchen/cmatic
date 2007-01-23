<?php

require_once 'db/BaseDbObj.php';
require_once 'db/JudgeAvailabilityObj.php';
require_once 'db/JudgeStyleMatrixObj.php';
require_once 'html/CheckboxArrayElem.php';
require_once 'html/InputElem.php';
require_once 'html/SelectElem.php';
require_once 'util/Db.php';
require_once 'util/TextUtils.php';

class JudgeObj extends BaseDbObj {

  var $judge_id;

  var $contact_id;
  var $school_affiliation_id;
  var $school_affiliation_name;
  var $formal_name;
  var $use_chinese_name;
  var $preferred_language;
  var $vip;
  var $food_restrictions__special;
  var $food_restrictions__vegetarian;
  var $food_restrictions__vegan;
  var $food_restriction_special;
  var $transportation__special;
  var $transportation__airport;
  var $transportation__cmat;
  var $transportation_special;
  var $parking_pass__wants;
  var $parking_pass__gets;

  var $formal_name_generated;

  var $judge_availability_list;
  var $judge_style_matrix_list;


  function getOneFromJudgeId($id) {
    $j = JudgeObj::getFromSomeId('judge_id', $id);
    return $j[0];
  }


  function getOneFromContactId($id) {
    $j = JudgeObj::getFromSomeId('contact_id', $id);
    return $j[0];
  }


  function getFromSomeId($fieldName, $id) {
    $q = sprintf("SELECT j.*, s.name as school_affiliation_name,"
                 . " c.salutation as contact_salutation,"
                 . " c.first_name as contact_first_name,"
                 . " c.middle_name as contact_middle_name,"
                 . " c.last_name as contact_last_name"
                 . " FROM cmat_core.judge j, cmat_core.school s,"
                 . " cmat_core.contact c"
                 . " WHERE j.%s = '%s'"
                 . " AND j.school_affiliation_id = s.school_id"
                 . " AND j.contact_id = c.contact_id;",
                 TextUtils::cleanTextForSql($fieldName),
                 TextUtils::cleanTextForSql($id));
    $r = Db::query($q);
    $js = array();
    while ($row = Db::fetch_array($r)) {
      $j = new JudgeObj();
      $j->loadFromArray($row);
      $j->loadFromDb = true;
      $js[] = $j;
    }
    Db::free_result($r);
    return $js;
  }


  function loadFromPostArray($p) {
    $frSpecial = TextUtils::postVarToString($p['food_restrictions__special']);
    $frVegetarian = TextUtils::postVarToString($p['food_restrictions__vegetarian']);
    $frVegan = TextUtils::postVarToString($p['food_restrictions__vegan']);

    $tSpecial = TextUtils::postVarToString($p['transportation__special']);
    $tAirport = TextUtils::postVarToString($p['transportation__airport']);
    $tCmat = TextUtils::postVarToString($p['transportation__cmat']);

    $ppWants = TextUtils::postVarToString($p['parking_pass__wants']);
    $ppGets = TextUtils::postVarToString($p['parking_pass__gets']);

    $p['food_restrictions'] = $frSpecial . $frVegetarian . $frVegan;
    $p['transportation'] = $tSpecial . $tAirport . $tCmat;
    $p['parking_pass'] = $ppWants . $ppGets;

    $this->loadFromArray($p);

  }


  function loadFromArray($a) {
    $this->judge_id = $a['judge_id'];
    $this->contact_id = $a['contact_id'];
    $this->school_affiliation_id = $a['school_affiliation_id'];
    $this->school_affiliation_name = $a['school_affiliation_name'];
    $this->formal_name = $a['formal_name'];
    $this->use_chinese_name = $a['use_chinese_name'];
    if ('' == $this->formal_name) {
      $formalName = '';
      if ('t' == $this->use_chinese_name) {
        $formalName = sprintf('%s %s %s',
                              $a['contact_salutation'],
                              $a['contact_last_name'],
                              $a['contact_first_name']);
      } else {
        $formalName = sprintf('%s %s %s %s',
                              $a['contact_salutation'],
                              $a['contact_first_name'],
                              $a['contact_middle_name'],
                              $a['contact_last_name']);
      }
      $this->formal_name_generated = preg_replace('/\s+/i', ' ',trim($formalName));
    }
    $this->preferred_language = $a['preferred_language'];
    $this->vip = $a['vip'];
    list($this->food_restrictions__special,
         $this->food_restrictions__vegetarian,
         $this->food_restrictions__vegan)
      = TextUtils::bitsToArray($a['food_restrictions']);
    $this->food_restriction_special = $a['food_restriction_special'];
    list($this->transportation__special,
         $this->transportation__airport,
         $this->transportation__cmat)
      = TextUtils::bitsToArray($a['transportation']);
    $this->transportation_special = $a['transportation_special'];
    list($this->parking_pass__wants,
         $this->parking_pass__gets)
      = TextUtils::bitsToArray($a['parking_pass']);
  }


  function save() {
    $q_format = "";
    if ($this->loadFromDb) {
      $q_format = "UPDATE cmat_core.judge SET"
        . " contact_id = '%s',"
        . " school_affiliation_id = '%s',"
        . " formal_name = '%s',"
        . " use_chinese_name = '%s',"
        . " preferred_language = %d,"
        . " vip = '%s',"
        . " food_restrictions = B'%s',"
        . " food_restriction_special = '%s',"
        . " transportation = B'%s',"
        . " transportation_special = '%s',"
        . " parking_pass = B'%s'"
        . " WHERE judge_id = '"
        . TextUtils::cleanTextForSql($this->judge_id)
        . "';";
    } else {
      $q_format = "INSERT INTO cmat_core.judge ("
        . " judge_id,"
        . " contact_id,"
        . " school_affiliation_id,"
        . " formal_name,"
        . " use_chinese_name,"
        . " preferred_language,"
        . " vip,"
        . " food_restrictions, food_restriction_special,"
        . " transportation, transportation_special,"
        . " parking_pass"
        . ") VALUES ("
        . " cmat_pl.next_key('a02'),"
        . " '%s',"
        . " '%s',"
        . " '%s',"
        . " '%s',"
        . " %d,"
        . " '%s',"
        . " B'%s', '%s',"
        . " B'%s', '%s',"
        . " B'%s'"
        . ");";
    }
    $q = sprintf($q_format,
                 $this->contact_id,
                 $this->school_affiliation_id,
                 $this->formal_name,
                 $this->use_chinese_name,
                 $this->preferred_language,
                 $this->vip,
                 $this->food_restrictions__special
                 . $this->food_restrictions__vegetarian
                 . $this->food_restrictions__vegan,
                 $this->food_restriction_special,
                 $this->transportation__special
                 . $this->transportation__airport
                 . $this->transportation__cmat,
                 $this->transportation_special,
                 $this->parking_pass__wants
                 . $this->parking_pass__gets);
    Db::query($q);


    $q_format = "SELECT judge_id FROM cmat_core.judge WHERE contact_id = '%s';";
    $q = sprintf($q_format, $this->contact_id);
    $r = Db::query($q);
    $row = Db::fetch_array($r);
    $this->judge_id = $row['judge_id'];
    Db::free_result($r);

    $availability = new JudgeAvailabilityObj();
    $availability->judge_id = $this->judge_id;
    $availability->contact_id = $this->contact_id;
    $availability->cmat_year = 15; // TODO: This should reference the current year.
    $availability->early_shift = 't';
    $availability->late_shift = 't';
    $availability->save();

    $styleMatrixTraditional = new JudgeStyleMatrixObj();
    $styleMatrixTraditional->judge_id = $this->judge_id;
    $styleMatrixTraditional->contact_id = $this->contact_id;
    $styleMatrixTraditional->style = '0';
    $styleMatrixTraditional->save();

    $styleMatrixContemporary = new JudgeStyleMatrixObj();
    $styleMatrixContemporary->judge_id = $this->judge_id;
    $styleMatrixContemporary->contact_id = $this->contact_id;
    $styleMatrixContemporary->style = '1';
    $styleMatrixContemporary->save();

    $styleMatrixInternal = new JudgeStyleMatrixObj();
    $styleMatrixInternal->judge_id = $this->judge_id;
    $styleMatrixInternal->contact_id = $this->contact_id;
    $styleMatrixInternal->style = '2';
    $styleMatrixInternal->save();
    
    $styleMatrixPushHands = new JudgeStyleMatrixObj();
    $styleMatrixPushHands->judge_id = $this->judge_id;
    $styleMatrixPushHands->contact_id = $this->contact_id;
    $styleMatrixPushHands->style = '3';
    $styleMatrixPushHands->save();
 
  }


  function getIdFieldName() {
    return "judge_id";
  }


  function getTypePrefix() {
    return "a02";
  }


  function getContactId() {
    return $this->contact_id;
  }


  function displayDetailTitle() {
    echo "<h1>Judge Details:</h1>";
  }


  function displayEditTitle() {
    echo "<h1>Judge Edit:</h1>";
  }


  function displayHtmlDetail() {
?>
<div class="judgeGroup group">
  <h2>Judge Details:</h2>
  <a href="d.php?t=<?php echo EDIT_PAGE; ?>&amp;id=<?php echo $this->judge_id; ?>">Edit</a>
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Formal Name:</th>
            <td><?php echo $this->htmlGet('formal_name'); ?></td>
          </tr>
          <tr>
            <th scope="row">School Affiliation:</th>
            <td><?php echo $this->htmlGet('school_affiliation_name'); ?></td>
          </tr>
          <tr>
            <th scope="row">VIP:</th>
            <td><?php echo $this->htmlGet('vip'); ?></td>
          </tr>
        </table>
      </div>
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Preferred Language:</th>
            <td><?php echo $this->htmlGet('preferred_language'); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="rightColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Food Restrictions:</th>
            <td><?php echo $this->htmlGet('food_restrictions'); ?></td>
          </tr>
          <tr>
            <th scope="row">Food Restriction Comments:</th>
            <td><?php echo $this->htmlGet('food_restriction_special'); ?></td>
          </tr>
        </table>
      </div>
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Parking Pass:</th>
            <td><?php echo $this->htmlGet('parking_pass'); ?></td>
          </tr>
          <tr>
            <th scope="row">Transportation:</th>
            <td><?php echo $this->htmlGet('transportation'); ?></td>
          </tr>
          <tr>
            <th scope="row">Transportation Comments:</th>
            <td><?php echo $this->htmlGet('transportation_special'); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="bottomRow clearingBox">
<?php
  JudgeAvailabilityObj::displayHtmlManyDetail($this->judge_availability_list);
  JudgeStyleMatrixObj::displayHtmlManyDetail($this->judge_style_matrix_list);
?>
    </div>
  </div>
</div>
<?php
  }


  function displayHtmlEdit() {
    $formalName = new InputElem('formal_name', $this->formal_name);
    $useChineseName = new SelectElem('use_chinese_name', array('f', 't'), array('No', 'Yes'), $this->use_chinese_name);
    $schoolAssoc = new LookupElem('school_affiliation', 'a04', $this->school_affiliation_id, $this->school_affiliation_name);
    $contactId = new InputElem('contact_id', $this->contact_id, 'hidden');
    $vip = new SelectElem('vip', array('f', 't'), array('No', 'Yes'), $this->vip);
    $prefLang = new SelectElem('preferred_language', array(0, 1, 2), array("English", "Mandarin", "Cantonese"), $this->preferred_language);
    $foodRestrict = new CheckboxArrayElem(array('food_restrictions__special', 'food_restrictions__vegetarian', 'food_restrictions__vegan'),
                                          array('Special', 'Vegetarian', 'Vegan'),
                                          array($this->food_restrictions__special, $this->food_restrictions__vegetarian, $this->food_restrictions__vegan));
    $parkPass = new CheckboxArrayElem(array('parking_pass__wants', 'parking_pass__gets'),
                                      array('Wants', 'Gets'),
                                      array($this->parking_pass__wants, $this->parking_pass__gets));
    $transport = new CheckboxArrayElem(array('transportation__special', 'transportation__airport', 'transportation__cmat'),
                                       array('Special', 'Airport', 'CMAT'),
                                       array($this->transportation__special, $this->transportation__airport, $this->transportation__cmat));

    $contactId->disp();
?>
<div class="judgeGroup group">
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Formal Name:</th>
            <td><?php $formalName->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Use Chinese Name:</th>
            <td><?php $useChineseName->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">School Affiliation:</th>
            <td><?php echo $schoolAssoc->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">VIP:</th>
            <td><?php $vip->disp(); ?></td>
          </tr>
        </table>
      </div>
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Preferred Language:</th>
            <td><?php $prefLang->disp(); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="rightColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Food Restrictions:</th>
            <td><?php $foodRestrict->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Food Restriction Comments:</th>
            <td><textarea name="food_restriction_special" cols="25" rows="3"><?php echo $this->food_restriction_special; ?></textarea></td>
          </tr>
        </table>
      </div>
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Parking Pass:</th>
            <td><?php $parkPass->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Transportation:</th>
            <td><?php $transport->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Transportation Comments:</th>
            <td>
              <textarea name="transportation_special" cols="25" rows="3"><?php echo $this->transportation_special; ?></textarea>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div class="bottomRow clearingBox"></div>
  </div>
</div> 
<?php
  }


  function htmlGet($field) {
    switch ($field) {
    case 'food_restrictions':
      $foods = array();
      if ($this->food_restrictions__special) {
        $foods[] = TextUtils::numToFoodRestrictions(0);
      }
      if ($this->food_restrictions__vegetarian) {
        $foods[] = TextUtils::numToFoodRestrictions(1);
      }
      if ($this->food_restrictions__vegan) {
        $foods[] = TextUtils::numToFoodRestrictions(2);
      }
      $ret = implode(", ", $foods);
      break;
    case 'formal_name':
      if ('' == $this->formal_name) {
        $ret = parent::htmlGet('formal_name_generated');
      } else {
        $ret = parent::htmlGet('formal_name');
      }
      break;
    case 'parking_pass':
      $parking = array();
      if ($this->parking_pass__wants) {
        $parking[] = "Wants";
      }
      if ($this->parking_pass__gets) {
        $parking[] = "Gets";
      }
      $ret = implode(", ", $parking);
      break;
    case 'preferred_language':
      $ret = TextUtils::numToLanguage($this->preferred_language);
      break;
    case 'transportation':
      $trans = array();
      if ($this->transportation__special) {
        $trans[] = "Special";
      }
      if ($this->transportation__airport) {
        $trans[] = "Pickup from airport";
      }
      if ($this->transportation__cmat) {
        $trans[] = "Ride to CMAT";
      }
      $ret = implode(", ", $trans);
      break;
    case 'vip':
      $ret = TextUtils::pgBooleanToText($this->vip);
      break;
    default:
      $ret = parent::htmlGet($field);
    }

    return $ret;
  }


  function loadChildrenFromDb() {
    $this->judge_availability_list = JudgeAvailabilityObj::getManyFromJudgeId($this->judge_id);
    $this->judge_style_matrix_list = JudgeStyleMatrixObj::getManyFromJudgeId($this->judge_id);
  }

}
?>
