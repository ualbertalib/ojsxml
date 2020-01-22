<?php

$xmlWriter->startElement("section");
$sectionAbbrev = xmlFormat($sectionRow['sectionAbbrev']);
 $xmlWriter->writeAttribute('ref',$sectionAbbrev );

    $xmlWriter->startElement("abbrev");
        $xmlWriter->writeRaw($sectionAbbrev);
    $xmlWriter->endElement();
    $xmlWriter->startElement("policy"); $xmlWriter->endElement();
    $xmlWriter->startElement("title");
        $xmlWriter->writeRaw(xmlFormat($sectionRow['sectionTitle']));
    $xmlWriter->endElement();

$xmlWriter->endElement(); // </section>

