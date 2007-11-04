<?php

class CmaticApiException extends Exception {
    function __construct($msg) {
        parent::__construct($msg);
    }
}

class CmaticInstallerException extends Exception {
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
