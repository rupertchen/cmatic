<?php

require_once 'db/BaseDbObj.php';
require_once 'html/InputElem.php';
require_once 'html/SelectElem.php';
require_once 'util/Db.php';
require_once 'util/TextUtils.php';

class JudgeAvailabilityObj extends BaseDbObj {

  var $judge_availability_id;

  var $cmat_year;
  var $judge_id;
  var $contact_id;
  var $early_shift;
  var $late_shift;


  function getOneFromJudgeAvailabilityId($id) {
    $o = JudgeAvailabilityObj::getFromSomeId('judge_availability_id', $id);
    return $o[0];
  }


  function getManyFromJudgeId($id) {
    return JudgeAvailabilityObj::getFromSomeId('judge_id', $id);
  }


  function getFromSomeId($fieldName, $id) {
    $q = sprintf("SELECT * FROM cmat_annual.judge_availability WHERE %s = '%s' ORDER BY cmat_year DESC",
                 TextUtils::cleanTextForSql($fieldName),
                 TextUtils::cleanTextForSql($id));
    $r = Db::query($q);
    $list = array();
    while ($row = Db::fetch_array($r)) {
      $o = new JudgeAvailabilityObj();
      $o->loadFromArray($row);
      $o->loadFromDb = true;
      $list[] = $o;
    }
    Db::free_result($r);
    return $list;
  }


  function displayHtmlManyDetail($list) {
    echo '<div class="judgeAvailabilityGroup group">';
    echo '<h2>Judge Availability Details:</h2>';
    echo '<div class="details">';
    echo '<div class="bottomRow">';
    echo '<div class="subGroup">';
    echo '<table>';
    echo '<tr><th scope="col">Action</th><th scope="col">CMAT Year</th><th scope="col">Early Shift</th><th scope="col">Late Shift</th></tr>';
    foreach ($list as $k => $v) {
      echo sprintf('<tr><td><a href="d.php?t=%s&amp;id=%s">Edit</a></td><td>%s</td><td>%s</td><td>%s</td></tr>',
                   EDIT_PAGE,
                   $v->judge_availability_id,
                   $v->htmlGet('cmat_year'),
                   $v->htmlGet('early_shift'),
                   $v->htmlGet('late_shift'));
    }
    echo '</table></div></div></div></div>';
  }


  function loadFromArray($a) {
    $this->judge_availability_id = $a['judge_availability_id'];
    $this->cmat_year = $a['cmat_year'];
    $this->judge_id = $a['judge_id'];
    $this->contact_id = $a['contact_id'];
    $this->early_shift = $a['early_shift'];
    $this->late_shift = $a['late_shift'];
  }


  function save() {
    $q_format = null;
    if ($this->loadFromDb) {
      $q_format = 'UPDATE cmat_annual.judge_availability SET'
        . " judge_id = '%s',"
        . " contact_id = '%s',"
        . " cmat_year = %d,"
        . " early_shift = '%s',"
        . " late_shift = '%s'"
        . " WHERE judge_availability_id = '"
        . TextUtils::cleanTextForSql($this->judge_availability_id)
        . "';";
    } else {
      $q_format = 'INSERT INTO cmat_annual.judge_availability ('
        . " judge_availability_id,"
        . " judge_id,"
        . " contact_id,"
        . " cmat_year,"
        . " early_shift,"
        . " late_shift"
        . ") VALUES ("
        . " cmat_pl.next_key('a06'),"
        . " '%s',"
        . " '%s',"
        . " %d,"
        . " '%s',"
        . " '%s');";
    }
    $q = sprintf($q_format,
                 $this->judge_id,
                 $this->contact_id,
                 $this->cmat_year,
                 $this->early_shift,
                 $this->late_shift);
    Db::query($q);
  }


  function displayEditTitle() {
    echo '<h1>Judge Availability Edit:</h1>';
  }


  function displayDetailTitle() {
  }


  function displayHtmlEdit() {
    $judgeId = new InputElem('judge_id', $this->judge_id, 'hidden');
    $contactId = new InputElem('contact_id', $this->contact_id, 'hidden');
    $year = new InputElem('cmat_year', $this->cmat_year, 'hidden');
    $earlyShift = new SelectElem('early_shift', array(f, t), array('No', 'Yes'), $this->early_shift);
    $lateShift = new SelectElem('late_shift', array(f, t), array('No', 'Yes'), $this->late_shift);

    $judgeId->disp();
    $contactId->disp();
    $year->disp();
?>
<div class="judgeAvailabilityGroup group">
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Early Shift</th>
            <td><?php $earlyShift->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Late Shift</th>
            <td><?php $lateShift->disp(); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="clearingBox"></div>
  </div>
</div>
<?
  }


  function getIdFieldName() {
    return "judge_availability_id";
  }


  function getTypePrefix() {
    return 'a06';
  }


  function getContactId() {
    return $this->contact_id;
  }


  function htmlGet($field) {
    $ret = null;
    switch ($field) {
    case 'early_shift':
      $ret = TextUtils::pgBooleanToText($this->early_shift);
      break;
    case 'late_shift':
      $ret = TextUtils::pgBooleanToText($this->late_shift);
      break;
    default:
      $ret = parent::htmlGet($field);
    }
    return $ret;
  }

}

?>
