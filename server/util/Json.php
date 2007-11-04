<?php

class Json {

    /**
     * This works on the idea that array_keys() will return
     * a sequential array; hence, a sequential array's keys will
     * be equal to the keys of its keys, but an associative array's
     * keys will not.
     */
    private static function isAssociative($a) {
        if (!is_array($a) || empty($a)) {
            // We consider empty arrays to be sequential arrays
            return false;
        } else {
            $keys = array_keys($a);
            return array_keys($keys) !== $keys;
        }
    }


    /**
     * Alias for isAssociative()
     */
    private static function isObject($o) {
        return Json::isAssociative($o);
    }


    /**
     * Encode the object in JSON.
     */
    public static function encode($o) {
        $json = '';
        if (is_null($o)) {
            $json = 'null';
        } else if (is_object($o) || is_resource($o)) {
            // can't encode objects or resources
        } else if (is_float($o) || is_int($o)) {
            // encode numbers as Javascript numbers
            $json = strval($o);
        } else if (is_string($o)) {
            $json = Json::_encodeString($o);
        } else if (is_array($o)) {
            if (Json::isObject($o)) {
                $json = Json::_encodeObject($o);
            } else {
                $json = Json::_encodeArray($o);
            }
        }
        return $json;
    }


    /**
     * Encode a string as a string in JSON.
     */
    private static function _encodeString($s) {
        $t = array(
            "\"" => "\\\"",
            "\\" => "\\\\",
            "\/" => "\\\/",
            "\b" => "\\b",
            "\f" => "\\f",
            "\n" => "\\n",
            "\r" => "\\r",
            "\t" => "\\t");
        return '"' . strtr($s, $t) . '"';
    }

    /**
     * Encode a sequential aray as an array in JSON.
     */
    private static function _encodeArray($a) {
        return '[' . implode(', ', array_map(array('Json', 'encode'), $a)) . ']';
    }


    /**
     * Encode an associative array as an object in JSON.
     */
    private static function _encodeObject($o) {
        $pairs = array();
        foreach ($o as $k => $v) {
            $pairs[] = Json::encode($k) . ':' . Json::encode($v);
        }
        return '{' . implode(', ', $pairs) . '}';
    }
}
?>
