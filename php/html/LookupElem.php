<?php

require_once 'html/InputElem.php';

class LookupElem {

  var $name;
  var $obj;
  var $valId;
  var $valName;

  function LookupElem($name, $obj, $valId, $valName) {
    $this->name = $name;
    $this->obj = $obj;
    $this->valId = $valId;
    $this->valName = $valName;
  }


  function disp() {
    $idElem = new InputElem($this->name . '_id', '', 'hidden');
    $nameElem = new InputElem($this->name . '_name', '', 'hidden');

    $idElem->disp();
    $nameElem->disp();

    echo sprintf('<span id="%s_display">&hellip;</span>', $this->name);
    echo sprintf('<a id="%s_clear" class="btn" href="javascript:void(0);">x</a>', $this->name);
    echo sprintf('<a id="%s_switch" class="btn" href="javascript:void(0);">Lookup</a>', $this->name);
    echo sprintf('<iframe id="%s_lookup" class="popup" style="display:none;" src="blank.html" frameborder="0"></iframe>', $this->name);
    echo sprintf('<script type="text/javascript">var tmpL = new LookupField(\'%s\', \'%s\'); tmpL.updateValues(\'%s\', \'%s\');</script>',
                 $this->name, $this->obj, $this->valId, $this->valName);
  }

}

?>
