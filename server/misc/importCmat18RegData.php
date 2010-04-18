<?php

require_once '../util/Db.php';
require_once '../util/TextUtils.php';


$fileNames = getFileNames();
if (count($fileNames) == 0) {
  print "No input file(s).\n";
  exit(1);
 } else {
  importFiles($fileNames);
  exit(0);
 }


/**
 * File names passed in through command line parameters.
 * @return array
 */
function getFileNames() {
  global $argv;

  if (count($argv) > 1) {
    return array_slice($argv, 1);
  } else {
    return array();
  }
}


/**
 * Import data from each file.
 */
function importFiles($fileNames) {
  foreach ($fileNames as $fileName) {
    if (file_exists($fileName)) {
      printf("Importing %s.", $fileName);
      importFile($fileName);
      printf(" Done.\n");
    } else {
      printf("Skipped: %s. File does not exist.\n", $fileName);
    }
  }
}


function importFile($fileName) {
  $reg = parseLines(file($fileName));
  if (isRegistrationAlreadyImported($reg)) {
    return;
  }
  insertRegistration($reg);
}


/**
 * @return Registration
 */
function parseLines($lines) {
  $data = array();
  $lastMarker = null;

  foreach ($lines as $line) {
    $trimmed = trim($line);
    if (isMarker($trimmed)) {
      $lastMarker = $trimmed;
    } else if ($trimmed && $lastMarker) {
      $data[$lastMarker][] = $trimmed;
    }
  }

  return new Registration($data);
}


function isMarker($s) {
  return (bool)preg_match('/^.*:$/', $s);
}


function isRegistrationAlreadyImported($reg) {
  // TODO: Should check that this competitor doesn't already exist.
  return false;
}


function insertRegistration(Registration $reg) {
  var_dump($reg);

  $competitor_sql = $reg->getInsertCompetitorSql();
  var_dump($competitor_sql);

  $individual_event_sqls = $reg->getIndividualEventSqls();
  var_dump($individual_event_sqls);

  if (false) {
    $conn = PdoHelper::GetPdo();
    $conn->beginTransaction();
    $conn->query($competitor_sql);
    $conn->commit();
  }
}


class Registration {

  private $_raw;


  function __construct($raw) {
    $this->_raw = $raw;
  }


  function getInsertCompetitorSql() {
    $competitorCols = $this->getCompetitorColumns();
    return sprintf('insert into %s (%s) values (%s)',
		   CmaticSchema::getTypeDbTable('competitor'),
		   implode(', ', array_keys($competitorCols)),
		   implode(', ', array_values($competitorCols)));
  }


  function getCompetitorColumns() {
    $cols = array('first_name' => $this->getFirstName(),
		  'last_name' => $this->getLastName(),
		  'sex_id' => $this->getSexId(),
		  'age' => $this->getAge(),
		  'division_id' => $this->getDivisionId(),
		  'street_address' => $this->getStreetAddress(),
		  'city' => $this->getCity(),
		  'state' => $this->getState(),
		  'postal_code' => $this->getPostalCode(),
		  'country' => $this->getCountry(),
		  'email' => $this->getEmail(),
		  'phone_1' => $this->getPhone1(),
		  'emergency_contact_name' => $this->getEmergencyContactName(),
		  'emergency_contact_relation' => $this->getEmergencyContactRelation(),
		  'emergency_contact_phone' => $this->getEmergencyContactPhone(),
		  'amount_paid' => $this->getAmountPaid(),
		  'comments' => $this->getComments());
    return array_map(array('Registration', '_formatValue'), $cols);
  }


  function getFirstName() {
    return $this->_raw['First Name:'][0];
  }


  function getLastName() {
    return $this->_raw['Last Name:'][0];
  }


  function getSexId() {
    switch ($this->_raw['Gender:'][0]) {
    case 'Female':
      return DbId::SEX_FEMALE;
    case 'Male':
      return DbId::SEX_MALE;
    default:
      return DbId::SEX_NA;
    }
  }


  function getAge() {
    switch ($this->_raw['Age Group:'][0]) {
    case '7 or younger':
      return 7;
    case '8-12':
      return 12;
    case '13-17':
      return 17;
    case '18-35':
      return 35;
    case '36-52':
      return 52;
    case '53 or older':
      return 53;
    default:
      return null;
    }
  }


  function getDivisionId() {
    switch ($this->_raw['Experience Level:'][0]) {
    case 'Beginner':
      return DbId::DIV_BEGINNER;
    case 'Intermediate':
      return DbId::DIV_INTERMEDIATE;
    case 'Advanced':
      return DbId::DIV_ADVANCED;
    default:
      return DbId::DIV_NA;
    }
  }


