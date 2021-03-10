<?php

namespace OJSXml;

require 'bootstrap.php';

define("USER_FILES_PATTERN", "./csv/users/*");

generateIssuesXml();
//generateUsersXml();

function generateIssuesXml() {
    $dbManager = new DBManager();
    $dbManager->importIssueCsvData("./csv/abstracts/*");

    $issueCount = $dbManager->getIssueCount();

    for ($i = 0; $i < $issueCount; $i++) {
        $xmlBuilder = new IssuesXmlBuilder("output/issues_{$i}.xml", $dbManager);
        $xmlBuilder->setIteration($i);
        $xmlBuilder->buildXml();
    }
}

function generateUsersXml() {
    $files = glob(USER_FILES_PATTERN);
    $filesCount = 0;

    foreach ($files as $file) {
        $filesCount += 1;
        $data = csv_to_array($file, ",");

        $xmlBuilder = new UsersXmlBuilder("output/users_{$filesCount}.xml");
        $xmlBuilder->setData($data);
        $xmlBuilder->buildXml();
    }
}


//$xmlBuilder = new IssuesXmlBuilder("output/test.xml");



//for ($i = 0; 2; $i++) {
//    $xmlBuilder = new IssuesXmlBuilder("output/test-" . $i . ".xml");
//}