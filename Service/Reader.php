<?php

final class Reader {
    private string $pattern = '/\b[A-Za-z_][A-Za-z0-9_]*\b/';

    public function classify(string $text): int {
        if ($text === '') { return -1; }

        switch ($text) {
            case '#':
                return 1;
            case '@':
                return 2;
            case '-':
            case '*':
            case '+':
                return 3;
            case "\n":
                return 4;
            case '_':
                return 5;
            case '>':
                return 6;
            case '[':
            case ']':
            case '(':
            case ')':
                return 7;
            case '!':
                return 8;
            case '`':
                return 9;
            case '~':
                return 10;
            case '|':
                return 11;
            case '\\':
                return 12;
            default:
                return 0;
        }
    }

    public function semantic(array $tokens): array {
        $semantic = [];

        $matrix = [
            /* 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 */
            [1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0], /* 0 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], /* 1 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], /* 2 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], /* 3 */
            [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1], /* 4 */
            [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0], /* 5 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], /* 6 */
            [1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0], /* 7 */
            [0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0], /* 8 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0], /* 9 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0], /* 10 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0], /* 11 */
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], /* 12 */
        ];

        

        $count = count($tokens);
        for ($index = 0; $index < $count; $index++) {
            $current = $tokens[$index];
            $next = $tokens[$index + 1] ?? 4;
            $semantic[] = $matrix[$current][$next];
        }

        return $semantic;
    }

    public function tokenize(string $text): array {
        if ($text === '') { return []; }
        preg_match_all($this->pattern, $text, $matches);

        return $matches[0] ?? [];
    }
}
