<?php

require_once 'db/BaseDbObj.php';
require_once 'html/InputElem.php';
require_once 'util/Db.php';
require_once 'util/TextUtils.php';

class ContactObj extends BaseDbObj {

  var $contact_id;

  var $salutation;
  var $first_name;
  var $last_name;
  var $middle_name;
  var $cell_phone;
  var $evening_phone;
  var $day_phone;
  var $email;
  var $mailing_street_address_1;
  var $mailing_street_address_2;
  var $mailing_city;
  var $mailing_state;
  var $mailing_zip;
  var $mailing_country;
  var $comment;
  var $aim;
  var $yim;
  var $msn;
  var $icq;
  var $known_languages__english;
  var $known_languages__mandarin;
  var $known_languages__cantonese;


  function getOneFromContactId($id) {
    $c = ContactObj::getFromSomeId('contact_id', $id);
    return $c[0];
  }


  function getFromSomeId($fieldName, $id) {
    $q = sprintf("SELECT * FROM cmat_core.contact WHERE %s = '%s';",
                 TextUtils::cleanTextForSql($fieldName),
                 TextUtils::cleanTextForSql($id));
    $r = Db::query($q);
    $cs = array();
    while ($row = Db::fetch_array($r)) {
      $c = new ContactObj();
      $c->loadContactFromArray($row);
      $c->loadFromDb = true;
      $cs[] = $c;
    }
    Db::free_result($r);
    return $cs;
  }


  function loadFromPostArray($p) {
    $klEnglish = TextUtils::postVarToString($p['known_languages__english']);
    $klMandarin = TextUtils::postVarToString($p['known_languages__mandarin']);
    $klCantonese = TextUtils::postVarToString($p['known_languages__cantonese']);

    $p['known_languages'] = $klEnglish . $klMandarin . $klCantonese;

    $this->loadContactFromArray($p);
  }

  function loadContactFromArray($a) {
    $this->contact_id = $a['contact_id'];
    $this->salutation = $a['salutation'];
    $this->first_name = $a['first_name'];
    $this->last_name = $a['last_name'];
    $this->middle_name = $a['middle_name'];
    $this->cell_phone = $a['cell_phone'];
    $this->evening_phone = $a['evening_phone'];
    $this->day_phone = $a['day_phone'];
    $this->email = $a['email'];
    $this->mailing_street_address_1 = $a['mailing_street_address_1'];
    $this->mailing_street_address_2 = $a['mailing_street_address_2'];
    $this->mailing_city = $a['mailing_city'];
    $this->mailing_state = $a['mailing_state'];
    $this->mailing_zip = $a['mailing_zip'];
    $this->mailing_country = $a['mailing_country'];
    $this->comment = $a['comment'];
    $this->aim = $a['aim'];
    $this->yim = $a['yim'];
    $this->msn = $a['msn'];
    $this->icq = $a['icq'];
    list($this->known_languages__english,
         $this->known_languages__mandarin,
         $this->known_languages__cantonese)
      = TextUtils::bitsToArray($a['known_languages']);
  }


  function save() {
    $q_format = "";
    if ($this->loadFromDb) {
      $q_format = "UPDATE cmat_core.contact SET"
        . " salutation = '%s',"
        . " first_name = '%s',"
        . " last_name = '%s',"
        . " middle_name = '%s',"
        . " cell_phone = '%s',"
        . " evening_phone = '%s',"
        . " day_phone = '%s',"
        . " email = '%s',"
        . " mailing_street_address_1 = '%s',"
        . " mailing_street_address_2 = '%s',"
        . " mailing_city = '%s',"
        . " mailing_state = '%s',"
        . " mailing_zip = '%s',"
        . " mailing_country = '%s',"
        . " comment = '%s',"
        . " aim = '%s',"
        . " yim = '%s',"
        . " msn = '%s',"
        . " icq = '%s',"
        . " known_languages = B'%s'"
        . " WHERE contact_id = '"
        . TextUtils::cleanTextForSql($this->contact_id)
        . "';";
    } else {
      $q_format = "INSERT INTO cmat_core.contact ("
        . " contact_id,"
        . " salutation, first_name, last_name, middle_name,"
        . " cell_phone, evening_phone, day_phone,"
        . " email,"
        . " mailing_street_address_1, mailing_street_address_2,"
        . " mailing_city, mailing_state, mailing_zip, mailing_country,"
        . " comment,"
        . " aim, yim, msn, icq,"
        . " known_languages"
        . ") VALUES ("
        . " cmat_pl.next_key('a01'),"
        . " '%s', '%s', '%s', '%s',"
        . " '%s', '%s', '%s',"
        . " '%s',"
        . " '%s', '%s',"
        . " '%s', '%s', '%s', '%s',"
        . " '%s',"
        . " '%s', '%s', '%s', '%s',"
        . " B'%s'"
        . ");";
    }
    $q = sprintf($q_format,
                 $this->salutation,
                 $this->first_name,
                 $this->last_name,
                 $this->middle_name,
                 $this->cell_phone,
                 $this->evening_phone,
                 $this->day_phone,
                 $this->email,
                 $this->mailing_street_address_1,
                 $this->mailing_street_address_2,
                 $this->mailing_city,
                 $this->mailing_state,
                 $this->mailing_zip,
                 $this->mailing_country,
                 $this->comment,
                 $this->aim,
                 $this->yim,
                 $this->msn,
                 $this->icq,
                 $this->known_languages__english
                 . $this->known_languages__mandarin
                 . $this->known_languages__cantonese);
    Db::query($q);
  }


