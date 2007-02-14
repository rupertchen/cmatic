<?php

require_once 'db/BaseDbObj.php';
require_once 'html/SelectElem.php';
require_once 'util/Db.php';
require_once 'util/TextUtils.php';

class JudgeStyleMatrixObj extends BaseDbObj {

  var $judge_style_matrix_id;

  var $judge_id;
  var $contact_id;

  /*
   * TODO: Comment about styles and matrix values
   * such as push hand = style:3
   */
  var $style;
  var $matrix;

  function getOneFromJudgeStyleMatrixId($id) {
    $o = JudgeStyleMatrixObj::getFromSomeId('judge_style_matrix_id', $id);
    return $o[0];
  }


  function getManyFromJudgeId($id) {
    return JudgeStyleMatrixObj::getFromSomeId('judge_id', $id);
  }


  function getFromSomeId($fieldName, $id) {
    $q = sprintf("SELECT * FROM cmat_core.judge_style_matrix WHERE %s = '%s' ORDER BY style",
                 TextUtils::cleanTextForSql($fieldName),
                 TextUtils::cleanTextForSql($id));
    $r = Db::query($q);
    $list = array();
    while ($row = Db::fetch_array($r)) {
      $o = new JudgeStyleMatrixObj();
      $o->loadFromArray($row);
      $o->loadFromDb = true;
      $list[] = $o;
    }
    Db::free_result($r);
    return $list;
  }


  function displayHtmlManyDetail($list) {
    echo '<div class="judgeStyleMatrixGroup group">';
    echo '<h2>Judge Style Matrix Details:</h2>';
    echo '<div class="details">';
    echo '<div class="bottomRow">';
    echo '<div class="subGroup">';
    echo '<table>';
    echo '<tr><th scope="col">Action</th><th scope="col">Style</th><th scope="col">Matrix</th></tr>';
    foreach ($list as $k => $v) {
      echo sprintf('<tr><td><a href="d.php?t=%s&amp;id=%s">Edit</a></td><td>%s</td><td>%s</td></tr>',
                   EDIT_PAGE,
                   $v->judge_style_matrix_id,
                   $v->htmlGet('style'),
                   $v->htmlGet('matrix'));
    }
    echo '</table></div></div></div></div>';
  }


  function loadFromArray($a) {
    $this->judge_style_matrix_id = $a['judge_style_matrix_id'];
    $this->judge_id = $a['judge_id'];
    $this->contact_id = $a['contact_id'];
    $this->style = $a['style'];
    if ($this->isPushHands()) {
      // Push hands does not have matrix values only uses l0_a0
      $this->matrix = array(array($a['l0_a0']));
    } else {
      $levels = range(0, 3);
      $ages = range(0, 4);
      $this->matrix = array();
      foreach($levels as $level) {
        $tmp = array();
        foreach ($ages as $age) {
          $tmp[$age] = $a[sprintf('l%d_a%d', $level, $age)];
        }
        $this->matrix[$level] = $tmp;
      }
    }
  }


  function save() {
    $q_format = null;
    $levels = range(0, 3);
    $ages = range(0, 4);
    if ($this->loadFromDb) {
      $q_matrix = array();
      foreach ($levels as $l) {
        foreach ($ages as $a) {
          $q_matrix[] = sprintf(' l%d_a%d = %%d', $l, $a);
        }
      }

      $q_format = 'UPDATE cmat_core.judge_style_matrix SET'
        . " judge_id = '%s',"
        . " contact_id = '%s',"
        . " style = %d,"
        . implode(', ', $q_matrix)
        . " WHERE judge_style_matrix_id = '"
        . TextUtils::cleanTextForSql($this->judge_style_matrix_id)
        . "';";        
    } else {
      $q_matrix0 = array();
      $q_matrix1 = array();
      foreach ($levels as $l) {
        foreach ($ages as $a) {
          $q_matrix0[] = sprintf('l%d_a%d', $l, $a);
          $q_matrix1[] = ' %d';
        }
      }
      $q_format = 'INSERT INTO cmat_core.judge_style_matrix ('
        . " judge_style_matrix_id,"
        . " judge_id,"
        . " contact_id,"
        . " style,"
        . implode(', ', $q_matrix0)
        . ') VALUES ('
        . " cmat_pl.next_key_a07(),"
        . " '%s',"
        . " '%s',"
        . " %d,"
        . implode(', ', $q_matrix1)
        . ");";
    }
    $q = sprintf($q_format,
                 $this->judge_id,
                 $this->contact_id,
                 $this->style,
                 $this->matrix[0][0],
                 $this->matrix[0][1],
                 $this->matrix[0][2],
                 $this->matrix[0][3],
                 $this->matrix[0][4],
                 $this->matrix[1][0],
                 $this->matrix[1][1],
                 $this->matrix[1][2],
                 $this->matrix[1][3],
                 $this->matrix[1][4],
                 $this->matrix[2][0],
                 $this->matrix[2][1],
                 $this->matrix[2][2],
                 $this->matrix[2][3],
                 $this->matrix[2][4],
                 $this->matrix[3][0],
                 $this->matrix[3][1],
                 $this->matrix[3][2],
                 $this->matrix[3][3],
                 $this->matrix[3][4]);
    Db::query($q);
  }


