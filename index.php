<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Image Gallery</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
</head>
<body>
    <div class="gallery"></div>

    <script>
        const imageFolder = 'images/';
        const images = [
            <?php
                $dir = 'images/';
                $files = scandir($dir);
                $pngFiles = array_filter($files, function($file) {
                    return pathinfo($file, PATHINFO_EXTENSION) === 'png';
                });
                echo '"' . implode('","', $pngFiles) . '"';
            ?>
        ];

        const gallery = document.querySelector('.gallery');

        images.forEach(image => {
            const anchor = document.createElement('a');
            anchor.href = imageFolder + image;
            anchor.target = '_blank';

            const img = document.createElement('img');
            img.src = `thumbnail.php?src=${imageFolder}${image}&w=150&h=150`;
            img.alt = image;

            anchor.appendChild(img);
            gallery.appendChild(anchor);
        });
    </script>
</body>
</html>