  function getFullName() {
    return TextUtils::makeFullName($this->last_name, $this->first_name, $this->middle_name);
  }


  function htmlGet($field) {
    // Retrieve Data
    switch ($field) {
    case 'full_name':
      $ret = sprintf("<a href=\"d.php?a&amp;=request&amp;t=%s&amp;id=%s\">%s</a>",
                     CONTACT_DETAIL_PAGE,
                     $this->contact_id,
                     TextUtils::htmlize($this->getFullName()));
      break;
    case 'known_languages':
      $langs = array();
      if ($this->known_languages__english) {
        $langs[] = TextUtils::numToLanguage(0);
      }
      if ($this->known_languages__mandarin) {
        $langs[] = TextUtils::numToLanguage(1);
      }
      if ($this->known_languages__cantonese) {
        $langs[] = TextUtils::numToLanguage(2);
      }
      $ret = implode(', ', $langs);
      break;
    default:
      $ret = parent::htmlGet($field);
    }
    
    // Extra processing
    if ($field == 'comment') {
      $ret = str_replace("\n", '<br/>', $ret);
    }
    return $ret;
  }


  function getIdFieldName() {
    return "contact_id";
  }


  function getTypePrefix() {
    return "a01";
  }


  function getContactId() {
    return $this->contact_id;
  }


  function getStandardName() {
    return $this->getFullName();
  }


  function getSummaryDetail() {
    return $this->comment;
  }

  function displayDetailTitle() {
?>
<h1>Detail Page: <?php echo $this->htmlGet('full_name'); ?></h1>
<a href="d.php?t=<?php echo LISTING_PAGE; ?>">List</a>
<?php
  }


  function displayEditTitle() {
    echo "<h1>Contact Edit Page</h1>";
  }


