<?php

require_once 'util/TextUtils.php';
require_once 'db/BaseDbObj.php';
require_once 'db/ContactObj.php';
require_once 'db/JudgeObj.php';
require_once 'db/MedicalObj.php';
require_once 'db/SchoolObj.php';
require_once 'db/TourneyheadObj.php';
require_once 'html/InputElem.php';

class Create {

  var $c;

  function Create($handleAction = false) {
    if ($handleAction) {
      $this->handleAction($_POST);
    } else {
      // Switch to an Obj and set the default values
      switch ($_GET['prefix']) {
      case 'a01':
        $this->c = new ContactObj();
        $this->c->mailing_country = 'USA';
        $this->c->known_languages__english = '1';
        break;
      case 'a02':
        $this->c = new JudgeObj();
        $this->c->contact_id = $_GET['contact_id'];
        $this->c->school_affiliation_id = '00000000000000000000';
        break;
      case 'a03':
        $this->c = new TourneyheadObj();
        $this->c->contact_id = $_GET['contact_id'];
        $this->c->cmat_year = 15;
        break;
      case 'a04':
        $this->c = new SchoolObj();
        $this->c->contact_id = '00000000000000000000';
        break;
      case 'a05':
        $this->c = new MedicalObj();
        $this->c->contact_id = $_GET['contact_id'];
        break;
      }
    }
  }

  /****************************************
   * disp()
   */
  function disp() {
    $c = $this->c;

    $prefixElem = new InputElem('prefix', $c->getTypePrefix(), 'hidden');
    $saveElem = new InputElem('_create', 'New', 'submit');

    if (is_a($c, 'JudgeObj')
        || is_a($c, 'TourneyheadObj')
        || is_a($c, 'MedicalObj')) {
      $t = sprintf("%s&amp;id=%s", CONTACT_DETAIL_PAGE, $c->contact_id);
    } else if (is_a($c, 'ContactObj')) {
      $t = sprintf("%s&amp;obj=%s&amp;type=%s", LISTING_PAGE, "contact", "other");
    } else if (is_a($c, 'SchoolObj')) {
      $t = sprintf("%s&amp;obj=%s&amp;type=%s", LISTING_PAGE, "school", "all");
    } else {
      $t = sprintf("%s", LISTING_PAGE);
    }

    $c->displayEditTitle();
    echo sprintf('<form action="d.php?f=%s&amp;t=%s" method="POST">',
                 CREATE_PAGE, $t);
    $prefixElem->disp();
    $c->displayHtmlEdit();
    $saveElem->disp();
    echo '</form>';
  }


  /****************************************
   * dispDebug
   */
  function dispDebug() {
    echo sprintf('<hr/><h1>Create Debug</h1><pre>%s</pre>',
                 print_r($this, true));
  }


  /****************************************
   * hasError
   */
  function hasError() {
    return !$this->c->isValid();
  }


  /****************************************
   *
   */
  function handleAction($p) {
    if (isset($_POST['_create'])) {
      $obj = null;
      switch ($_POST['prefix']) {
      case 'a01':
        $obj = new ContactObj();
        $obj->loadFromPostArray($p);
        break;
      case 'a02':
        $obj = new JudgeObj();
        $obj->loadFromPostArray($p);
        break;
      case 'a03':
        $obj = new TourneyheadObj();
        $obj->loadFromArray($p);
        break;
      case 'a04':
        $obj = new SchoolObj();
        $obj->loadSchoolFromArray($p);
        break;
      case 'a05':
        $obj = new MedicalObj();
        $obj->loadFromArray($p);
        break;
      }
      $obj->save();
      $this->c = $obj;
    } else {
      $this->c = new ContactObj();
    }
  }

}
?>
