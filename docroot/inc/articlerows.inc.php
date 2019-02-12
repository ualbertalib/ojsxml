<?php
use OJSXml\Authors;

$xmlWriter->startElement("article");  /** @var $xmlWriter XMLWriter */
    $xmlWriter->writeAttribute("section_ref",$sectionAbbrev);
    $xmlWriter->writeAttribute("stage","production");
    $xmlWriter->writeAttribute("date_published", date("Y-m-d", strtotime(trim($articleRow['datePublished']))));
    $xmlWriter->writeAttribute("seq", $article_sequence);

    $xmlWriter->startElement("title");
        $xmlWriter->writeRaw(xmlFormat(trim($articleRow['articleTitle'])));
    $xmlWriter->endElement();

    $xmlWriter->startElement("abstract");
        $xmlWriter->writeRaw(xmlFormat(trim($articleRow['abstract'])));
    $xmlWriter->endElement();

    // $xmlWriter->startElement("copyrightYear");        $xmlWriter->writeRaw(xmlFormat(trim($articleRow['year'])));    $xmlWriter->endElement();

    /**
     * Subject/Keywords are seperated by a ';' in the spreadsheet therefore parse it
     */
    if(semiColonFix($articleRow['keywords'] != "")){
        $keywordAry = parseSemiColon($articleRow['keywords']);
        $xmlWriter->startElement('subjects');
        foreach($keywordAry as $keyword){
            $xmlWriter->startElement('subject');
        //    $xmlWriter->writeAttribute('locale', 'en_US');
            $xmlWriter->writeRaw(trim($keyword));
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement();
    }




    $fields = array("authors");
    $authorsXml = new Authors($articleRow['authors'], $xmlWriter, $articleRow['authorEmail'], $articleRow['affiliations']);
    $authorsXml->getAuthorXML();

    $xmlWriter->startElement("submission_file");
        $xmlWriter->writeAttribute("id", $submission_id);
        $xmlWriter->writeAttribute("stage","proof");

        // if the desiredExtension is missing append it to the filename
        $fileName = fileExtensionAppender($articleRow['fileName'],'pdf');
        $xmlWriter->startElement("revision");
            $xmlWriter->writeAttribute("genre", "Article Text");
            $xmlWriter->writeAttribute("number", 1);
            $xmlWriter->writeAttribute("filetype", getFiletype($articleRow['galleyLabel']));
            $xmlWriter->writeAttribute("filename", $fileName);

            $xmlWriter->startElement("name");
            $xmlWriter->writeRaw($fileName);
            $xmlWriter->endElement();
            $xmlWriter->startElement("href");
            $xmlWriter->writeAttribute("src", $PDF_URL . $fileName);
            $xmlWriter->writeAttribute("mime_type",getFiletype($articleRow['galleyLabel']));
            $xmlWriter->endElement();
        $xmlWriter->endElement();
    $xmlWriter->endElement();

$xmlWriter->startElement("article_galley");
    $xmlWriter->startElement("id");
        $xmlWriter->writeRaw($submission_id);
    $xmlWriter->endElement();
    $xmlWriter->startElement("name");
        $xmlWriter->writeRaw("PDF");
    $xmlWriter->endElement();
    $xmlWriter->startElement("seq");
        $xmlWriter->writeRaw("1");
    $xmlWriter->endElement();
    $xmlWriter->startElement("submission_file_ref");
        $xmlWriter->writeAttribute("id",$submission_id);
        $xmlWriter->writeAttribute("revision",1);
    $xmlWriter->endElement();

$xmlWriter->endElement();
    /*
    $xmlWriter->startElement("article_galley");
        $xmlWriter->writeAttribute('approved', 'true');
        $xmlWriter->startElement("name");
            $xmlWriter->writeAttribute('locale', 'en_US');
            $xmlWriter->writeRaw($articleRow['galleyLabel']);
        $xmlWriter->endElement();
        $xmlWriter->startElement("seq");
          $xmlWriter->writeRaw("0");
        $xmlWriter->endElement();
        $xmlWriter->startElement("remote");
            $xmlWriter->writeAttribute('src', $PDF_URL . $articleRow['fileName']);
        $xmlWriter->endElement();
    $xmlWriter->endElement();
    */

    // </article_galley>

    $xmlWriter->startElement("pages");
        $xmlWriter->writeRaw(trim($articleRow['pages'], '-'));
    $xmlWriter->endElement();

    // $xmlWriter->startElement("date_published");     $xmlWriter->writeRaw(xmlFormat(trim($articleRow['datePublished'])));    $xmlWriter->endElement();
$xmlWriter->endElement();

