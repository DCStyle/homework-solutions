<?php
// Simple script to check PHP upload settings
// Save this as check_php_settings.php in your Laravel project's public directory
// Access it via http://localhost:8000/check_php_settings.php

echo "<h1>PHP Upload Settings</h1>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th><th>Converted</th></tr>";

// Check upload_max_filesize
$upload_max = ini_get('upload_max_filesize');
$upload_max_bytes = return_bytes($upload_max);
echo "<tr><td>upload_max_filesize</td><td>{$upload_max}</td><td>" . format_bytes($upload_max_bytes) . "</td></tr>";

// Check post_max_size
$post_max = ini_get('post_max_size');
$post_max_bytes = return_bytes($post_max);
echo "<tr><td>post_max_size</td><td>{$post_max}</td><td>" . format_bytes($post_max_bytes) . "</td></tr>";

// Check memory_limit
$memory_limit = ini_get('memory_limit');
$memory_limit_bytes = return_bytes($memory_limit);
echo "<tr><td>memory_limit</td><td>{$memory_limit}</td><td>" . format_bytes($memory_limit_bytes) . "</td></tr>";

// Check max_execution_time
$max_execution_time = ini_get('max_execution_time');
echo "<tr><td>max_execution_time</td><td>{$max_execution_time}</td><td>{$max_execution_time} seconds</td></tr>";

// Check max_input_time
$max_input_time = ini_get('max_input_time');
echo "<tr><td>max_input_time</td><td>{$max_input_time}</td><td>{$max_input_time} seconds</td></tr>";

// Check file_uploads
$file_uploads = ini_get('file_uploads') ? 'Enabled' : 'Disabled';
echo "<tr><td>file_uploads</td><td>{$file_uploads}</td><td>-</td></tr>";

// Check max_file_uploads
$max_file_uploads = ini_get('max_file_uploads');
echo "<tr><td>max_file_uploads</td><td>{$max_file_uploads}</td><td>-</td></tr>";

echo "</table>";

// Check Laravel validation limits
echo "<h2>Laravel Validation</h2>";
echo "<p>Your controller has a 'max:102400' validation rule which allows files up to 100MB.</p>";

// Recommendations
echo "<h2>Recommendations</h2>";

if ($upload_max_bytes < 104857600) { // 100MB
    echo "<p style='color:red'>⚠️ Your upload_max_filesize ({$upload_max}) is less than 100MB needed for your validation rule.</p>";
    echo "<p>Add this to your .htaccess or php.ini:</p>";
    echo "<pre>php_value upload_max_filesize 100M</pre>";
}

if ($post_max_bytes < $upload_max_bytes) {
    echo "<p style='color:red'>⚠️ Your post_max_size ({$post_max}) is less than upload_max_filesize ({$upload_max}).</p>";
    echo "<p>post_max_size should be larger than upload_max_filesize. Add this to your .htaccess or php.ini:</p>";
    echo "<pre>php_value post_max_size " . format_bytes($upload_max_bytes * 1.2, 0, "M") . "</pre>";
}

if ($max_execution_time < 300) {
    echo "<p style='color:orange'>⚠️ Your max_execution_time ({$max_execution_time} seconds) may be too short for large file uploads.</p>";
    echo "<p>Consider increasing it to at least 300 seconds in your .htaccess or php.ini:</p>";
    echo "<pre>php_value max_execution_time 300</pre>";
}

if ($max_input_time < 300) {
    echo "<p style='color:orange'>⚠️ Your max_input_time ({$max_input_time} seconds) may be too short for large file uploads.</p>";
    echo "<p>Consider increasing it to at least 300 seconds in your .htaccess or php.ini:</p>";
    echo "<pre>php_value max_input_time 300</pre>";
}

// Utility functions
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;

    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }

    return $val;
}

function format_bytes($bytes, $precision = 2, $unit = "") {
    if ($unit) {
        switch(strtoupper($unit)) {
            case 'K': return round($bytes / 1024, $precision) . 'K';
            case 'M': return round($bytes / (1024*1024), $precision) . 'M';
            case 'G': return round($bytes / (1024*1024*1024), $precision) . 'G';
        }
    }

    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}