  function displayHtmlDetail() {
?>
<div class="contactGroup group">
<h2>Contact Details:</h2>
<a href="d.php?t=<?php echo EDIT_PAGE; ?>&amp;id=<?php echo $this->contact_id; ?>">Edit</a>
<div class="details">
  <div class="leftColumn">
  <div class="subGroup">
  <table>
    <tr>
      <th scope="row">Salutation:</th>
      <td><?php echo $this->htmlGet('salutation'); ?></td>
    </tr>
    <tr>
      <th scope="row">Last Name:</th>
      <td><?php echo $this->htmlGet('last_name'); ?></td>
    </tr>
    <tr>
      <th scope="row">First Name: </th>
      <td><?php echo $this->htmlGet('first_name'); ?></td>
    </tr>
    <tr>
      <th scope="row">Middle Name:</th>
      <td><?php echo $this->htmlGet('middle_name'); ?></td>
    </tr>
  </table>
  </div>
  <div class="subGroup">
  <table>
    <tr>
      <th scope="row">Street Address Line 1:</th>
      <td><?php echo $this->htmlGet('mailing_street_address_1'); ?></td>
    </tr>
    <tr>
      <th scope="row">Street Address Line 2:</th>
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
      <td><?php echo $this->htmlGet('mailing_country'); ?></td>
    </tr>
  </table>
  </div>
  </div>
  <div class="rightColumn">
  <div class="subGroup">
  <table>
    <tr>
      <th scope="row">Cell Phone:</th>
      <td><?php echo $this->htmlGet('cell_phone'); ?></td>
    </tr>
    <tr>
      <th scope="row">Evening Phone:</th>
      <td><?php echo $this->htmlGet('evening_phone'); ?></td>
    </tr>
    <tr>
      <th scope="row">Day Phone:</th>
      <td><?php echo $this->htmlGet('day_phone'); ?></td>
    </tr>
  </table>
  </div>
  <div class="subGroup">
  <table>
    <tr>
      <th scope="row">E-Mail:</th>
      <td><?php echo $this->htmlGet('email'); ?></td>
    </tr>
    <tr>
      <th scope="row">AIM:</th>
      <td><?php echo $this->htmlGet('aim'); ?></td>
    </tr>
    <tr>
      <th scope="row">YIM:</th>
      <td><?php echo $this->htmlGet('yim'); ?></td>
    </tr>
    <tr>
      <th scope="row">MSN:</th>
      <td><?php echo $this->htmlGet('msn'); ?></td>
    </tr>
    <tr>
      <th scope="row">ICQ:</th>
      <td><?php echo $this->htmlGet('icq'); ?></td>
    </tr>
  </table>
  </div>
  <div class="subGroup">
  <table>
    <tr>
       <th scope="row">Known Languages:</th>
       <td><?php echo $this->htmlGet('known_languages'); ?></td>
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
    $firstName = new InputElem('first_name', $this->first_name);
    $lastName = new InputElem('last_name', $this->last_name);
    $middleName = new InputElem('middle_name', $this->middle_name);
    $street1 = new InputElem('mailing_street_address_1', $this->mailing_street_address_1);
    $street2 = new InputElem('mailing_street_address_2', $this->mailing_street_address_2);
    $city = new InputElem('mailing_city', $this->mailing_city);
    $state = new InputElem('mailing_state', $this->mailing_state);
    $zip = new InputElem('mailing_zip', $this->mailing_zip);
    $country = new InputElem('mailing_country', $this->mailing_country);
    $cell = new InputElem('cell_phone', $this->cell_phone);
    $evening = new InputElem('evening_phone', $this->evening_phone);
    $day = new InputElem('day_phone', $this->day_phone);
    $email = new InputElem('email', $this->email);
    $aim = new InputElem('aim', $this->aim);
    $yim = new InputElem('yim', $this->yim);
    $msn = new InputElem('msn', $this->msn);
    $icq = new InputElem('icq', $this->icq);
    $knownLang = new CheckboxArrayElem(array('known_languages__english',
                                             'known_languages__mandarin',
                                             'known_languages__cantonese'),
                                       array('English', 'Mandarin', 'Cantonese'),
                                       array($this->known_languages__english,
                                             $this->known_languages__mandarin,
                                             $this->known_languages__cantonese));
?>
<div class="contactGroup group">
<div class="details">
  <div class="leftColumn">
    <div class="subGroup">
      <table>
        <tr>
          <th scope="row">Salutation:</th>
          <td>
            <select name="salutation">
              <option<?php if ("" == $this->salutation) echo ' selected="selected"'; ?> value="">--None--</option>
              <optgroup label="Standard">
                <option<?php if ("Dr." == $this->salutation) echo ' selected="selected"'; ?>>Dr.</option>
                <option<?php if ("Mr." == $this->salutation) echo ' selected="selected"'; ?>>Mr.</option>
                <option<?php if ("Mrs." == $this->salutation) echo ' selected="selected"'; ?>>Mrs.</option>
                <option<?php if ("Ms." == $this->salutation) echo ' selected="selected"'; ?>>Ms.</option>
              </optgroup>
              <optgroup label="Judges">
                <option<?php if ("Grandmaster" == $this->salutation) echo ' selected="selected"'; ?>>Grandmaster</option>
                <option<?php if ("Great Grandmaster" == $this->salutation) echo ' selected="selected"'; ?>>Great Grandmaster</option>
                <option<?php if ("Madame" == $this->salutation) echo ' selected="selected"'; ?>>Madame</option>
                <option<?php if ("Master" == $this->salutation) echo ' selected="selected"'; ?>>Master</option>
                <option<?php if ("Sifu" == $this->salutation) echo ' selected="selected"'; ?>>Sifu</option>
              </optgroup>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row">First Name:</th>
          <td><?php $firstName->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">Last Name:</th>
          <td><?php $lastName->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">Middle Name:</th>
          <td><?php $middleName->disp(); ?></td>
        </tr>
      </table>
    </div>
    <div class="subGroup">
      <table>
        <tr>
          <th scope="row">Street Address Line 1:</th>
          <td><?php $street1->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">Street Address Line 2:</th>
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
  <div class="rightColumn">
    <div class="subGroup">
      <table>
        <tr>
          <th scope="row">Cell Phone:</th>
          <td><?php $cell->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">Evening Phone:</th>
          <td><?php $evening->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">Day Phone:</th>
          <td><?php $day->disp(); ?></td>
        </tr>
      </table>
    </div>
    <div class="subGroup">
      <table>
        <tr>
          <th scope="row">E-Mail:</th>
          <td><?php $email->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">AIM:</th>
          <td><?php $aim->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">YIM:</th>
          <td><?php $yim->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">MSN:</th>
          <td><?php $msn->disp(); ?></td>
        </tr>
        <tr>
          <th scope="row">ICQ:</th>
          <td><?php $icq->disp(); ?></td>
        </tr>
      </table>
    </div>
    <div class="subGroup">
      <table>
        <tr>
          <th scope="row">Known Languages:</th>
          <td><?php $knownLang->disp(); ?></td>
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
  new PhoneNumberField('cell_phone');
  new PhoneNumberField('evening_phone');
  new PhoneNumberField('day_phone');
</script>
</div>
<?php
  }
  

  function displayHtmlTableHeader() {
    $cols = array('Full Name', 'Email', 'Aim', 'Cell', 'Day Phone', 'Comment');
    echo '<tr>';
    foreach ($cols as $i => $val) {
      echo sprintf('<th scope="col">%s</th>', $val);
    }
    echo '</tr>';
  }


  function displayHtmlTableRow($isEven) {
    $rHtmlFormat = '<tr class="%s"><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
      echo sprintf($rHtmlFormat,
                   $isEven ? "even" : "odd",
                   $this->htmlGet('full_name'),
                   $this->htmlGet('email'),
                   $this->htmlGet('aim'),
                   $this->htmlGet('cell_phone'),
                   $this->htmlGet('day_phone'),
                   $this->htmlGet('comment'));
  }

}

?>
