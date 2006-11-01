<?php

class SelectElem {

  var $name;
  var $values;
  var $labels;
  var $selected;

  function SelectElem($name, $values, $labels, $selected = null) {
    $this->name = $name;
    $this->values = $values;
    if (is_null($labels)) {
      $this->labels = $values;
    } else {
      $this->labels = $labels;
    }
    $this->selected = $selected;
  }


  // TODO: Have all elements do things this way
  function toString() {
    $ret = array();
    $ret[] = sprintf('<select name="%s">', $this->name);
    $count = count($this->values);
    for ($i = 0; $i < $count; $i++) {
      $val = $this->values[$i];
      $label = $this->labels[$i];
      $isSelected = ($val == $this->selected) ? ' selected="selected"' : '';
      $ret[] = sprintf('<option value="%s"%s>%s</option>',
                       $val, $isSelected, $label);
    }
    $ret[] = "</select>";

    return implode('', $ret);
  }


  // TODO: Push this up to all elements
  function disp() {
    echo $this->toString();
  }

}

?>
