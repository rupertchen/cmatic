<?php

  // Does a basic import of data from Po's CMAT18 registration files.
  // There are a few quirks and manual touch-ups that are required.
  // 1) Only "standard" individual events are imported. Events without
  //    age groups such as push hands and group events will be skipped
  //    and must be added manually.
  // 2) Weight for push hands competitors will need to be entered manually
  //    by looking at which push hands event is listed in the 


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
      printf("Importing: %s.\n", $fileName);
      try {
	importFile($fileName);
      } catch (Exception $e) {
	printf("\tSkipped: %s\n", $e->getMessage());
      }
    } else {
      printf("Skipped: %s. File does not exist.\n", $fileName);
    }
  }
}


function importFile($fileName) {
  $reg = parseLines(file($fileName));
  if (isRegistrationAlreadyImported($reg)) {
    throw new Exception('Similar competitor already exists.');
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
  return (bool) $reg->getCompetitorId();
}


function insertRegistration(Registration $reg) {
  $competitorSql = $reg->getInsertCompetitorSql();

  $conn = PdoHelper::GetPdo();
  $conn->beginTransaction();
  $conn->query($competitorSql);
  $conn->commit();
  $conn = null;

  $conn = PdoHelper::GetPdo();
  $conn->beginTransaction();
  foreach ($reg->getIndividualEventSqls() as $sql) {
    $conn->query($sql);
  }
  $conn->commit();
  $conn = null;

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
    return $this->_getRaw('First Name:');
  }


  function getLastName() {
    return $this->_getRaw('Last Name:');
  }


  function getSexId() {
    switch ($this->_getRaw('Gender:')) {
    case 'Female':
      return DbId::SEX_FEMALE;
    case 'Male':
      return DbId::SEX_MALE;
    default:
      return DbId::SEX_NA;
    }
  }


  function getAge() {
    switch ($this->_getRaw('Age Group:')) {
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
      return 0;
    }
  }


  function getDivisionId() {
    switch ($this->_getRaw('Experience Level:')) {
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
    $street = $this->_getRaw('Address Street:');
    if (is_array($street)) {
      return implode(' ', $street);
    } else {
      return $street;
    }
  }


  function getCity() {
    return $this->_getRaw('Address City:');
  }


  function getState() {
    return $this->_getRaw('Address State:');
  }


  function getPostalCode() {
    return $this->_getRaw('Address Zip:');
  }


  function getCountry() {
    return $this->_getRaw('Address Country:');
  }


  function getEmail() {
    return $this->_getRaw('Email Address:');
  }


  function getPhone1() {
    return $this->_getRaw('Phone Number:');
  }


  function getEmergencyContactName() {
    $name = array($this->_getRaw('Emergency Contact Last Name:'),
		  $this->_getRaw('Emergency Contact First Name:'));
    return implode(', ', $name);
  }


  function getEmergencyContactRelation() {
    return null;
  }


  function getEmergencyContactPhone() {
    return $this->_getRaw('Emergency Contact Phone Number:');
  }


  function getAmountPaid() {
    $fees = $this->_getRaw('Fees:'); 
    return (float)$fees;
    if (is_numeric($fees)) {
      return $fees;
    } else {
      return 0;
    }
  }


  function getComments() {
    $bits = array();
    $reg_num = $this->_getRaw('Registration Number:');
    if ($reg_num) {
      $bits[] = 'Registration Number: ' . $reg_num;
    }
    if ($this->_raw['All-around:'][0] == 'Yes') {
      $bits[] = 'All-around: Yes';
    }
    return implode('; ', $bits);
  }


  function _getRaw($label) {
    $x = $this->_raw[$label];
    if (!$x) {
      return null;
    }

    if (count($x) == 1) {
      return $x[0];
    }
    return $x;
  }


  function getIndividualEventSqls() {
    $formNames = array_unique(array_map('trim',
					explode(',', $this->_raw['Events:'][0])));
    $sqls = array();

    $competitorId = $this->getCompetitorId();
    if (!$competitorId) {
      throw new Exception('Could not determine competitor id.');
    }
    $divisionId = $this->getDivisionId();
    $sexId = $this->getSexId();
    $age = $this->getAge();
    foreach ($formNames as $formName) {
      $formId = DbId::FormNameToId($formName);
      $eventId = $this->getEventId($divisionId,
				   $sexId,
				   $age,
				   $formId);
      if (!$eventId) {
	printf("\tUnable to auto-place competitor (%d:%s, %s) in form (%d:%s)\n",
	       $competitorId,
	       $this->getLastName(),
	       $this->getFirstName(),
	       $formId,
	       $formName);
      } else {
	$sqls[] = sprintf('insert into %s (event_id, competitor_id) values (%d, %d)',
			  CmaticSchema::getTypeDbTable('scoring'),
			  $eventId,
			  $competitorId);
      }
    }

    return $sqls;
  }


  function getEventId($divisionId, $sexId, $age, $formId) {
    $sql = sprintf('select event_id from %s where division_id = %d and sex_id = %d and age_group_id in (%s) and form_id = %d',
		   CmaticSchema::getTypeDbTable('event'),
		   $divisionId,
		   $sexId,
		   implode(', ', DbId::AgeToAgeGroupIds($age)),
		   $formId);
    $conn = PdoHelper::GetPdo();
    $results = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    switch (count($results)) {
    case 0:
      return null;
    case 1:
      return $results[0]['event_id'];
    default:
      throw new Exception('Multiple possible event IDs');
    }
  }


  function getCompetitorId() {
    $clauses = array();
    $cols = $this->getCompetitorColumns();
    foreach (array('first_name', 'last_name', 'sex_id', 'age', 'division_id') as $col) {
      $clauses[] = sprintf('%s = %s', $col, $cols[$col]);
    }
    $sql = sprintf('select competitor_id from %s where %s',
		   CmaticSchema::getTypeDbTable('competitor'),
		   implode(' and ', $clauses));
    $conn = PdoHelper::GetPdo();
    $results = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    switch (count($results)) {
    case 0:
      return null;
    case 1:
      return $results[0]['competitor_id'];
    default:
      throw new Exception('Multiple possible competitor IDs');
    }
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

  const AGE_YOUNG_CHILD = 1;
  const AGE_CHILD = 2;
  const AGE_TEEN = 3;
  const AGE_ADULT_WUSHU = 4;
  const AGE_SENIOR_WUSHU = 5;
  const AGE_ADULT_INTERNAL = 6;
  const AGE_SENIOR_INTERNAL = 7;
  const AGE_NA = 8;


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


  function AgeToAgeGroupIds($age) {
    if ($age < 8) {
      return array(self::AGE_YOUNG_CHILD);
    }
    if ($age < 13) {
      return array(self::AGE_CHILD);
    }
    if ($age < 18) {
      return array(self::AGE_TEEN);
    }
    if ($age < 36) {
      return array(self::AGE_ADULT_WUSHU, self::AGE_ADULT_INTERNAL);
    }
    if ($age < 53) {
      return array(self::AGE_SENIOR_WUSHU, self::AGE_ADULT_INTERNAL);
    }
    if ($age >= 53) {
      return array(self::AGE_SENIOR_WUSHU, self::AGE_SENIOR_INTERNAL);
    }
  }
}
