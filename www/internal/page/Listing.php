<?php

require_once 'db/ContactObj.php';
require_once 'db/SchoolObj.php';
require_once 'page/BasePage.php';
require_once 'util/Db.php';

class Listing extends BasePage {

  var $obj;
  var $type;
  var $title;

  var $list;

  /****************************************
   * Constructor
   */
  function Listing() {
    
    $this->obj = $_GET['obj'];
    $this->type = $_GET['type'];
    $this->list = array();

    // Both values must be given explicitly for us to override the cookies
    if ($this->obj && $this->type) {
      setcookie(LISTING_OBJ_COOKIE, $this->obj);
      setcookie(LISTING_TYPE_COOKIE, $this->type);
    } else {
      $this->obj = $_COOKIE[LISTING_OBJ_COOKIE];
      $this->type = $_COOKIE[LISTING_TYPE_COOKIE];
    }
    switch ($this->obj) {
    case "school":
      $q = "SELECT s.*, c.first_name, c.last_name, c.middle_name"
        . " FROM cmat_core.school s LEFT OUTER JOIN cmat_core.contact c"
        . " ON (s.contact_id = c.contact_id)"
        . " WHERE s.school_id != '00000000000000000000'";
      switch ($this->type) {
      case "international":
        $q .= " AND s.mailing_country != 'USA'";
        $this->title = "International Schools";
        break;
      case "usa":
        $q .= " AND s.mailing_country = 'USA'";
        $this->title = "USA Schools";
        break;
      case "all":
      default:
        $this->title = "All Schools";
        break;
      }

      $q .= ' ORDER BY s.name, s.mailing_city, s.mailing_state, s.mailing_country;';
      $r = Db::query($q);
      while ($row = Db::fetch_array($r)) {
        $s = new SchoolObj();
        $row['contact_name'] = TextUtils::makeFullName($row['last_name'], $row['first_name'], $row['middle_name']);
        $s->loadSchoolFromArray($row);
        $s->loadFromDb = true;
        $this->list[] = $s;
      }
      Db::free_result($r);
      break;
    case "contact":
    default:
      $q = 'SELECT * FROM cmat_core.contact'
        . " WHERE contact_id != '00000000000000000000'";
      switch ($this->type) {
      case "other":
        $q .= " AND NOT EXISTS"
          . " (SELECT 1 FROM cmat_core.judge"
          . " WHERE contact.contact_id = judge.contact_id)"
          . " AND NOT EXISTS"
          . " (SELECT 1 FROM cmat_annual.tourneyhead"
          . " WHERE contact.contact_id = tourneyhead.contact_id)"
          . " AND NOT EXISTS"
          . " (SELECT 1 FROM cmat_core.medical"
          . " WHERE contact.contact_id  = medical.contact_id)";
        $this->title = "Other Contacts";
        break;
      case "judge":
        $q .= " AND EXISTS"
          . " (SELECT 1 FROM cmat_core.judge"
          . " WHERE contact.contact_id = judge.contact_id)";
        $this->title = "Judges";
        break;
      case "tourneyhead":
        $q .= " AND EXISTS"
          . " (SELECT 1 FROM cmat_annual.tourneyhead"
          . " WHERE contact.contact_id = tourneyhead.contact_id)";
        $this->title = "Tournament Staff";
        break;
      case "medical":
        $q .= " AND EXISTS"
          . " (SELECT 1 FROM cmat_core.medical"
          . " WHERE contact.contact_id = medical.contact_id)";
        $this->title = "Medical Staff";
        break;
      case "all":
      default:
        $this->title = "All Contacts";
      }

      $q .= " ORDER BY last_name, first_name, middle_name;";
      $r = Db::query($q);
      while ($row = Db::fetch_array($r)) {
        $c = new ContactObj();
        $c->loadContactFromArray($row);
        $c->loadFromDb = true;
        $this->list[] = $c;
      }
      Db::free_result($r);
      break;
    }

    $GLOBALS['HtmlTitle'] = $this->title;
  }


  /****************************************
   * disp()
   */
  function disp() {
?>
<h1>List Page: <?php echo $this->title; ?></h1>
<table border="0" cellpadding="0" cellspacing="0">
<tr><td>
<fieldset>
  <legend>Contact</legend>
  <div>
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=contact&amp;type=all">All</a>]
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=contact&amp;type=judge">Judges</a>]
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=contact&amp;type=tourneyhead">Tournament Staff</a>]
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=contact&amp;type=medical">Medical Staff</a>]
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=contact&amp;type=other">Other</a>]
  </div>
</fieldset>
</td><td>
<fieldset>
  <legend>School</legend>
  <div>
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=school&amp;type=all">All</a>]
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=school&amp;type=international">International</a>]
    [<a href="d.php?t=<?php echo LISTING_PAGE; ?>&amp;obj=school&amp;type=usa">USA</a>]
  </div>
</fieldset>
</td></tr></table>
<?php
  if (count($this->list) > 0) {
    echo '<table class="list" border="0" cellpadding="0" cellspacing="0">';
    $this->list[0]->displayHtmlTableHeader();
    $rParity = false;
    foreach ($this->list as $i => $c) {
      $c->displayHtmlTableRow($rParity);
      $rParity = !$rParity;
    }
    echo '</table>';
  } else {
    echo "No records found.";
  }
?>
<fieldset>
  <legend>Create</legend>
  <div>
    [<a href="d.php?t=<?php echo CREATE_PAGE; ?>&amp;prefix=a01">Contact</a>]
    [<a href="d.php?t=<?php echo CREATE_PAGE; ?>&amp;prefix=a04">School</a>]
  </div>
</fieldset>
<?php
  }


  /****************************************
   * dispDebug()
   */
  function dispDebug() {
    // Remove $this->list from debug display (possibly huge)
    $list = $this->list;
    $this->list = "--Not shown in debug--";

    echo sprintf('<hr/><h1>Listing Debug</h1><pre>%s</pre>',
                 print_r($this, true));

    // Restore list
    $this->list = $list;
  }

}

?>