  function getStreetAddress() {
    return implode(' ', $this->_raw['Address Street:']);
  }


  function getCity() {
    return $this->_raw['Address City:'][0];
  }


  function getState() {
    return $this->_raw['Address State:'][0];
  }


  function getPostalCode() {
    return $this->_raw['Address Zip:'][0];
  }


  function getCountry() {
    return $this->_raw['Address Country:'][0];
  }


  function getEmail() {
    return $this->_raw['Email Address:'][0];
  }


  function getPhone1() {
    return $this->_raw['Phone Number:'][0];
  }


  function getEmergencyContactName() {
    $name = array($this->_raw['Emergency Contact Last Name:'][0],
		  $this->_raw['Emergency Contact First Name:'][0]);
    return implode(', ', $name);
  }


  function getEmergencyContactRelation() {
    return null;
  }


  function getEmergencyContactPhone() {
    return $this->_raw['Emergency Contact Phone Number:'][0];
  }


  function getAmountPaid() {
    return $this->_raw['Fees:'][0];
  }


  function getComments() {
    switch ($this->_raw['All-around:'][0]) {
    case 'Yes':
      return 'All-around: Yes';
    default:
      return 'All-around: No';
    }
  }


  function getIndividualEventSqls() {
    // TODO:
    $form_names = explode(',', $this->_raw['Events:'][0]);
    $form_names = array_map('trim', $form_names);
    var_dump($form_names);
    $form_ids = array_map(array('DbId', 'FormNameToId'), $form_names);
    var_dump($form_ids);
    $sqls = array();
    return $sqls;
  }


  private function _formatValue($v) {
    if (is_int($v)) {
      return $v;
    } else if (is_float($v)) {
      return $v;
    } else {
      return sprintf("'%s'", TextUtils::cleanTextForSql($v));
    }
  }

}


/**
 * Hard-coded IDs that match exactly what is in the DB.
 */
class DbId {

  const SEX_NA = 1;
  const SEX_MALE = 2;
  const SEX_FEMALE = 3;

  const DIV_NA = 1;
  const DIV_BEGINNER = 2;
  const DIV_INTERMEDIATE = 3;
  const DIV_ADVANCED = 4;


  static function FormNameToId($name) {
    switch ($name) {
    case 'Traditional Northern':
      return 1;
    case 'Traditional Southern':
      return 2;
    case 'Traditional Other':
      return 3;
    case 'Traditional Short Weapon':
      return 4;
    case 'Traditional Long Weapon':
      return 5;
    case 'Traditional Other Weapon':
      return 6;
    case 'Contemporary Long Fist':
      return 7;
    case 'Contemporary Southern Fist':
      return 8;
    case 'Contemporary Other':
      return 9;
    case 'Contemporary Nandu Long Fist':
      return 10;
    case 'Contemporary Nandu Southern Fist':
      return 11;
    case 'Contemporary Straightsword':
      return 12;
    case 'Contemporary Broadsword':
      return 13;
    case 'Contemporary Southern Broadsword':
      return 14;
    case 'Contemporary Spear':
      return 15;
    case 'Contemporary Staff':
      return 16;
    case 'Contemporary Southern Staff':
      return 17;
    case 'Contemporary Other Weapon':
      return 18;
    case 'Internal 42 Taiji (compulsory)':
      return 19;
    case 'Internal 24 Yang':
      return 20;
    case 'Internal 24 Chen':
      return 21;
    case 'Internal Open Yang':
      return 22;
    case 'Internal Open Chen':
      return 23;
    case 'Internal Xingyi':
      return 24;
    case 'Internal Bagua':
      return 25;
    case 'Internal Sun Taiji':
      return 26;
    case 'Internal Guang Ping':
      return 27;
    case 'Internal Other':
      return 28;
    case 'Internal 42 Taiji Straightsword (compulsory)':
      return 29;
    case 'Internal Taiji Sword':
      return 30;
    case 'Internal Other Taiji Weapon':
      return 31;
    case 'Internal Other Weapon':
      return 32;
    case 'Push Hands Female <135 lbs':
    case 'Push Hands Female 135+ lbs':
    case 'Push Hands Male <145 lbs':
    case 'Push Hands Male 145-175 lbs':
    case 'Push Hands Male 176-205 lbs':
    case 'Push Hands Male 205+ lbs':
      return 33;
    case 'Sparring Set':
      return 34;
    case 'Group Set - External':
      return 35;
    case 'Group Set - Internal':
      return 36;
    case 'Group Set - Guang Ping':
      return 37;
    }
  }
}
