<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https'
    : 'http';

$host = $_SERVER['HTTP_HOST'];
$project_folder = 'coba';

define('BASE_URL', $protocol . '://' . $host . '/' . $project_folder);

function base_url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}
