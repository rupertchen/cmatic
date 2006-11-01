<?php

require_once 'db/ContactObj.php';
require_once 'db/SchoolObj.php';
require_once 'page/BasePage.php';
require_once 'util/Db.php';

class LookupPopup extends BasePage {

  var $obj;
  var $origLookupId;
  var $list;
  var $title;

  /****************************************
   * Constructor
   */
  function LookupPopup() {
    $this->obj = $_GET['obj'];
    $this->origLookupId = $_GET['orig'];
    $this->list = array();

    switch ($this->obj) {
    case "a04":
      $q = 'SELECT * FROM cmat_core.school'
        . ' ORDER BY name';
      $r = Db::query($q);
      while ($row = Db::fetch_array($r)) {
        $s = new SchoolObj();
        $s->loadSchoolFromArray($row);
        $s->loadFromDb = true;
        $this->list[] = $s;
      }
      Db::free_result($r);

      $this->title = "School Lookup";
      break;
    case "a01":
    default:
      $q = 'SELECT * FROM cmat_core.contact'
        . ' ORDER BY last_name, first_name, middle_name';
      $r = Db::query($q);
      while ($row = Db::fetch_array($r)) {
        $c = new ContactObj();
        $c->loadContactFromArray($row);
        $c->loadFromDb = true;
        $this->list[] = $c;
      }
      Db::free_result($r);

      $this->title = "Contact Lookup";
      break;
    }

    $GLOBALS[HTML_TITLE] = $this->title;
    $GLOBALS[BODY_CLASS] = "lookupPage";
  }


  /****************************************
   * disp()
   */
  function disp() {
?>
<script type="text/javascript">
  l = parent.document.elements['LookupField_<?php echo $this->origLookupId; ?>'];

  function pick(id, name) {
    l.updateValues(id, name);
    l.closeLookup();
  }
</script>
<div class="popupControlBar">
  <a class="btn" href="javascript:l.closeLookup();">Close</a>
</div>
<div class="win">
  <table>
    <tr>
      <th scope="col">Name</th><th scope="col">Details</th>
    </tr>
<?php
    foreach ($this->list as $i => $o) {
      $name = addslashes($o->getStandardName());
      echo sprintf('<tr><td><a href="javascript:pick(\'%s\', \'%s\');">%s</a></td>'
                   . '<td>%s</td></tr>',
                   $o->getId(), $name, $name, addslashes($o->getSummaryDetail()));
    }
?>
  </table>
</div>
<?php
  }

}

?>
