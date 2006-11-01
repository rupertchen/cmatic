<?php

require_once 'page/BasePage.php';
require_once 'db/SchoolObj.php';
require_once 'util/TextUtils.php';

class SchoolDetail extends BasePage {

  var $s;

  /****************************************
   * Constructor
   */
  function SchoolDetail() {
    $s = SchoolObj::getOneFromSchoolId($_GET['id']);
    if ($s) {
      $this->s = $s;
    }
  }


  function disp() {
    $this->s->displayDetailTitle();
    $this->s->displayHtmlDetail();
  }


  function dispDebug() {
    echo sprintf("<hr/><h1>SchoolDetail Debug</h1><pre>%s</pre>",
                 print_r($this, true));
  }

}

?>
