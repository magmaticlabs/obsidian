<?php

$file_data = str_replace("\r", "\n", file_get_contents('php://stdin'));

$headers = [
    'Name'        => '(Plugin|Theme) Name',
    'PackageURI'  => '(Plugin|Theme) URI',
    'Version'     => 'Version',
    'Description' => 'Description',
    'Author'      => 'Author',
    'AuthorURI'   => 'Author URI',
    'TextDomain'  => 'Text Domain',
    'DomainPath'  => 'Domain Path',
    'Network'     => 'Network',
];

foreach ($headers as $header => $regex) {
    $pattern = '/^[ \t\/*#@]*' . $regex . ':(.*)$/mi';
    if (preg_match($pattern, $file_data, $match) && !empty($match[1])) {
        $headers[$header] = trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $match[count($match) - 1]));
    } else {
        $headers[$header] = '';
    }
}

echo json_encode($headers);
