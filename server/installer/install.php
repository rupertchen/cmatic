<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

require_once '../util/Db.php';
require_once '../util/TextUtils.php';


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
function step2Body($confData = false) {
    // TODO: Give proper instructions on what to do next on this page
    if ($confData) {
?>
        <h1>Action Required: CMATic Configuration Not Saved</h1>
        <p>
            The CMATic installer was unable to write to the settings file.
            Copy the following configuration into <kbd>&lt;CMATic root&gt;/server/.cmatic_config</kbd> before continuing.
            Remember to replace the password placeholder text with the real password.
        </p>
        <pre><code><?php echo TextUtils::htmlize($confData); ?></code></pre>
<?php } ?>
        <h1>CMATic Installed</h1>
        <p>
            The database has been setup and is ready for CMATic.
        </p>
        <h1>Now What?</h1>
        <p>
            Congratulations!
            You are now ready to create events for your tournament and add competitors.
        </p>
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
    <link href="../resources/css/installer.css" type="text/css" rel="stylesheet"/>
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
        } else if (!preg_match('/^[a-zA-Z][_a-zA-Z0-9]*$/', $requestParams['tablePrefix'])) {
            $errorMessages[] = 'The table prefix must start with a letter and may only contain letters, numbers, and underscores';
        }

        if (count($errorMessages) > 0) {
            throw new InstallerException($errorMessages);
        }

        try {
            // TODO: Probably should refactor this logic out into a function

            // Create the schema in the DB
            // Open the file, replace the table prefix, strip comments
            $schemaFile = 'schema/postgres8.sql';
            $sql = fread(fopen($schemaFile, 'r'), filesize($schemaFile));
            $sql = preg_replace('/cmatic_/', $requestParams['tablePrefix'], $sql);
            // Strip away inline comments beginning with "--" or "//"
            $sql = PdoHelper::removeComments($sql);

            $db = new PDO(PdoHelper::getPgsqlDsn($requestParams['host'], $requestParams['port'], $requestParams['db']),
                    $requestParams['user'], $requestParams['password']);
            // The squeaky wheel gets the oil, make it known when things went wrong.
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->beginTransaction();
            try {
                foreach ($sql as $singleSql) {
                    $singleSql = trim($singleSql);
                    if (!empty($singleSql)) {
                        $db->exec($singleSql);
                    }
                }
                $db->commit();
                // This should be in a "finally" block, but PHP doesn't have such a thing?!
                $db = null;
            } catch (Exception $e) {
                // Roll back the transaction and then re-throw the exception
                $db->rollBack();
                throw $e;
            }

            // Create the configuration file
            $confFilename = '../.cmatic_conf.php';
            $passwordPlaceholder = 'password_placeholder';
            $confFile = @fopen($confFilename, 'w');
            $confData = '<?php' . "\n"
                . '    // This file was generated by CMATic on ' . date('r') . '.' .  "\n"
                . '    // Don\'t modify unless you know what you\'re doing!!' . "\n"
                . '    $CONF = array();' . "\n"
                . '    $CONF[\'db\'] = array();' . "\n"
                . '    $CONF[\'db\'][\'host\'] = \'' . TextUtils::slashForPhpStringLiteral($requestParams['host']) . '\';' . "\n"
                . '    $CONF[\'db\'][\'port\'] = \'' . TextUtils::slashForPhpStringLiteral($requestParams['port']) . '\';' . "\n"
                . '    $CONF[\'db\'][\'db\'] = \'' . TextUtils::slashForPhpStringLiteral($requestParams['db']) . '\';' . "\n"
                . '    $CONF[\'db\'][\'user\'] = \'' . TextUtils::slashForPhpStringLiteral($requestParams['user']) . '\';' . "\n"
                . '    $CONF[\'db\'][\'password\'] = \'' . TextUtils::slashForPhpStringLiteral($passwordPlaceholder) . '\';' . "\n"
                . '    $CONF[\'app\'] = array();' . "\n"
                . '    $CONF[\'app\'][\'tablePrefix\'] = \'' . TextUtils::slashForPhpStringLiteral($requestParams['tablePrefix']) . '\';' . "\n"
                . '?>' . "\n";

            // Save it if possible, otherwise display it.
            if (!@fwrite($confFile, preg_replace('/' . $passwordPlaceholder . '/', TextUtils::slashForPhpStringLiteral($requestParams['password']), $confData))) {
                // save failed, display
                step2Body($confData);
            } else {
                step2Body();
            }
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
