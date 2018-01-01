<?php

namespace Phpactor\ClassFileConverter\Adapter\Simple;

/**
 * Return the class name from a file.
 *
 * Based on http://stackoverflow.com/questions/7153000/get-class-name-from-file
 */
class ClassScanner
{
    public function getClassNameFromFile($file)
    {
        $fp = fopen($file, 'r');

        $class = $namespace = $buffer = '';
        $i = 0;

        while (!$class) {
            if (feof($fp)) {
                break;
            }

            // Read entire lines to prevent keyword truncation
            for ($line = 0; $line <= 20; $line++) {
                $buffer .= fgets($fp);
            }
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            for (; $i < count($tokens); $i++) {
                if ($tokens[$i][0] === \T_NAMESPACE) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\' . $tokens[$j][1];
                        } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === \T_CLASS) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === \T_STRING) {
                            $class = $tokens[$i + 2][1];
                            break 2;
                        }
                    }
                }
            }
        }

        if (!trim($class)) {
            return;
        }

        fclose($fp);

        return ltrim($namespace . '\\' . $class, '\\');
    }
}
