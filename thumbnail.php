<?php
function getMimeType($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
    ];
    return $mimeTypes[$ext] ?? 'application/octet-stream';
}

function createThumbnail($imagePath, $maxWidth, $maxHeight, $cachePath) {
    $info = getimagesize($imagePath);
    if ($info === false) {
        throw new Exception('Unable to get image size for: ' . $imagePath);
    }

    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($imagePath);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($imagePath);
            break;
        default:
            throw new Exception('Unsupported image type: ' . $mime);
    }

    $width = $info[0];
    $height = $info[1];
    $scale = min($maxWidth / $width, $maxHeight / $height);
    if ($scale >= 1) {
        $newWidth = $width;
        $newHeight = $height;
    } else {
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);
    }

    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }

    imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($thumbnail, $cachePath, 75);
            break;
        case 'image/png':
            imagepng($thumbnail, $cachePath, 8);
            break;
        case 'image/gif':
            imagegif($thumbnail, $cachePath);
            break;
    }

    imagedestroy($thumbnail);
    imagedestroy($image);
}

$imagePath = $_GET['src'];
$maxWidth = isset($_GET['w']) ? (int)$_GET['w'] : 360;
$maxHeight = isset($_GET['h']) ? (int)$_GET['h'] : 360;

$cacheDir = 'cache/';
$cacheFilename = md5($imagePath . $maxWidth . $maxHeight) . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);
$cachePath = $cacheDir . $cacheFilename;

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

if (file_exists($cachePath)) {
    header('Content-Type: ' . getMimeType($cachePath));
    readfile($cachePath);
} else {
    if (file_exists($imagePath)) {
        try {
            createThumbnail($imagePath, $maxWidth, $maxHeight, $cachePath);
            header('Content-Type: ' . getMimeType($cachePath));
            readfile($cachePath);
        } catch (Exception $e) {
            header("HTTP/1.0 500 Internal Server Error");
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        echo 'Error: Image not found.';
    }
}
?>
