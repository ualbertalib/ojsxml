<?php
use OJSXml\Authors;

$xmlWriter->startElement("article");  /** @var $xmlWriter XMLWriter */


    $xmlWriter->writeAttribute("section_ref",$sectionAbbrev);
    $xmlWriter->writeAttribute("stage","production");
    $xmlWriter->writeAttribute("date_published", date("Y-m-d", strtotime(trim($articleRow['datePublished']))));
    $xmlWriter->writeAttribute("seq", $article_sequence);
    
    
    $doi = trim($articleRow['DOI']);
    if($doi!=""){
        $xmlWriter->startElement("id");
            $xmlWriter->writeAttribute("type","doi");
            $xmlWriter->writeAttribute("advice","update");
            $xmlWriter->writeRaw(xmlFormat($doi));
        $xmlWriter->endElement();
    }
    $xmlWriter->startElement("title");
        $xmlWriter->writeRaw(xmlFormat(trim($articleRow['articleTitle'])));
    $xmlWriter->endElement();
    
    if(trim($articleRow['subTitle'])!=""){ 
    $xmlWriter->startElement("subtitle");
        $xmlWriter->writeRaw(xmlFormat(trim($articleRow['subTitle'])));
    $xmlWriter->endElement();
    }

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
            $xmlWriter->writeRaw(xmlFormat(trim($keyword)));
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

        $fileName = $articleRow['fileName'];
        $baseFileName = basename($articleRow['fileName']); // removes the path if it exists 
        
     
        $xmlWriter->startElement("revision");
            $xmlWriter->writeAttribute("genre", "Article Text");
            $xmlWriter->writeAttribute("number", 1); 
            $xmlWriter->writeAttribute("filetype", getFiletype($articleRow['galleyLabel']));
            $xmlWriter->writeAttribute("filename", $baseFileName);

            $xmlWriter->startElement("name");
            $xmlWriter->writeRaw($baseFileName);
            $xmlWriter->endElement();
            $xmlWriter->startElement("href");
            $xmlWriter->writeAttribute("src", $PDF_URL . $fileName);
            $xmlWriter->writeAttribute("mime_type",getFiletype($articleRow['galleyLabel']));
            $xmlWriter->endElement();
        $xmlWriter->endElement();
    $xmlWriter->endElement();
    
    
    $suppRows = explode(",",$articleRow['supplementary_files']);

    
    $n = array();
    foreach($suppRows as $key=>$r){
      
        $n[$key] = $key;
        $r[$key] = trim($r);
        $parts = parse_url($r);
        $filename[$key] = basename($parts["path"]);
        
        $suppId[$key] = $submission_id . "_" . $n[$key];
        $xmlWriter->startElement("supplementary_file");
        $xmlWriter->writeAttribute("stage", "proof");
        $xmlWriter->writeAttribute("id", $suppId[$key]);
        $xmlWriter->startElement("revision");
        $xmlWriter->writeAttribute("number", 1 );
        $xmlWriter->writeAttribute("filetype", get_mime_type($filename[$key]) );
        $xmlWriter->writeAttribute("genre", "Other");
        $xmlWriter->writeAttribute("viewable", "false");
        $xmlWriter->writeAttribute("filename", $filename[$key]);
        $xmlWriter->startElement("name");
         $xmlWriter->writeRaw(basename(trim($r)));
        $xmlWriter->endElement();
        $xmlWriter->startElement("href");
         $xmlWriter->writeAttribute("src",trim($r));
         $xmlWriter->writeAttribute("mime_type", get_mime_type($filename[$key]));
        $xmlWriter->endElement();
        $xmlWriter->endElement();
        $xmlWriter->endElement();
       
    }
    
     /**
        * Article Galley for supplementary Files
        */
      foreach($suppRows as $key=>$r){
        $xmlWriter->startElement("article_galley");
        //$xmlWriter->startElement("id");
         //   $xmlWriter->writeRaw($submission_id);
       // $xmlWriter->endElement();
        $xmlWriter->startElement("name");
            $xmlWriter->writeRaw($filename[$key]);
        $xmlWriter->endElement();
        $xmlWriter->startElement("seq");
            $xmlWriter->writeRaw("1");
        $xmlWriter->endElement();
        $xmlWriter->startElement("submission_file_ref");
            $xmlWriter->writeAttribute("id",$suppId[$key]);
            $xmlWriter->writeAttribute("revision",1);
        $xmlWriter->endElement();
        $xmlWriter->endElement();
      }
    
/**
 * Article Galley for the main file
 */
$xmlWriter->startElement("article_galley");
    // $xmlWriter->startElement("id"); $xmlWriter->writeRaw($submission_id);    $xmlWriter->endElement();
    
    $xmlWriter->startElement("name");
        $xmlWriter->writeRaw($articleRow['galleyLabel']);
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

