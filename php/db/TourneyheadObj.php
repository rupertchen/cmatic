<?php

require_once 'db/BaseDbObj.php';
require_once 'html/InputElem.php';
require_once 'html/SelectElem.php';
require_once 'util/TextUtils.php';

class TourneyHeadObj extends BaseDbObj {

  var $tourneyhead_id;

  var $cmat_year;
  var $contact_id;
  var $committee;
  var $is_committee_head;


  function getOneFromTourneyheadId($id) {
    $t = TourneyheadObj::getFromSomeId('tourneyhead_id', $id);
    return $t[0];
  }


  function getManyFromContactId($id) {
    return TourneyheadObj::getFromSomeId('contact_id', $id);
  }


  function getFromSomeId($fieldName, $id) {
    $q = sprintf("SELECT * FROM cmat_annual.tourneyhead WHERE %s = '%s' ORDER BY cmat_year DESC, lower(committee);",
                 TextUtils::cleanTextForSql($fieldName),
                 TextUtils::cleanTextForSql($id));
    $r = Db::query($q);
    $ts = array();
    while ($row = Db::fetch_array($r)) {
      $t = new TourneyheadObj();
      $t->loadFromArray($row);
      $t->loadFromDb = true;
      $ts[] = $t;
    }
    Db::free_result($r);
    return $ts;
  }


  function displayHtmlManyDetail($ts) {
    echo '<div class="tourneyheadGroup group">';
    echo '<h2>Tourneyhead Details:</h2>';
    echo '<div class="details">';
    echo '<div class="bottomRow">';
    echo '<div class="subGroup">';
    echo '<table>';
    echo '<tr><th scope="col">Action</th><th scope="col">CMAT Year</th><th scope="col">Committee</th><th scope="col">Head</th></tr>';
    foreach ($ts as $k => $v) {
      echo sprintf('<tr><td><a href="d.php?t=%s&amp;id=%s">Edit</a></td><td>%s</td><td>%s</td><td>%s</td></tr>',
                   EDIT_PAGE,
                   $v->tourneyhead_id,
                   $v->htmlGet('cmat_year'),
                   $v->htmlGet('committee'),
                   $v->htmlGet('is_committee_head'));
    }
    echo '</table></div></div></div></div>';
  }


  function loadFromArray($a) {
    $this->tourneyhead_id = $a['tourneyhead_id'];
    $this->contact_id = $a['contact_id'];
    $this->cmat_year = $a['cmat_year'];
    $this->committee = $a['committee'];
    $this->is_committee_head = $a['is_committee_head'];
  }


  function save() {
    $q_format = null;
    if ($this->loadFromDb) {
      $q_format = "UPDATE cmat_annual.tourneyhead SET"
        . " contact_id = '%s',"
        . " cmat_year = %d,"
        . " committee = '%s',"
        . " is_committee_head = '%s'"
        . " WHERE tourneyhead_id = '"
        . TextUtils::cleanTextForSql($this->tourneyhead_id)
        . "';";
    } else {
      $q_format = "INSERT INTO cmat_annual.tourneyhead ("
        . " tourneyhead_id,"
        . " contact_id,"
        . " cmat_year,"
        . " committee,"
        . " is_committee_head"
        . ") VALUES ("
        . " cmat_pl.next_key('a03'),"
        . " '%s',"
        . " %d,"
        . " '%s',"
        . " '%s');";
    }
    $q = sprintf($q_format,
                 $this->contact_id,
                 $this->cmat_year,
                 $this->committee,
                 $this->is_committee_head);
    Db::query($q);
  }


  function displayEditTitle() {
    echo "<h1>Tourneyhead Edit:</h1>";
  }


  function displayDetailTitle() {
    echo "<h1>Tourneyhead Details:</h1>";
  }


  function displayHtmlEdit() {
    $contactId = new InputElem('contact_id', $this->contact_id, 'hidden');
    $year = new SelectElem('cmat_year', range(15, 10), null, $this->cmat_year);
    $comm = new InputElem('committee', $this->committee);
    $isHead = new SelectElem('is_committee_head', array(f, t), array('No', 'Yes'), $this->is_committee_head);

    $contactId->disp();
?>
<div class="tourneyheadGroup group">
<div class="details">
  <div class="leftColumn">
    <div class="subGroup">
      <table>
        <tr>
          <th scope="row">CMAT Year</th>
          <td><?php $year->disp(); ?></td>
        </tr><tr>
          <th scope="row">Committee</th>
          <td><?php $comm->disp(); ?></td>
        </tr><tr>
          <th scope="row">Head</th>
          <td><?php $isHead->disp(); ?></td>
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
    return "tourneyhead_id";
  }


  function getTypePrefix() {
    return "a03";
  }


  function getContactId() {
    return $this->contact_id;
  }


  function htmlGet($field) {
    $ret = null;
    switch ($field) {
    case 'is_committee_head':
      $ret = TextUtils::pgBooleanToText($this->is_committee_head);
      break;
    default:
      $ret = parent::htmlGet($field);
    }
    return $ret;
  }

}

?>
