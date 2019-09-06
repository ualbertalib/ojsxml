<?php

/**
 * Convert the cover image to base64
 * */
$path = 'csv/abstracts/issue_cover_images/' . $r['cover_image_filename'];
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$coverImageBase64 = base64_encode($data);

$xmlWriter->startElement("issue_covers");
$xmlWriter->startElement("cover");
$xmlWriter->startElement("cover_image");
$xmlWriter->writeRaw($r['cover_image_filename']);
$xmlWriter->endElement();
$xmlWriter->startElement("cover_image_alt_text");
$xmlWriter->writeRaw($r['cover_image_alt_text']); 
$xmlWriter->endElement();

$xmlWriter->startElement("embed");
$xmlWriter->writeAttribute("encoding", "base64");

$xmlWriter->writeRaw($coverImageBase64); 

$xmlWriter->endElement();
$xmlWriter->endElement();
$xmlWriter->endElement();
