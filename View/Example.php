<?php

require_once __DIR__ . '/../Service/Reader.php';

$reader = new Reader();
$text = <<<'MARKDOWN'
# Header
![Image of a Violet Carpenter Bee](Bee.png)
Hi, this is a paragraph with some text.
- This is a list item;
- This is another list item;
- This is a third list item.
# Menu
- <a href="/">Home</a>
- <a href="/about">About</a>
- <a href="/posts">Posts</a>
- <a href="/contact">Contact</a>
- <a href="/compile">Compile</a>
- <a href="/show">Show</a>
- <a href="/example">Example</a>
# Author
Written by Violet Bee Project.
# Sidebar
Sidebar note with paragraph text.
# Footer
Footer text.
MARKDOWN;
$compiled = $reader->compile($text);
$html = $reader->render($compiled);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="/Style.css">
	<title>Markdown Example</title>
</head>
<body>
	<?= $html ?>
</body>
</html>
