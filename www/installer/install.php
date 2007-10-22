<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
ini_set('include_path', sprintf('%s:%s', ini_get('include_path'), '/home/serka/eclipse-workspace/cmat-o-matic/php'));

require_once 'util/Db.php';
require_once 'util/TextUtils.php';


/**
 * The initial page (Step 1) for installing
 * CMAT-o-Matic
 */
function step1Body($inputs, $errorMessages = false) {
?>
        <h1>Welcome to the CMAT-o-Matic Installer</h1>
        <p>Describe CMAT-o-Matic here</p>
        <h1>Installation Settings</h1>
<?php
    if ($errorMessages) {
        echo '<div class="errorBox">';
        foreach ($errorMessages as $m) {
            echo '<span class="errorMessage">', $m, '</span>';
        }
        echo '</div>';
    }
?>
        <form method="POST">
            <fieldset>
                <legend>Database Connection Settings</legend>
                <table>
                    <tr>
                        <td><label for="host">Hostname<label></td>
                        <td><input type="text" id="host" name="host" value="<?php echo TextUtils::htmlize($inputs['host']); ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="port">Port<label></td>
                        <td><input type="text" id="port" name="port" value="<?php echo TextUtils::htmlize($inputs['port']); ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="db">Database Name<label></td>
                        <td><input type="text" id="db" name="db" value="<?php echo TextUtils::htmlize($inputs['db']); ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="user">User Name<label></td>
                        <td><input type="text" id="user" name="user" value="<?php echo TextUtils::htmlize($inputs['user']); ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="password">Password<label></td>
                        <td><input type="password" id="password" name="password" value="<?php echo TextUtils::htmlize($inputs['password']); ?>"/></td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Application Settings</legend>
                <table>
                    <tr>
                        <td><label for="tablePrefix">Database Table Prefix<label></td>
                        <td><input type="text" id="tablePrefix" name="tablePrefix" value="<?php echo TextUtils::htmlize($inputs['tablePrefix']); ?>"/></td>
                    </tr>
                </table>
            </fieldset>
            <input type="submit" name="doInstall" value="Install"/>
        </form>
<?php
}

/**
 * Further instructions (Step 2) of installing
 * CMAT-o-Matic
 */
function step2Body() {
    // TODO: Give proper instructions on what to do next on this page
?>
        <h1>Installed!</h1>
        <p>Give more instructions here</p>
<?php
}

class InstallerException extends Exception {
    private $_errorMessages;

    function __construct($errorMessages) {
        parent::__construct('Errors during installation');
        $this->_errorMessages = $errorMessages;
    }

    final function getErrorMessages() {
        return $this->_errorMessages;
    }
}

?>
<html>
<head>
    <META http-equiv="Content-Style-Type" content="text/css">
    <title>CMAT-o-Matic Installer</title>
    <link href="../css/installer.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<?php

$doInstall = isset($_REQUEST['doInstall']);
// We're being somewhat lazy by treating all params, not just the ones we need
$requestParams = TextUtils::undoMagicQuotes($_REQUEST);
if ($doInstall) {
    try {
        // Fail fast before even trying to touch the DB.
        // Possibly report multiple problems here.
        $errorMessages = array();

        if (empty($requestParams['host'])) {
            $errorMessages[] = 'The hostname must be supplied.';
        }
        if (empty($requestParams['port'])) {
            $errorMessages[] = 'The port must be supplied.';
        }
        if (empty($requestParams['db'])) {
            $errorMessages[] = 'The database name must be supplied.';
        }
        if (empty($requestParams['user'])) {
            $errorMessages[] = 'The database user must be supplied.';
        }
        if (empty($requestParams['tablePrefix'])) {
            // This isn't strictly necessary, but will be enforced for now.
            $errorMessages[] = 'The table prefix must be supplied.';
        } else if (!preg_match('/^[a-zA-Z][_a-zA-Z]*$/', $requestParams['tablePrefix'])) {
            $errorMessages[] = 'The table prefix must start with a letter and may only contain letters and underscores';
        }

        if (count($errorMessages) > 0) {
            throw new InstallerException($errorMessages);
        }

        try {
            // TODO: Probably should refactor this logic out into a function
            $db = new PDO(PdoHelper::getPgsqlDsn($requestParams['host'], $requestParams['port'], $requestParams['db']),
                    $requestParams['user'], $requestParams['password']);
            // The squeaky wheel gets the oil, make it known when things went wrong.
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // REMOVE: This is only here so we make some sort of query to test the db
            var_dump($db->query('SELECT now() AS time')->fetch(PDO::FETCH_ASSOC));
            // REMOVE: end.

            // Create the schema in the DB
            // Open the file, replace the
            $schemaFile = 'schema/postgres8.sql';
            $sqlStatements = fread(fopen($schemaFile, 'r'), filesize($schemaFile));
            // TODO: Perform prefix substitution
            $sqlStatements = preg_replace('/cmatic_/', $requestParams['tablePrefix'], $sqlStatements);
            // TODO: Strip comments and return individual queries
            // TODO: Start transaction
            // TODO: Execute query, on failure stop and report the error
            // TODO: Commit transaction

            // Create the configuration file

            // Save it if possible, otherwise display it.


            $db = null;

            step2Body();
        } catch (PDOException $e) {
            throw new InstallerException(array('Problem during install: ' . $e->getMessage()));
        }

    } catch (InstallerException $e) {
        // TODO: This is a poor way to get the errormessages.
        // Make a new exception type and pass the message in there.
        step1Body($requestParams, $e->getErrorMessages());
    }
} else {
    step1Body($requestParams);
}
?>
</body>
</html>
