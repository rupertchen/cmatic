<?php

require_once 'util/TextUtils.php';

class BaseDbObj {

  var $loadFromDb;
  var $errors = array();

  function htmlGet($field) {
    $ret = TextUtils::htmlize($this->$field);
    if (0 == strlen($ret)) {
      $ret = "&nbsp;";
    }
    return $ret;
  }


  function isValid() {
    return 0 == count($this->errors);
  }


  function displayEditTitle() {
    echo "\n<!-- Default object edit title -->\n";
  }


  function displayDetailTitle() {
    echo "\n<!-- Default object detail title-->\n";
  }


  function displayHtmlDetail() {
    echo "\n<!-- Default object html detail -->\n";
  }


  function displayHtmlEdit() {
    echo "\n<!-- Default object html edit -->\n";
  }


  function displayHtmlTableHeader() {
    echo "\n<!-- Default object html table header -->\n";
  }


  function displayHtmlTableRow($isEven) {
    echo "\n<!-- Default object html table row -->\n";
  }


  function getIdFieldName() {
    return "No id field given.";
  }


  function getTypePrefix() {
    return "No type prefix.";
  }


  function getContactId() {
    return "Object not associated to contact";
  }


  function getId() {
    $field = $this->getIdFieldName();
    return $this->$field;
  }


  function getStandardName() {
    return "??";
  }


  function getSummaryDetail() {
    return "??";
  }


  function isTopLevel() {
    return false;
  }


  /*
   * TODO: Make a *Rec getter that will recursively get
   * everything and leave the current one that does not.
   */
  function loadChildrenFromDb() {}

}

?>
