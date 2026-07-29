<?php

require_once __DIR__ . '/../Service/Reader.php';

$reader = new Reader();
$text = "# Heading\nRender test with Reader compile.\n- Hi.\n## Heading 2\n- Hello.\n- World.\n";
$compiled = $reader->compile($text);

$input = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
$output = htmlspecialchars(
	json_encode($compiled, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES,
	'UTF-8'
);

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
			</ul>
		</nav>
	</header>

	<main>
		<section id="start">
			<h2>Compile</h2>
			<p>Render test with Reader compile.</p>

			<h3>Input</h3>
			<pre><?= $input ?></pre>

			<h3>Output</h3>
			<pre><?= $output ?></pre>
		</section>
	</main>

	<footer>
		<p>&copy; 2026 Violet Bee Project. All rights reserved.</p>
	</footer>

</body>
</html>
