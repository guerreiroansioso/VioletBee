<?php

final class Reader {
	private string $pattern = '/\n|\S+/u';

	public function classify(string $text): int {
		if ($text === '') { return -1; }

		switch ($text) {
			case '#':
			case '##':
			case '###':
			case '####':
			case '#####':
			case '######':
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

		/* 0 = Skip, 1 = Text, 2 = Heading, */
		/* 3 = List, 4 = End block */
		$matrix = [
			[1, 0, 0, 0, 4, 0, 0, 0, 0, 0, 0, 0, 0],
			[2, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0],
			[1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			[1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0],
			[1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0],
			[1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0],
			[1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0],
			[1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
		];

		foreach ($tokens as $i => $token) {
			$next = $tokens[$i + 1] ?? 4;
			$semantic[] = $matrix[$token][$next];
		}

		return $semantic;
	}

	public function tokenize(string $text): array {
		if ($text === '') { return []; }
		preg_match_all($this->pattern, $text, $matches);

		return $matches[0] ?? [];
	}

	public function compile(string $text): array {
		$matches = $this->tokenize($text);

		$tokens = [];
		foreach ($matches as $match) {
			$tokens[] = $this->classify($match);
		}

		$semantic = $this->semantic($tokens);

		$blocks = [];
		$block = ['type' => 'Paragraph', 'text' => ''];
		foreach ($tokens as $i => $token) {
			$match = $matches[$i];

			switch ($semantic[$i]) {
				case 1:
					$block['text'] = $block['text'] . ' ' . $match;
					break;

				case 2:
					$block['type'] = 'Heading';
					$block['level'] = strlen($match);
					break;

				case 3:
					$block = ['type' => 'List', 'text' => ''];
					break;

				case 4:
					$block['text'] = $block['text'] . ' ' . $match;
					$blocks[] = $block;
					$block = ['type' => 'Paragraph', 'text' => ''];
					break;
			}
		}

        foreach ($blocks as &$block) {
            $block['text'] = trim($block['text']);
        }

			return $blocks;
		}

		public function render(array $blocks): string {
			$html = '';

			foreach ($blocks as $i => $block) {
				$text = $block['text'];

				switch ($block['type']) {
					case 'Heading':
						$level = $block['level'];
                        $begin = '<h' . $level . '>';
                        $end = '</h' . $level . '>';
						$html .= $begin . $text . $end;
						break;

					case 'List':
						if (($blocks[$i - 1]['type']) !== 'List') {
							$html .= '<ul>';
						}

						$html .= '<li>' . $text . '</li>';

						if (($blocks[$i + 1]['type']) !== 'List') {
							$html .= '</ul>';
						}
						break;

					case 'Paragraph':
						$html .= '<p>' . $text . '</p>';
						break;
				}
			}

			return $html;
		}
	}
