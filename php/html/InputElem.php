<?php

class InputElem {

  var $type;
  var $name;
  var $val;
  var $class;

  function InputElem($name, $val, $type = 'text', $class = '') {
    $this->type = $type;
    $this->name = $name;
    $this->val = $val;
    $this->class = $class;
  }


  function disp() {
    echo sprintf('<input type="%s" name="%s" id="%s" value="%s" class="%s"/>',
                 $this->type, $this->name, $this->name, $this->val, $this->class);
  }

}

?>
