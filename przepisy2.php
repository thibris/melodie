<?php
require_once "Parsedown.php";
$Parsedown = new Parsedown();

$baseDir = __DIR__ . "/przepisy2";
$dirs = array_filter(glob($baseDir . "/*"), 'is_dir');

// Sortowanie
$sort = $_GET['sort'] ?? 'az';

usort($dirs, function($a, $b) use ($sort) {
    switch ($sort) {
        case 'za':
            return strcasecmp(basename($b), basename($a));
        case 'new':
            return filemtime($b) - filemtime($a);
        case 'old':
            return filemtime($a) - filemtime($b);
        case 'az':
        default:
            return strcasecmp(basename($a), basename($b));
    }
});

$selected = isset($_GET['katalog']) ? basename($_GET['katalog']) : null;

function loadMarkdown($path, $Parsedown) {
    if (file_exists($path)) {
        return $Parsedown->text(file_get_contents($path));
    }
    return "<p><i>Brak pliku: " . htmlspecialchars(basename($path)) . "</i></p>";
}

function loadImages($folder) {
    if (!is_dir($folder)) {
        return [];
    }

    $files = scandir($folder);
    $out = [];
    $exts = ['jpg','jpeg','png','gif','webp'];

    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $folder . '/' . $f;
        if (!is_file($path)) continue;

        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, $exts, true)) {
            $out[] = $path;
        }
    }

    return $out;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Przepisy 2</title>

<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    margin: 0;
    background: #f3f3f3;
    color: #222;
    height: 100vh;
    overflow: hidden; /* klucz: wyłączamy scroll całej strony */
}

/* GÓRNE MENU */
#menu {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: #ffffffdd;
    backdrop-filter: blur(6px);
    padding: 8px 15px; /* mniejszy odstęp */
    border-bottom: 1px solid #ddd;
    display: flex;
    gap: 20px;
    align-items: center;
    z-index: 1000;
    height: 48px;
}

/* GŁÓWNY PODZIAŁ STRONY */
#container {
    position: absolute;
    top: 48px; /* dokładnie pod menu */
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    overflow: hidden;
}

/* LEWA KOLUMNA – LISTA */
#lista {
    width: 25%; /* max 1/4 strony */
    min-width: 220px;
    max-width: 400px;
    background: #fff;
    border-right: 1px solid #ddd;
    padding: 15px;
    overflow-y: auto; /* osobne przewijanie */
    box-shadow: 2px 0 4px #0001;
}

/* PRAWA KOLUMNA – PRZEPIS */
#przepis {
    flex: 1;
    padding: 25px;
    overflow-y: auto; /* osobne przewijanie */
    background: #fff;
}

/* GALERIA */
.gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 25px;
}

.gallery img {
    max-width: 100%;
    border-radius: 6px;
    box-shadow: 0 1px 4px #0002;
    transition: transform .2s ease, box-shadow .2s ease;
}

.gallery img:hover {
    transform: scale(1.03);
    box-shadow: 0 3px 10px #0003;
}

/* MOBILE */
@media (max-width: 800px) {
    #container {
        flex-direction: column;
    }
    #lista {
        width: 100%;
        max-width: none;
        border-right: none;
        border-bottom: 1px solid #ddd;
        height: 30%;
    }
    #przepis {
        height: 70%;
    }
}
</style>

</head>
<body>

<div id="menu">
    <form method="GET" style="display:flex; gap:10px;">
        <label>Przepis:</label>
        <select name="katalog" onchange="this.form.submit()">
            <option value="">-- wybierz --</option>
            <?php foreach ($dirs as $dir): 
                $name = basename($dir); ?>
                <option value="<?= htmlspecialchars($name) ?>" 
                    <?= $selected === $name ? "selected" : "" ?>>
                    <?= htmlspecialchars($name) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Sortuj:</label>
        <select name="sort" onchange="this.form.submit()">
            <option value="az"  <?= $sort=='az'?'selected':'' ?>>A–Z</option>
            <option value="za"  <?= $sort=='za'?'selected':'' ?>>Z–A</option>
            <option value="new" <?= $sort=='new'?'selected':'' ?>>Najnowsze</option>
            <option value="old" <?= $sort=='old'?'selected':'' ?>>Najstarsze</option>
        </select>
    </form>
</div>

<?php if ($selected): 
    $folder = $baseDir . "/" . $selected;
    $lista = loadMarkdown($folder . "/lista.md", $Parsedown);
    $przepis = loadMarkdown($folder . "/przepis.md", $Parsedown);
    $images = loadImages($folder);
?>
<div id="container">
    <div id="lista">
        <?= $lista ?>
    </div>

    <div id="przepis">
        <?= $przepis ?>

        <?php if ($images): ?>
        <h2>Zdjęcia</h2>
        <div class="gallery">
            <?php foreach ($images as $img): ?>
                <img src="przepisy2/<?= htmlspecialchars($selected) ?>/<?= htmlspecialchars(basename($img)) ?>">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

</body>
</html>

