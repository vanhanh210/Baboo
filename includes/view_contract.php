<?php
session_start();

function downloadImage($file_path) {
    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        echo "File not found.";
    }
}

function viewImage($file_path) {
    if (file_exists($file_path)) {
        header('Content-Type: image/jpeg'); 
        readfile($file_path);
        exit;
    } else {
        echo "File not found.";
    }
}

if (isset($_GET['action']) && isset($_GET['file'])) {
    $file_path = urldecode($_GET['file']);
    
    if ($_GET['action'] === 'download') {
        downloadImage($file_path);
    } elseif ($_GET['action'] === 'view') {
        viewImage($file_path);
    } else {
        echo "Invalid action.";
    }
} else {
    echo "No file specified.";
}
?>