<?php
namespace App\Mappers;

class AsciiMapper {
    public static function toAscii($string) {
        $asciiString = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $asciiString .= ord($string[$i]);
        }
        return $asciiString;
    }
}