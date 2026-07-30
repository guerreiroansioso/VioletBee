<?php

require_once __DIR__ . '/../Service/Reader.php';

$reader = new Reader();
$text = "# Heading\n\nRender test of compiler.\n- Hi;\n- Second item;\n- Third item.\n\n[Example](https://example.com)\n\n## Heading 2\n\n### Heading 3\n\nThis is a note.";
$compiled = $reader->compile($text);
$html = $reader->render($compiled);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Violet Bee Project</title>
</head>
<body>

	<header>
		<h1>Violet Bee Project</h1>
		<nav>
			<ul>
				<li><a href="/">Home</a></li>
				<li><a href="/about">About</a></li>
				<li><a href="/posts">Posts</a></li>
				<li><a href="/contact">Contact</a></li>
				<li><a href="/compile">Compile</a></li>
				<li><a href="/show">Show</a></li>
			</ul>
		</nav>
	</header>

	<main>
		<section id="start">
			<?= $html ?>
		</section>
	</main>

	<footer>
		<p>&copy; 2026 Violet Bee Project. All rights reserved.</p>
	</footer>

</body>
</html>