  function displayEditTitle() {
    echo '<h1>Judge Style Matrix Edit:</h1>';
  }


  function displayDetailTitle() {
  }


  function displayHtmlEdit() {
    $judgeId = new InputElem('judge_id', $this->judge_id, 'hidden');
    $contactId = new InputElem('contact_id', $this->contact_id, 'hidden');
    $style = new InputElem('style', $this->style, 'hidden');
    
    $judgeId->disp();
    $contactId->disp();
    $style->disp();
?>
<div class="judgeSkillMatrixGroup group">
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
<?php
  if($this->isPushHands()) {
    $ynm = new SelectElem('l0_a0', range(0, 2), array('No', 'Yes', 'Maybe'), $this->matrix[0][0]);
    echo '<table><tr><th scope="row">Qualified to Judge:</th><td>';
    $ynm->disp();
    echo '</td></tr></table>';
  } else {
    echo JudgeStyleMatrixObj::makeHtmlMatrix($this->style, $this->matrix, true);
  }
  
   
?>
      </div>
    </div>
    <div class="clearingBox"></div>
  </div>
</div>
<?php
  }


  function getIdFieldName() {
    return "judge_style_matrix_id";
  }


  function getTypePrefix() {
    return 'a07';
  }


  function getContactId() {
    return $this->contact_id;
  }

  function htmlGet($field) {
    switch ($field) {
    case 'matrix':
      if ($this->isPushHands()) {
        $ret = TextUtils::numToYnmText($this->matrix[0][0]);
      } else {
        $ret = JudgeStyleMatrixObj::makeHtmlMatrix($this->style, $this->matrix, false);
      }
      break;
    case 'style':
      $ret = TextUtils::numToStyle($this->style);
      break;
    default:
      $ret = parent::htmlGet($field);
    }

    return $ret;
  }


  function makeHtmlMatrix($style, $matrix, $isEdit) {
    $ret = array('<table>');
    $ret[] = sprintf('<tr><td></td><th scope="col">%s</th><th scope="col">%s</th><th scope="col">%s</th><th scope="col">%s</th><th scope="col">%s</th></tr>',
                     TextUtils::numToAge($style, 0),
                     TextUtils::numToAge($style, 1),
                     TextUtils::numToAge($style, 2),
                     TextUtils::numToAge($style, 3),
                     TextUtils::numToAge($style, 4));
    $levels = range(0, 3);
    $ages = range(0, 4);
    foreach ($levels as $level) {
      $ret[] = sprintf('<tr><th scope="row">%s</th>', TextUtils::numToLevel($level));
      foreach ($ages as $age) {
        if ($isEdit) {
          $cellName = sprintf('l%d_a%d', $level, $age);
          $ynm = new SelectElem($cellName,
                                array(0, 1, 2),
                                array('No', 'Yes', 'Maybe'),
                                $this->matrix[$level][$age]);
          $cell = $ynm->toString();
        } else {
          $cell = TextUtils::numToYnmText($matrix[$level][$age]);
        }
        $ret[] = sprintf('<td>%s</td>', $cell);
      }
      $ret[] = '</tr>';
    }
    $ret[] = '</table>';

    return implode('', $ret);
  }


  function isPushHands() {
    return (3 == $this->style);
  }
}

?>
