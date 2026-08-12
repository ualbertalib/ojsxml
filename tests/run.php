<?php

/**
 * End-to-end smoke tests for OJS 3.5 issue and user XML generation.
 */

$repository = dirname(__DIR__);
$temporaryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ojsxml-' . bin2hex(random_bytes(6));
echo "Using temporary directory: {$temporaryRoot}" . PHP_EOL;

function makeDirectory($path) {
    if (!mkdir($path, 0777, true) && !is_dir($path)) {
        throw new RuntimeException("Unable to create test directory: {$path}");
    }
}

function writeCsv($path, $headers, $rows) {
    $handle = fopen($path, 'w');
    if ($handle === false) {
        throw new RuntimeException("Unable to create test CSV: {$path}");
    }

    fputcsv($handle, $headers, ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($handle, $row, ',', '"', '');
    }
    fclose($handle);
}

function runConverter($repository, $arguments) {
    $command = escapeshellarg(PHP_BINARY);
    if (!extension_loaded('sqlite3')) {
        $command .= ' -d extension=sqlite3';
    }
    $command .= ' ' . escapeshellarg($repository . '/csvToXmlConverter.php');
    foreach ($arguments as $argument) {
        $command .= ' ' . escapeshellarg($argument);
    }

    $output = array();
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);
    if ($exitCode !== 0) {
        throw new RuntimeException("Converter failed:\n" . implode("\n", $output));
    }
}

function validateXml($xmlPath, $schemaPath) {
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->load($xmlPath, LIBXML_NONET);
    $valid = $loaded && $document->schemaValidate($schemaPath);
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$valid) {
        $messages = array_map(function ($error) {
            return trim($error->message);
        }, $errors);
        throw new RuntimeException("Schema validation failed for {$xmlPath}:\n" . implode("\n", $messages));
    }

    return $document;
}

function assertXPathCount($document, $expression, $expected) {
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('pkp', 'http://pkp.sfu.ca');
    $actual = $xpath->query($expression)->length;
    if ($actual !== $expected) {
        throw new RuntimeException("Expected {$expected} matches for {$expression}; found {$actual}.");
    }
}

function removeTestDirectory($path) {
    if (!is_dir($path)) return;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}

try {
    $issuesInput = $temporaryRoot . '/issues';
    $usersInput = $temporaryRoot . '/users';
    $issuesOutput = $temporaryRoot . '/issues-output';
    $usersOutput = $temporaryRoot . '/users-output';
    makeDirectory($issuesInput . '/article_galleys');
    makeDirectory($issuesInput . '/issue_cover_images');
    makeDirectory($usersInput);
    makeDirectory($issuesOutput);
    makeDirectory($usersOutput);

    $testConfigPath = $temporaryRoot . '/config.ini';
    $databasePath = str_replace('\\', '/', $temporaryRoot . '/test.db');
    $logPath = str_replace('\\', '/', $temporaryRoot);
    $testConfig = file_get_contents($repository . '/config.ini');
    $testConfig = preg_replace('/^sqlite_location\s*=.*$/m', 'sqlite_location = "' . $databasePath . '"', $testConfig);
    $testConfig = preg_replace('/^logLocation\s*=.*$/m', 'logLocation = "' . $logPath . '"', $testConfig);
    $testConfig = preg_replace('/^dateFormat\s*=.*$/m', 'dateFormat = "d/m/Y"', $testConfig);
    file_put_contents($testConfigPath, $testConfig);
    putenv('OJSXML_CONFIG=' . $testConfigPath);

    file_put_contents($issuesInput . '/article_galleys/article.pdf', "%PDF-1.4\n%%EOF\n");
    writeCsv(
        $issuesInput . '/issues.csv',
        array('issueTitle', 'sectionTitle', 'sectionAbbrev', 'authors', 'affiliation', 'DOI',
            'articleTitle', 'year', 'datePublished', 'volume', 'issue', 'startPage', 'endPage',
            'articleAbstract', 'galleyLabel', 'authorEmail', 'fileName', 'keywords', 'citations',
            'licenseUrl', 'copyrightHolder', 'copyrightYear'),
        array(array('Test Issue', 'Articles', 'ART', 'Author, Alice', 'Test University',
            '10.1234/example', 'OJS 3.5 Article', '2026', '12/08/2026', '1', '1', '1', '8',
            'Test abstract', 'PDF', 'alice@example.com', 'article.pdf',
            'metadata;open access', "First citation\nSecond citation",
            'https://creativecommons.org/licenses/by/4.0', 'Alice Author', '2026'))
    );

    $userGroupsPath = $temporaryRoot . '/user-groups.xml';
    file_put_contents($userGroupsPath, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<PKPUsers xmlns="http://pkp.sfu.ca">
  <user_groups>
    <user_group>
      <role_id>65536</role_id><context_id>1</context_id><is_default>true</is_default>
      <show_title>true</show_title><permit_self_registration>true</permit_self_registration>
      <permit_metadata_edit>false</permit_metadata_edit><name locale="en_US">Author</name>
      <abbrev locale="en_US">AU</abbrev><stage_assignments>1:3:4:5</stage_assignments><masthead>false</masthead>
    </user_group>
  </user_groups>
  <users><user><givenname locale="en_US">Source</givenname><email>source@example.com</email>
    <username>source</username><password><value>unused-password</value></password>
    <user_user_group><user_group_ref>Author</user_group_ref><masthead>false</masthead></user_user_group>
  </user></users>
</PKPUsers>
XML
    );

    writeCsv(
        $usersInput . '/users.csv',
        array('firstname', 'lastname', 'email', 'affiliation', 'country', 'username',
            'tempPassword', 'role1', 'role2', 'role3', 'role4', 'reviewInterests'),
        array(array('Alice', 'Author', 'alice@example.com', 'Test University', 'CA', 'alice',
            'temporary-password', 'Author', '', '', '', 'metadata, open access'))
    );

    chdir($repository);
    runConverter($repository, array('issues', 'admin', $issuesInput, $issuesOutput));
    runConverter($repository, array('users', 'admin', $usersInput, $usersOutput, $userGroupsPath));

    $issueDocument = validateXml($issuesOutput . '/issues_0.xml', $repository . '/docroot/output/native.xsd');
    assertXPathCount($issueDocument, '//pkp:section[@seq="0"][@abstract_word_count="0"]', 1);
    assertXPathCount($issueDocument, '//pkp:author/pkp:affiliation/pkp:name', 1);
    assertXPathCount($issueDocument, '//pkp:keywords/pkp:keyword/pkp:name', 2);

    $userDocument = validateXml($usersOutput . '/users_1.xml', $repository . '/docroot/output/pkp-users.xsd');
    assertXPathCount($userDocument, '/pkp:PKPUsers/pkp:user_groups', 1);
    assertXPathCount($userDocument, '//pkp:user/pkp:affiliation/pkp:name', 1);
    assertXPathCount($userDocument, '//pkp:user/pkp:user_user_group/pkp:masthead', 1);

    echo "OJS 3.5 XML smoke tests passed." . PHP_EOL;
} finally {
    removeTestDirectory($temporaryRoot);
}
