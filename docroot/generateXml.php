<?php


require 'bootstrap.php';

use OJSXml\TempTable;

$submission_id = 0;
$tempTable = new TempTable($db, $TEMP_TABLE_NAME);
$tempTable->truncate();
$files = glob("./csv/abstracts/*");
$fileCount = 0;


/* 
  Loop for each csv file. If there is more then 1 csv file the output directory will be as 1.xml, 2.xml etc for each csv file.
*/
foreach ($files as $filepath) {
    $fileCount +=1;

    $count = 0;
    $data = csv_to_array($filepath,",");


    foreach($data as $row){
            $tempTable->insertAssocDataIntoTempTable($row);
    }

}

$issueCountQuery = "SELECT count( distinct issueTitle) as issueCount FROM " . $TEMP_TABLE_NAME  ;
$issueCount = $db->single($issueCountQuery );

echo "Number of issues:" . $issueCount['issueCount'] . "\n";

if($issueCount['issueCount']==0){
    echo "Error Number of issues found is 0\n";
    exit();
}


for($i = 0; $i < $issueCount['issueCount']; $i++ ){

    // Orders the article sequence
        $article_sequence = 0;

        $q_getIssues = "SELECT trim(issueTitle) issueTitle, issue, year, Volume, datePublished, cover_image_filename, cover_image_alt_text
                   FROM " . $TEMP_TABLE_NAME . " Group by issueTitle order by issueTitle limit " . ($i * $ISSUES_PER_FILE) ." ," . $ISSUES_PER_FILE;
        //echo $q_getIssues;
        $db->query( $q_getIssues);
        //echo $q_getIssues . "<br>";
        $issueRows = $db->resultset();
        if (count($issueRows) == 0){
            break;
        }
       // print_r($issueRows);

        $xmlWriter = new XMLWriter();
        $xmlWriter->openURI("output/" . $i . ".xml");
        $xmlWriter->startDocument();
        $xmlWriter->setIndent(true);

        $xmlWriter->startElement("issues");
        $xmlWriter->writeAttribute("xmlns","http://pkp.sfu.ca");
        $xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $xmlWriter->writeAttribute("xsi:schemaLocation","http://pkp.sfu.ca native.xsd");


        
        foreach ($issueRows as $r) {
            $xmlWriter->startElement("issue");
            include ('inc/issuerows.inc.php');

            /**
             * SECTION
             */
            $q_getSection ="SELECT sectionTitle,sectionAbbrev FROM  " . $TEMP_TABLE_NAME .
                " WHERE trim(issueTitle) = :issueTitle group by sectionTitle, sectionAbbrev ";
            $db->query($q_getSection);
           // echo $q_getSection . "<br>";
            echo "issueTitle = " . $r['issueTitle'] . "\n";
            $db->bind(":issueTitle", trim($r['issueTitle']));
            $sectionRows = $db->resultset();

            $xmlWriter->startElement("sections");
            $sectionAbbreviations = null;
            foreach ($sectionRows as $sectionRow) {
                include('inc/sectionrows.inc.php');
                $sectionAbbreviations[] = $sectionRow['sectionAbbrev'];
            }
            $xmlWriter->endElement(); // </sections>
            
            
            if( trim($r['cover_image_filename']) != ''){            
             include('inc/issuecoversrows.inc.php');
            }

            /**
             * ARTICLE
             */
            $xmlWriter->startElement("articles");
           
            foreach($sectionAbbreviations as $key => $sectionAbbrev){
                
                
                
                $sectionQuery = "SELECT issueTitle, sectionTitle,sectionAbbrev, supplementary_files, dependent_files , authors, affiliations, DOI, articleTitle, subTitle, 
                year, 	(datePublished) datePublished,	volume, 
                issue, startPage, COALESCE(endPage,'') as endPage,  articleAbstract as abstract, 	
                galleyLabel, authorEmail, fileName, keywords, language
                FROM " . $TEMP_TABLE_NAME . " 
                WHERE trim(issueTitle) = trim(:issueTitle) and trim(sectionAbbrev) = trim(:sectionAbbrev)"; 
                $db->query($sectionQuery);
                $db->bind(":issueTitle", $r['issueTitle']);
                $db->bind(":sectionAbbrev", $sectionAbbrev);



                $articleRows = $db->resultset();
                //echo $sectionQuery;
                //echo "<br> Article -- <br>";
                //echo pre($articleRows);

                
                foreach ($articleRows as $articleRow) {
                    $submission_id += 1;
                    $article_sequence += 1;
                    $articleRow['pages'] =  $articleRow['startPage'] . '-' . $articleRow['endPage'];
                    include('inc/articlerows.inc.php');
                }
            }
            $xmlWriter->endElement(); // </articles>


            $xmlWriter->endElement(); // </issue>

        }

        $xmlWriter->endElement();  // end </issues>

}
    $xmlWriter->endDocument();
    $xmlWriter->flush();


echo "\nXML file(s) have been generated, please check the output folder.\n";