<?php

require_once 'db/BaseDbObj.php';
require_once 'html/InputElem.php';
require_once 'html/LookupElem.php';
require_once 'util/Db.php';
require_once 'util/TextUtils.php';

class SchoolObj extends BaseDbObj {

  var $school_id;

  var $name;
  var $email;
  var $phone;
  var $fax;
  var $contact_id;
  var $contact_name;
  var $mailing_street_address_1;
  var $mailing_street_address_2;
  var $mailing_city;
  var $mailing_state;
  var $mailing_zip;
  var $mailing_country;
  var $comment;

  function getOneFromSchoolId($id) {
    $s = SchoolObj::getFromSomeId('school_id', $id);
    return $s[0];
  }


  function getFromSomeId($fieldName, $id) {
    $q = sprintf("SELECT s.*, c.first_name, c.last_name, c.middle_name"
                 . " FROM cmat_core.school s LEFT OUTER JOIN cmat_core.contact c"
                 . " ON (s.contact_id = c.contact_id)"
                 . " WHERE s.%s = '%s';",
                 TextUtils::cleanTextForSql($fieldName),
                 TextUtils::cleanTextForSql($id));
    $r = Db::query($q);
    $ss = array();
    while ($row = Db::fetch_array($r)) {
      $s = new SchoolObj();
      $row['contact_name'] = TextUtils::makeFullName($row['last_name'], $row['first_name'], $row['middle_name']);
      $s->loadSchoolFromArray($row);
      $s->loadFromDb = true;
      $ss[] = $s;
    }
    Db::free_result($r);
    return $ss;
  }


  function loadSchoolFromArray($a) {
    $this->school_id = $a['school_id'];
    $this->name = $a['name'];
    $this->email = $a['email'];
    $this->phone = $a['phone'];
    $this->fax = $a['fax'];
    $this->contact_id = $a['contact_id'];
    $this->contact_name = $a['contact_name'];
    $this->mailing_street_address_1 = $a['mailing_street_address_1'];
    $this->mailing_street_address_2 = $a['mailing_street_address_2'];
    $this->mailing_city = $a['mailing_city'];
    $this->mailing_state = $a['mailing_state'];
    $this->mailing_zip = $a['mailing_zip'];
    $this->mailing_country = $a['mailing_country'];
    $this->comment = $a['comment'];
  }


  function save() {
    $q_format = "";
    if ($this->loadFromDb) {
      $q_format = "UPDATE cmat_core.school SET"
        . " name = '%s',"
        . " email = '%s',"
        . " phone = '%s',"
        . " fax = '%s',"
        . " contact_id = '%s',"
        . " mailing_street_address_1 = '%s',"
        . " mailing_street_address_2 = '%s',"
        . " mailing_city = '%s',"
        . " mailing_state = '%s',"
        . " mailing_zip = '%s',"
        . " mailing_country = '%s',"
        . " comment = '%s'"
        . " WHERE school_id = '"
        . TextUtils::cleanTextForSql($this->school_id)
        . "';";
    } else {
      $q_format = "INSERT INTO cmat_core.school ("
        . " school_id,"
        . " name, email, phone, fax,"
        . " contact_id,"
        . " mailing_street_address_1, mailing_street_address_2,"
        . " mailing_city, mailing_state, mailing_zip, mailing_country,"
        . " comment"
        . ") VALUES ("
        . " cmat_pl.next_key('a04'),"
        . " '%s', '%s', '%s', '%s',"
        . " '%s',"
        . " '%s', '%s',"
        . " '%s', '%s', '%s', '%s',"
        . " '%s'"
        . ");";
    }
    $q = sprintf($q_format,
                 $this->name,
                 $this->email,
                 $this->phone,
                 $this->fax,
                 $this->contact_id,
                 $this->mailing_street_address_1,
                 $this->mailing_street_address_2,
                 $this->mailing_city,
                 $this->mailing_state,
                 $this->mailing_zip,
                 $this->mailing_country,
                 $this->comment);
    Db::query($q);
  }


  function getIdFieldName() {
    return "school_id";
  }


  function getTypePrefix() {
    return "a04";
  }


  function getStandardName() {
    return $this->name;
  }


  function getSummaryDetail() {
    return $this->comment;
  }


  function displayDetailTitle() {
    echo "<h1>School Details:</h1>";
    echo sprintf('<a href="d.php?t=%s">List</a>', LISTING_PAGE);
  }


  function displayEditTitle() {
    echo "<h1>School Edit Page</h1>";
  }


