<?php
/**
 * Melayani file statis (audio upload) dengan aman
 */

namespace App\Controllers;

use Core\App;
use Core\Controller;

class FileController extends Controller
{
    public function streamAudio(string $filename): void
    {
        // Cegah path traversal
        $filename = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-.]+$/', $filename)) {
            http_response_code(400);
            exit('Nama file tidak valid');
        }

        $filePath = App::storagePath('audio') . '/' . $filename;

        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('File audio tidak ditemukan');
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'aac' => 'audio/aac',
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Accept-Ranges: bytes');

        // Dukungan range request (utk seek audio)
        if (isset($_SERVER['HTTP_RANGE'])) {
            $size = filesize($filePath);
            $range = $_SERVER['HTTP_RANGE'];
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $m)) {
                $start = (int) $m[1];
                $end = $m[2] !== '' ? (int) $m[2] : $size - 1;
                $end = min($end, $size - 1);
                if ($start > $end || $start >= $size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes */{$size}");
                    exit;
                }
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes {$start}-{$end}/{$size}");
                header("Content-Length: " . ($end - $start + 1));
                $fp = fopen($filePath, 'rb');
                fseek($fp, $start);
                $remaining = $end - $start + 1;
                while ($remaining > 0 && !feof($fp)) {
                    $chunk = fread($fp, min(8192, $remaining));
                    $remaining -= strlen($chunk);
                    echo $chunk;
                    flush();
                }
                fclose($fp);
                exit;
            }
        }

        readfile($filePath);
        exit;
    }
}