<?php

require_once 'db/ContactObj.php';
require_once 'db/JudgeObj.php';
require_once 'db/JudgeAvailabilityObj.php';
require_once 'db/JudgeStyleMatrixObj.php';
require_once 'db/MedicalObj.php';
require_once 'db/SchoolObj.php';
require_once 'db/TourneyheadObj.php';
require_once 'html/InputElem.php';
require_once 'page/BasePage.php';
require_once 'util/TextUtils.php';

class Edit extends BasePage 
{

  var $obj;

  function Edit($handleAction = false) {
    if ($handleAction) {
      $this->handleAction($_POST);
    } else {
      $id = $_GET['id'];
      switch (substr($id, 0, 3)) {
      case 'a01':
        $this->obj = ContactObj::getOneFromContactId($id);
        break;
      case 'a02':
        $this->obj = JudgeObj::getOneFromJudgeId($id);
        break;
      case 'a03':
        $this->obj = TourneyheadObj::getOneFromTourneyheadId($id);
        break;
      case 'a04':
        $this->obj = SchoolObj::getOneFromSchoolId($id);
        break;
      case 'a05':
        $this->obj = MedicalObj::getOneFromMedicalId($id);
        break;
      case 'a06':
        $this->obj = JudgeAvailabilityObj::getOneFromJudgeAvailabilityId($id);
        break;
      case 'a07':
        $this->obj = JudgeStyleMatrixObj::getOneFromJudgeStyleMatrixId($id);
        break;
      }
    }
  }

  /****************************************
   * disp()
   */
  function disp() {
    $o = $this->obj;

    $idField = $o->getIdFieldName();
    $id = $o->$idField;

    $idFieldElem = new InputElem($idField, $id, 'hidden');
    $prefixElem = new InputElem('prefix', $o->getTypePrefix(), 'hidden');
    $saveElem = new InputElem('_update', 'Save', 'submit');
    $cancelElem = new InputElem('_cancel', 'Cancel', 'submit');

    // Setup "t" and "id" params
    // As of the time of this writing, all objects are just related objects dangling
    // from a contact except schools.
    $t = null;
    $id = null;
    if (is_a($o, 'SchoolObj')) {
      $t = SCHOOL_DETAIL_PAGE;
      $id = $o->school_id;
    } else {
      $t = CONTACT_DETAIL_PAGE;
      $id = $o->getContactId();
    }

    $o->displayEditTitle();
    // TODO: Refactor the target into the objects
    echo sprintf('<form action="d.php?f=%s&amp;t=%s&amp;id=%s" method="post">',
                 EDIT_PAGE, $t, $id);
    $idFieldElem->disp();
    $prefixElem->disp();
    $o->displayHtmlEdit();
    $saveElem->disp();
    $cancelElem->disp();
    echo "</form>";
  }


  /****************************************
   * dispDebug
   */
  function dispDebug() {
    echo sprintf('<hr/><h1>Edit Debug</h1><pre>%s</pre>',
                 print_r($this, true));
  }


  /****************************************
   * hasError
   */
  function hasError() {
    return !$this->obj->isValid();
  }


  /****************************************
   *
   */
  function handleAction($p) {
    if (isset($_POST['_update'])) {
      $o = null;
      switch ($_POST['prefix']) {
      case 'a01':
        $o = new ContactObj();
        $o->loadFromPostArray($p);
        break;
      case 'a02':
        $o = new JudgeObj();
        $o->loadFromPostArray($p);
        break;
      case 'a03':
        $o = new TourneyheadObj();
        $o->loadFromArray($p);
        break;
      case 'a04':
        $o = new SchoolObj();
        $o->loadSchoolFromArray($p);
        break;
      case 'a05':
        $o = new MedicalObj();
        $o->loadFromArray($p);
        break;
      case 'a06':
        $o = new JudgeAvailabilityObj();
        $o->loadFromArray($p);
        break;
      case 'a07':
        $o = new JudgeStyleMatrixObj();
        $o->loadFromArray($p);
        break;
      default:
        break;
      }
      $o->loadFromDb = true;
      $o->save();
      $this->obj = $o;
    } else {
      $this->obj = new ContactObj();
    }
  }

}
?>
