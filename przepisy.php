<?php
$dir = __DIR__ . '/przepisy';
$files = glob($dir . '/*.pdf');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Przepisy</title>
</head>
<body>
<?php
  include 'header.php';
?>
    <h1>Przepisy:</h1>
    <ul>
        <?php foreach ($files as $file): ?>
            <?php $name = basename($file); ?>
            <li>
                <a href="view.php?file=<?= urlencode($name) ?>" target="_blank">
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
        <?php
		endforeach;
		echo '</br>'; 
	  	include 'footer.php';
	?>
    </ul>
</body>
</html>
