<?php
function readJson($filename) {
    $path = __DIR__ . '/../data/' . $filename;
    if (!file_exists($path)) {
        return [];
    }
    $content = file_get_contents($path);
    return json_decode($content, true) ?: [];
}

function writeJson($filename, $data) {
    $path = __DIR__ . '/../data/' . $filename;
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}
