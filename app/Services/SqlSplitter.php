<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Découpe un script SQL en instructions (gère les chaînes, les commentaires « -- » et « # »).
 * Ne gère pas la directive DELIMITER : les scripts du projet n'utilisent pas de procédures stockées.
 */
final class SqlSplitter
{
    /** @return list<string> */
    public static function split(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $quote = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($quote !== null) {
                $buffer .= $char;
                if ($char === '\\' && $quote !== '`') {
                    $buffer .= $next;
                    $i++;
                } elseif ($char === $quote) {
                    if ($next === $quote) {
                        $buffer .= $next;
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
            } elseif (($char === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) || $char === '#') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                $buffer .= "\n";
            } elseif ($char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i = $end === false ? $length : $end + 1;
            } elseif ($char === ';') {
                self::push($statements, $buffer);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }
        self::push($statements, $buffer);
        return $statements;
    }

    private static function push(array &$statements, string $buffer): void
    {
        $buffer = trim($buffer);
        if ($buffer !== '') {
            $statements[] = $buffer;
        }
    }
}
