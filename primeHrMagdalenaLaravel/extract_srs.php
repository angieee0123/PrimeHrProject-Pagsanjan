<?php
$zip = new ZipArchive();
$result = $zip->open(__DIR__ . '/srs.docx');
echo "Open result: " . var_export($result, true) . "\n";
echo "Num files: " . $zip->numFiles . "\n";
for ($i = 0; $i < $zip->numFiles; $i++) {
    echo $zip->getNameIndex($i) . "\n";
}
$xml = $zip->getFromName('word/document.xml');
echo "XML length: " . strlen($xml) . "\n";
$zip->close();

// Parse paragraphs
$dom = new DOMDocument();
@$dom->loadXML($xml);
$xpath = new DOMXPath($dom);
$xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

$paragraphs = $xpath->query('//w:p');
$output = '';
foreach ($paragraphs as $para) {
    $runs = $xpath->query('.//w:t', $para);
    $line = '';
    foreach ($runs as $run) {
        $line .= $run->nodeValue;
    }
    $output .= $line . "\n";
}

file_put_contents(__DIR__ . '/srs_extracted.txt', $output);
echo "Done. Lines: " . substr_count($output, "\n") . "\n";
