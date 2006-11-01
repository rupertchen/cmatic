<?php

require_once 'page/BasePage.php';
require_once 'db/ContactObj.php';
require_once 'db/JudgeObj.php';
require_once 'db/MedicalObj.php';
require_once 'db/TourneyheadObj.php';
require_once 'util/TextUtils.php';

class ContactDetail extends BasePage {

  var $c;
  var $j;
  var $ts;
  var $m;

  /****************************************
   * Constructor
   */
  function ContactDetail() {

    $cId = $_GET['id'];

    // Contact
    $c = ContactObj::getOneFromContactId($cId);
    if ($c) {
      $this->c = $c;
    }

    // Judge
    $j = JudgeObj::getOneFromContactId($cId);
    if ($j) {
      $j->loadChildrenFromDb();
      $this->j = $j;
    }

    // Tourneyheads
    $ts = TourneyheadObj::getManyFromContactId($cId);
    if (count($ts) > 0) {
      $this->ts = $ts;
    }

    // Medical
    $m = MedicalObj::getOneFromContactId($cId);
    if ($m) {
      $this->m = $m;
    }
  }


  /****************************************
   * disp()
   */
  function disp() {
    $hasJudgeRel = isset($this->j);
    $hasThRel = isset($this->ts);
    $hasMedicalRel = isset($this->m);

    // Display contact stuff
    $this->c->displayDetailTitle();
    $this->c->displayHtmlDetail();

    // Display relation info
    if ($hasJudgeRel) {
      $this->j->displayHtmlDetail();
    }
    if ($hasThRel) {
      TourneyheadObj::displayHtmlManyDetail($this->ts);
    }
    if ($hasMedicalRel) {
      $this->m->displayHtmlDetail();
    }

    // Create new relations
    // Only one judge relation allowed
    if (!$hasJudgeRel) {
      echo sprintf('[<a href="d.php?t=%s&amp;prefix=a02&amp;contact_id=%s">create judge</a>]',
                   CREATE_PAGE,
                   $this->c->contact_id);
    }
    echo sprintf('[<a href="d.php?t=%s&amp;prefix=a03&amp;contact_id=%s">create tourneyhead</a>]',
                 CREATE_PAGE,
                 $this->c->contact_id);
    // Only one medical relation allowed
    if (!$hasMedicalRel) {
      echo sprintf('[<a href="d.php?t=%s&amp;prefix=a05&amp;contact_id=%s">create medical</a>]',
                   CREATE_PAGE,
                   $this->c->contact_id);
    }
  }


  /****************************************
   * dispDebug()
   */
  function dispDebug() {
    echo sprintf("<hr/><h1>ContactDetail Debug</h1><pre>%s</pre>",
                 print_r($this, true));
  }

}
?>
