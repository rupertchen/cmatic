<?php

require_once 'db/BaseDbObj.php';
require_once 'html/SelectElem.php';
require_once 'util/TextUtils.php';

class MedicalObj extends BaseDbObj {

  var $medical_id;

  var $contact_id;
  var $type;

  function getOneFromMedicalId($id) {
    $m = MedicalObj::getFromSomeId('medical_id', $id);
    return $m[0];
  }


  function getOneFromContactId($id) {
    $m = MedicalObj::getFromSomeId('contact_id', $id);
    return $m[0];
  }


  function getFromSomeId($fieldName, $id) {
    $q = sprintf("SELECT * FROM cmat_core.medical WHERE %s = '%s'",
                 TextUtils::cleanTextForSql($fieldName),
                 TextUtils::cleanTextForSql($id));
    $r = Db::query($q);
    $list = array();
    while ($row = Db::fetch_array($r)) {
      $m = new MedicalObj();
      $m->loadFromArray($row);
      $m->loadFromDb = true;
      $list[] = $m;
    }
    Db::free_result($r);
    return $list;
  }


  function loadFromArray($a) {
    $this->medical_id = $a['medical_id'];
    $this->contact_id = $a['contact_id'];
    $this->type = $a['type'];
  }


  function save() {
    $q_format = null;
    if ($this->loadFromDb) {
      $q_format = "UPDATE cmat_core.medical SET"
        . " contact_id = '%s',"
        . " type = '%s'"
        . sprintf(" WHERE medical_id = '%s';", TextUtils::cleanTextForSql($this->medical_id));
    } else {
      $q_format = "INSERT INTO cmat_core.medical ("
        . " medical_id,"
        . " contact_id,"
        . " type"
        . ") VALUES ("
        . " cmat_pl.next_key('a05'),"
        . " '%s',"
        . " '%s');";
    }
    $q = sprintf($q_format,
                 $this->contact_id,
                 $this->type);
    Db::query($q);
  }


  function displayEditTitle() {
    echo "<h1>Medical Edit:</h1>";
  }


  function displayDetailTitle() {
    echo "<h1>Medical Details:</h1>";
  }


  function displayHtmlEdit() {
    $contactId = new InputElem('contact_id', $this->contact_id, 'hidden');
    $type = new SelectElem('type', array('--Unknown--', 'CMT', 'EMT', 'Masseuse', 'MD', 'MICP', 'RN'), null, $this->type);

    $contactId->disp();
?>
<div class="medicalGroup group">
  <h2>Medical Details:</h2>
  <a href="d.php?t=<?php echo EDIT_PAGE; ?>&id=<?php echo $this->medical_id; ?>">Edit</a>
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Type:</th>
            <td><?php $type->disp(); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="clearingBox"></div>
  </div>
</div>
<?php
  }


  function displayHtmlDetail() {
?>
<div class="medicalGroup group">
  <h2>Medical Details:</h2>
  <a href="d.php?t=<?php echo EDIT_PAGE; ?>&id=<?php echo $this->medical_id; ?>">Edit</a>
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Type:</th>
            <td><?php echo $this->htmlGet('type'); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="clearingBox"></div>
  </div>
</div>
<?php
  }


  function getIdFieldName() {
    return "medical_id";
  }


  function getTypePrefix() {
    return "a05";
  }


  function getContactId() {
    return $this->contact_id;
  }

}

?>
