<?php

class CheckboxArrayElem {

  var $vars;
  var $labels;
  var $areChecked;


  function CheckboxArrayElem($vars, $labels, $areChecked) {
    $this->vars = $vars;
    $this->labels = $labels;
    $this->areChecked = $areChecked;
  }


  function disp() {
    $c = count($this->vars);
    for ($i = 0; $i < $c; $i++) {
      $var = $this->vars[$i];
      $label = $this->labels[$i];
      $isChecked = ($this->areChecked[$i]) ? ' checked="checked"' : '';
      echo sprintf("<input id=\"%s\" type=\"checkbox\" name=\"%s\" value=\"1\"%s/><label for=\"%s\">%s</label>",
                   $var, $var, $isChecked, $var, $label);
    }
  }

}

?>
