<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
?>
<html>
    <head>
        <title>CMAT-o-Matic Installer</title>
    </head>
    <body>
        <h1>Welcome to the CMAT-o-Matic Installer</h1>
        <p>Describe CMAT-o-Matic here</p>
        <h1>Installation Settings</h1>
        <form method="POST">
            <fieldset>
                <legend>Database Connection Settings</legend>
                <table>
                    <tr>
                        <td><label for="hostname">Hostname<label></td>
                        <td><input type="text" id="hostname" name="hostname"/></td>
                    </tr>
                    <tr>
                        <td><label for="username">User Name<label></td>
                        <td><input type="text" id="username" name="username"/></td>
                    </tr>
                    <tr>
                        <td><label for="password">Password<label></td>
                        <td><input type="password" id="password" name="password"/></td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Application Settings</legend>
                <table>
                    <tr>
                        <td><label for="db_prefix">Database Table Prefix<label></td>
                        <td><input type="text" id="db_prefix" name="db_prefix"/></td>
                    </tr>
                </table>
            </fieldset>
            <input type="submit" name="install" value="Install"/>
        </form>
    </body>
</html>
