<?php


require 'bootstrap.php';

$files = glob("./csv/users/*");
$fileCount = 0;

/*
  Loop for each csv file. If there is more then 1 csv file the output directory will be as 1.xml, 2.xml etc for each csv file.
*/
foreach ($files as $filepath) {
    $fileCount +=1;

    $count = 0;
    $data = csv_to_array($filepath,",");

    $xmlWriter = new XMLWriter();
    $xmlWriter->openURI("output/users_{$fileCount}.xml");
    $xmlWriter->startDocument();
    $xmlWriter->setIndent(true);

    $xmlWriter->startElement("PKPUsers");
    $xmlWriter->writeAttribute("xmlns","http://pkp.sfu.ca");
    $xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
    $xmlWriter->writeAttribute("xsi:schemaLocation","http://pkp.sfu.ca pkp-users.xsd");

    $xmlWriter->startElement("user_groups");
        $xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $xmlWriter->writeAttribute("xsi:schemaLocation","http://pkp.sfu.ca pkp-users.xsd");


    /**
     * Journal Manager Group
     */
    $xmlWriter->startElement("user_group");
        $xmlWriter->startElement("role_id");
        $xmlWriter->writeRaw("16");
        $xmlWriter->endElement();
        $xmlWriter->startElement("context_id");
        $xmlWriter->writeRaw("1");
        $xmlWriter->endElement();
        $xmlWriter->startElement("is_default");
        $xmlWriter->writeRaw("true");
        $xmlWriter->endElement();
        $xmlWriter->startElement("show_title");
        $xmlWriter->writeRaw("false");
        $xmlWriter->endElement();
        $xmlWriter->startElement("permit_self_registration");
        $xmlWriter->writeRaw("false");
        $xmlWriter->endElement();
        $xmlWriter->startElement("name");
            $xmlWriter->writeAttribute("locale","en_US");
            $xmlWriter->writeRaw("Journal Manager");
        $xmlWriter->endElement();
        $xmlWriter->startElement("abbrev");
        $xmlWriter->writeAttribute("locale","en_US");
        $xmlWriter->writeRaw("JM");
        $xmlWriter->endElement();
        $xmlWriter->startElement("stage_assignments");
        $xmlWriter->endElement();
    $xmlWriter->endElement();
    /**
     * Section Editor
     */
    $xmlWriter->startElement("user_group");
        $xmlWriter->startElement("role_id");
        $xmlWriter->writeRaw("17");
        $xmlWriter->endElement();
        $xmlWriter->startElement("context_id");
        $xmlWriter->writeRaw("1");
        $xmlWriter->endElement();
        $xmlWriter->startElement("is_default");
        $xmlWriter->writeRaw("true");
        $xmlWriter->endElement();
        $xmlWriter->startElement("show_title");
        $xmlWriter->writeRaw("false");
        $xmlWriter->endElement();
        $xmlWriter->startElement("permit_self_registration");
        $xmlWriter->writeRaw("false");
        $xmlWriter->endElement();
        $xmlWriter->startElement("name");
        $xmlWriter->writeAttribute("locale","en_US");
        $xmlWriter->writeRaw("Section Editor");
        $xmlWriter->endElement();
        $xmlWriter->startElement("abbrev");
            $xmlWriter->writeAttribute('locale',"en_US");
            $xmlWriter->writeRaw("SecE");
        $xmlWriter->endElement();
        $xmlWriter->startElement("stage_assignments");
        $xmlWriter->writeRaw("1:3:4:5");
        $xmlWriter->endElement();
    $xmlWriter->endElement();
    /**
     * Reviewer
     */
    $xmlWriter->startElement("user_group");
        $xmlWriter->startElement("role_id");
        $xmlWriter->writeRaw("4096");
        $xmlWriter->endElement();
        $xmlWriter->startElement("context_id");
        $xmlWriter->writeRaw("1");
        $xmlWriter->endElement();
        $xmlWriter->startElement("is_default");
        $xmlWriter->writeRaw("true");
        $xmlWriter->endElement();
        $xmlWriter->startElement("show_title");
        $xmlWriter->writeRaw("false");
        $xmlWriter->endElement();
        $xmlWriter->startElement("permit_self_registration");
        $xmlWriter->writeRaw("true");
        $xmlWriter->endElement();
        $xmlWriter->startElement("name");
        $xmlWriter->writeAttribute('locale',"en_US");
        $xmlWriter->writeRaw("Reviewer");
        $xmlWriter->endElement();
        $xmlWriter->startElement("abbrev");
        $xmlWriter->writeAttribute('locale',"en_US");
        $xmlWriter->writeRaw("R");
        $xmlWriter->endElement();
        $xmlWriter->startElement("stage_assignments");
        $xmlWriter->writeRaw("3");
        $xmlWriter->endElement();
    $xmlWriter->endElement();
    /**
     * Reader Group
     */
    $xmlWriter->startElement("user_group");
            $xmlWriter->startElement("role_id");
            $xmlWriter->writeRaw("1048576");
            $xmlWriter->endElement();
            $xmlWriter->startElement("context_id");
            $xmlWriter->writeRaw("1");
            $xmlWriter->endElement();
            $xmlWriter->startElement("is_default");
            $xmlWriter->writeRaw("true");
            $xmlWriter->endElement();
            $xmlWriter->startElement("show_title");
            $xmlWriter->writeRaw("false");
            $xmlWriter->endElement();
            $xmlWriter->startElement("permit_self_registration");
            $xmlWriter->writeRaw("true");
            $xmlWriter->endElement();
            $xmlWriter->startElement("name");
            $xmlWriter->writeAttribute("locale","en_US");
            $xmlWriter->writeRaw("Reader");
            $xmlWriter->endElement();
            $xmlWriter->startElement("abbrev");
            $xmlWriter->writeAttribute("locale","en_US");
            $xmlWriter->writeRaw("Read");
            $xmlWriter->endElement();
            $xmlWriter->startElement("stage_assignments");
            $xmlWriter->endElement();
    $xmlWriter->endElement();


    $xmlWriter->endElement();


    /*** process rows here ***/
    $xmlWriter->startElement("users");
    foreach($data as $row) {
        $xmlWriter->startElement("user");
        $xmlWriter->startElement("firstname");
        $xmlWriter->writeRaw($row['firstname']);
        $xmlWriter->endElement();
        $xmlWriter->startElement("lastname");
        $xmlWriter->writeRaw($row['lastname']);
        $xmlWriter->endElement();
        $xmlWriter->startElement("affiliation");
        $xmlWriter->writeRaw($row['affiliation']);
        $xmlWriter->endElement();
        $xmlWriter->startElement("country");
        $xmlWriter->writeRaw($row['country']);
        $xmlWriter->endElement();
        $xmlWriter->startElement("email");
        $xmlWriter->writeRaw(htmlspecialchars($row['email']));
        $xmlWriter->endElement();
        $xmlWriter->startElement("username");
        $xmlWriter->writeRaw($row['username']);
        $xmlWriter->endElement();
        $xmlWriter->startElement("password");
        $xmlWriter->writeAttribute("must_change", true);
        $xmlWriter->startElement("value");
        $xmlWriter->writeRaw($row['tempPassword']);
        $xmlWriter->endElement();
        $xmlWriter->endElement();

        $xmlWriter->startElement("date_registered");
        $xmlWriter->writeRaw(date("Y-m-d H:i:s"));
        $xmlWriter->endElement();

        $xmlWriter->startElement("date_last_login");
        $xmlWriter->writeRaw(date("Y-m-d H:i:s"));
        $xmlWriter->endElement();
        $xmlWriter->startElement("inline_help");
        $xmlWriter->writeRaw("true");
        $xmlWriter->endElement();

        for($i=1; $i<=6; $i++){
            if( isset($row['role' . $i]) && $row['role' . $i] != ''){
                $xmlWriter->startElement("user_group_ref");
                $xmlWriter->writeRaw(userGroupRef($row['role' . $i]));
                $xmlWriter->endElement();
            }
        }

        $xmlWriter->endElement();

    }
    $xmlWriter->endElement();

    $xmlWriter->endElement();
    $xmlWriter->endDocument();
    $xmlWriter->flush();
}

echo "XML file(s) have been generated, please check the output folder.\n";