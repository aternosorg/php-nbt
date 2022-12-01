<?php

namespace Aternos\Nbt\String;

/**
 * https://py2jdbc.readthedocs.io/en/latest/mutf8.html
 * https://docs.oracle.com/javase/8/docs/api/java/io/DataInput.html#modified-utf-8
 * https://docs.oracle.com/javase/6/docs/api/java/io/DataInput.html#readUTF%28%29
 * Good luck
 */
class JavaEncoding
{
    protected static ?JavaEncoding $instance = null;

    /**
     * @return static
     */
    static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param string $string
     * @param string $sourceEncoding
     * @return string
     */
    public function encode(string $string, string $sourceEncoding = "UTF-8"): string
    {
        $result = "";

        $chars = mb_str_split($string, 1, $sourceEncoding);
        foreach ($chars as $char) {
            $c = mb_ord($char, $sourceEncoding);

            if($c === 0) {
                $result .= "\xC0\x80";
                continue;
            }

            if($c <= 0x7F) {
                $result .= chr($c);
                continue;
            }

            if($c <= 0x7FF) {
                $result .= chr(0xC0 | (0x1F & ($c >> 0x06)));
                $result .= chr(0x80 | (0x3F & $c));
                continue;
            }

            if($c <= 0xFFFF) {
                $result .= chr(0xE0 | (0x0F & ($c >> 0x0C)));
                $result .= chr(0x80 | (0x3F & ($c >> 0x06)));
                $result .= chr(0x80 | (0x3F & $c));
                continue;
            }

            $result .= chr(0xED);
            $result .= chr(0xA0 | ((($c >> 0x10) & 0x0F) - 1));
            $result .= chr(0x80 | (($c >> 0x0A) & 0x3f));
            $result .= chr(0xED);
            $result .= chr(0xb0 | (($c >> 0x06) & 0x0f));
            $result .= chr(0x80 | ($c & 0x3f));
        }

        return $result;
    }

    /**
     * @throws StringDataFormatException
     */
    public function decode(string $string, string $outputEncoding = "UTF-8"): string
    {
        $result = "";
        for ($i = 0; $i < strlen($string); $i++) {
            $a = ord($string[$i]);

            if ($a === 0) {
                throw new StringDataFormatException("Invalid NULL byte in string");
            }

            // Single byte character
            if (($a & 0b10000000) === 0b0) {
                $result .= mb_chr($a, $outputEncoding);
                continue;
            }

            $b = ord($string[++$i] ?? "\0");

            // Two byte character
            if (($a & 0b11100000) === 0b11000000) {
                if (($b & 0b11000000) !== 0b10000000) {
                    throw new StringDataFormatException("Invalid \"UTF-8\" sequence");
                }

                $result .= mb_chr((($a & 0x1F) << 6) | ($b & 0x3F), $outputEncoding);
                continue;
            }

            $c = ord($string[++$i] ?? "\0");

            // Maybe six byte character
            if ($a === 0b11101101 && ($b & 0b11110000) === 0b10100000 && ($c & 0b11000000) === 0b10000000) {
                $d = ord($string[$i + 1] ?? "\0");
                $e = ord($string[$i + 2] ?? "\0");
                $f = ord($string[$i + 3] ?? "\0");

                // Six byte character
                if ($d === 0b11101101 && ($e & 0b11110000) === 0b10110000 && ($f & 0b11000000) === 0b10000000) {
                    $result .= mb_chr(0x10000 |
                        ($b & 0x0F) << 0x10 |
                        ($c & 0x3F) << 0x0A |
                        ($e & 0x0F) << 0x06 |
                        ($f & 0x3F), $outputEncoding);

                    $i += 3;
                    continue;
                }
            }

            // Three byte character
            if (($a & 0b11110000) === 0b11100000) {
                if (($b & 0b11000000) !== 0b10000000 || ($c & 0b11000000) !== 0b10000000) {
                    throw new StringDataFormatException("Invalid \"UTF-8\" sequence");
                }

                $result .= mb_chr((($a & 0x0F) << 12) | (($b & 0x3F) << 6) | ($c & 0x3F), $outputEncoding);
                continue;
            }

            throw new StringDataFormatException("Invalid \"UTF-8\" sequence");
        }
        return $result;
    }
}