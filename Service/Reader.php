<?php

final class Reader {
	private string $pattern = '/\n|[][]|[()]|[^\s[\]()]+/u';

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

		$matrix = [
			/* token:       0  1  2  3  4  5  6   7  8  9 10 11 12 */
			/*  0 Skip */ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],
			/*  1 Text */ [ 1, 1, 1, 1, 4, 1, 1,  1, 1, 1, 1, 1, 1],
			/*  2 Head */ [ 1, 2, 0, 0, 0, 0, 0,  0, 0, 0, 0, 0, 0],
			/*  3 List */ [ 1, 0, 0, 0, 4, 0, 0,  0, 0, 0, 0, 0, 0],
			/*  4 End  */ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],
			/*  5 Link */ [ 5, 0, 0, 0, 0, 0, 0, 32, 0, 0, 0, 0, 0],
			/*  6 ELink*/ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],
			/*  7 Image*/ [ 0, 0, 0, 0, 0, 0, 0, 34, 0, 0, 0, 0, 0],
			/*  8 EImg */ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],
			/*  9 `    */ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],
			/* 10 ~    */ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],
			/* 11 |    */ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],
			/* 12 \    */ [ 1, 2, 1, 3, 0, 1, 1,  5, 7, 1, 1, 1, 1],

			/* 32 LSym */ 32 => [ 0, 0, 0, 0, 0, 0, 0, 33, 0, 0, 0, 0, 0],
			/* 33 LUrl */ 33 => [33, 0, 0, 0, 0, 0, 0,  6, 0, 0, 0, 0, 0],
			/* 34 ILbl */ 34 => [36, 0, 0, 0, 0, 0, 0, 35, 0, 0, 0, 0, 0],
			/* 35 ISym */ 35 => [ 0, 0, 0, 0, 0, 0, 0, 37, 0, 0, 0, 0, 0],
			/* 36 IAltB*/ 36 => [38, 0, 0, 0, 0, 0, 0, 35, 0, 0, 0, 0, 0],
			/* 37 IUrl */ 37 => [37, 0, 0, 0, 0, 0, 0,  8, 0, 0, 0, 0, 0],
			/* 38 IAlt */ 38 => [38, 0, 0, 0, 0, 0, 0, 35, 0, 0, 0, 0, 0],
		];

		$state = 0;
		$actions = [
			32 => 5,
			33 => 5,
			34 => 7,
			35 => 7,
			36 => 7,
			37 => 7,
			38 => 1,
		];
		foreach ($tokens as $token) {
			$state = $matrix[$state][$token];
			$semantic[] = $actions[$state] ?? $state;
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

				case 5:
					$block['type'] = 'Link';
					$block['text'] = $block['text'] . $match;
					break;

				case 6:
					$block['type'] = 'Link';
					$block['text'] = $block['text'] . $match;
					$blocks[] = $block;
					$block = ['type' => 'Paragraph', 'text' => ''];
					break;

				case 7:
					$block['type'] = 'Image';
					$block['text'] = $block['text'] . $match;
					break;

				case 8:
					$block['type'] = 'Image';
					$block['text'] = $block['text'] . $match;
					$blocks[] = $block;
					$block = ['type' => 'Paragraph', 'text' => ''];
					break;
			}
		}

		if ($block['text'] !== '') {
			$blocks[] = $block;
		}

        foreach ($blocks as &$block) {
            $block['text'] = trim($block['text']);

			if ($block['type'] === 'Heading') {
				$block['special'] = $this->special($block['text']);
			}
        }

		return $blocks;
	}

	public function render(array $blocks): string {
		$html = '';
		$special = 'None';

		foreach ($blocks as $i => $block) {
			$text = $block['text'];

			switch ($block['type']) {
				case 'Heading':
					if (($block['special'] ?? 'None') !== 'None') {
						if ($special !== 'None') {
							$html .= $this->select($special, false);
						}

						$special = $block['special'];
						$html .= $this->select($special, true);
						break;
					}

					$level = $block['level'];
					$begin = '<h' . $level . '>';
					$end = '</h' . $level . '>';
					$html .= $begin . $text . $end;
					break;

				case 'List':
					if (($blocks[$i - 1]['type'] ?? null) !== 'List') {
						$html .= '<ul>';
					}

					$html .= '<li>' . $text . '</li>';

					if (($blocks[$i + 1]['type'] ?? null) !== 'List') {
						$html .= '</ul>';
					}
					break;

				case 'Paragraph':
					$html .= '<p>' . $text . '</p>';
					break;

				case 'Link':
					$labelEnd = strpos($text, ']');
					$urlStart = strpos($text, '(');
					$label = substr($text, 1, $labelEnd - 1);
					$url = substr($text, $urlStart + 1, -1);
					$html .= '<a href="' . $url . '">' . $label . '</a>';
					break;

				case 'Image':
					$labelEnd = strpos($text, ']');
					$urlStart = strpos($text, '(');
					$label = substr($text, 2, $labelEnd - 2);
					$url = substr($text, $urlStart + 1, -1);
					$html .= '<img src="' . $url . '" alt="' . $label . '">';
					break;
			}
		}

		if ($special !== 'None') {
			$html .= $this->select($special, false);
		}

		return $html;
	}

	private function select(string $special, bool $start): string {
		switch ($special) {
			case 'Header':
				return $this->header($start);
			case 'Menu':
				return $this->menu($start);
			case 'Author':
				return $this->author($start);
			case 'Sidebar':
				return $this->sidebar($start);
			case 'Footer':
				return $this->footer($start);
			default:
				return '';
		}
	}

	private function special(string $text): string {
		switch ($text) {
			case 'Header':
				return 'Header';
			case 'Menu':
				return 'Menu';
			case 'Author':
				return 'Author';
			case 'Sidebar':
				return 'Sidebar';
			case 'Footer':
				return 'Footer';
			default:
				return 'None';
		}
	}

	private function header(bool $start): string {
		if ($start) { return '<header>'; }
		else { return '</header>'; }
	}

	private function menu(bool $start): string {
		if ($start) { return '<menu>'; }
		else { return '</menu>'; }
	}

	private function author(bool $start): string {
		if ($start) { return '<author>'; }
		else { return '</author>'; }
	}

	private function sidebar(bool $start): string {
		if ($start) { return '<sidebar>'; }
		else { return '</sidebar>'; }
	}

	private function footer(bool $start): string {
		if ($start) { return '<footer>'; }
		else { return '</footer>'; }
	}
}       