  function displayHtmlDetail() {
?>
<div class="schoolGroup group">
  <a href="d.php?t=<?php echo EDIT_PAGE; ?>&amp;id=<?php echo $this->school_id; ?>">Edit</a>
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Name:</th>
            <td><?php echo $this->htmlGet('name'); ?></td>
          </tr>
        </table>
      </div>
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">E-mail:</th>
            <td><?php echo $this->htmlGet('email'); ?></td>
          </tr>
          <tr>
            <th scope="row">Phone:</th>
            <td><?php echo $this->htmlGet('phone'); ?></td>
          </tr>
          <tr>
            <th scope="row">Fax:</th>
            <td><?php echo $this->htmlGet('fax'); ?></td>
          </tr>
          <tr>
            <th scope="row">Contact:</th>
            <td><?php echo $this->htmlGet('contact_name'); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="rightColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Street Address 1:</th>
            <td><?php echo $this->htmlGet('mailing_street_address_1'); ?></td>
          </tr>
          <tr>
            <th scope="row">Street Address 2:</th>
            <td><?php echo $this->htmlGet('mailing_street_address_2'); ?></td>
          </tr>
          <tr>
            <th scope="row">City:</th>
            <td><?php echo $this->htmlGet('mailing_city'); ?></td>
          </tr>
          <tr>
            <th scope="row">State:</th>
            <td><?php echo $this->htmlGet('mailing_state'); ?></td>
          </tr>
          <tr>
            <th scope="row">Zip:</th>
            <td><?php echo $this->htmlGet('mailing_zip'); ?></td>
          </tr>
          <tr>
            <th scope="row">Country:</th>
            <td><?php echo $this->htmlGet('mailing_country') ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="bottomRow clearingBox">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Comment:</th>
            <td><?php echo $this->htmlGet('comment'); ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
<?php
  }


  function displayHtmlEdit() {
    $name = new InputElem('name', $this->name);
    $email = new InputElem('email', $this->email);
    $phone = new InputElem('phone', $this->phone, 'text', 'phone');
    $fax = new InputElem('fax', $this->fax, 'text', 'phone');
    $contact = new LookupElem('contact', 'a01', $this->contact_id, $this->contact_name);
    $street1 = new InputElem('mailing_street_address_1', $this->mailing_street_address_1);
    $street2 = new InputElem('mailing_street_address_2', $this->mailing_street_address_2);
    $city = new InputElem('mailing_city', $this->mailing_city);
    $state = new InputElem('mailing_state', $this->mailing_state);
    $zip = new InputElem('mailing_zip', $this->mailing_zip);
    $country = new InputElem('mailing_country', $this->mailing_country);
?>
<div class="schoolGroup group">
  <div class="details">
    <div class="leftColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Name:</th>
            <td><?php $name->disp(); ?></td>
          </tr>
        </table>
      </div>
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">E-mail:</th>
            <td><?php $email->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Phone:</th>
            <td><?php $phone->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Fax:</th>
            <td><?php $fax->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Contact:</th>
            <td><?php $contact->disp(); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="rightColumn">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Street Address 1:</th>
            <td><?php $street1->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Street Address 2:</th>
            <td><?php $street2->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">City:</th>
            <td><?php $city->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">State:</th>
            <td><?php $state->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Zip:</th>
            <td><?php $zip->disp(); ?></td>
          </tr>
          <tr>
            <th scope="row">Country:</th>
            <td><?php $country->disp(); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="bottomRow clearingBox">
      <div class="subGroup">
        <table>
          <tr>
            <th scope="row">Comment:</th>
            <td><textarea name="comment" cols="60" rows="8"><?php echo $this->comment; ?></textarea></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    new PhoneNumberField('phone');
    new PhoneNumberField('fax');
  </script>
</div>
<?php
  }


  function displayHtmlTableHeader() {
    $colClasses = array('name', 'phone', 'fax', 'contact', 'street1', 'street2', 'city', 'state', 'zip', 'country', 'comment');
    $colLabels = array('Name', 'Phone', 'Fax', 'Contact', 'Street Address 1', 'Street Address 2',
                  'City', 'State', 'Zip', 'Country', 'Comment');

    echo '<thead><tr>';
    foreach ($colLabels as $i => $val) {
      echo sprintf('<th scope="col">%s</th>', $val);
    }
    echo '</tr></thead>';
  }


  function displayHtmlTableRow($isEven) {
    $rHtmlFormat = '<tr class="%s"><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
    echo sprintf($rHtmlFormat,
                 $isEven ? "even" : "odd",
                 $this->htmlGet('linked_name'),
                 $this->htmlGet('phone'),
                 $this->htmlGet('fax'),
                 $this->htmlGet('contact_name'),
                 $this->htmlGet('mailing_street_address_1'),
                 $this->htmlGet('mailing_street_address_2'),
                 $this->htmlGet('mailing_city'),
                 $this->htmlGet('mailing_state'),
                 $this->htmlGet('mailing_zip'),
                 $this->htmlGet('mailing_country'),
                 $this->htmlGet('comment'));
  }


  function htmlGet($field) {
    $ret = null;
    switch ($field) {
    case 'linked_name':
      $ret = sprintf('<a href="d.php?t=%s&amp;id=%s">%s</a>', SCHOOL_DETAIL_PAGE, $this->school_id, $this->name);
      break;
    default:
      $ret = parent::htmlGet($field);
    }
    return $ret;
  }

}

?>
