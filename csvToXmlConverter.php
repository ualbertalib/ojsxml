<?php

namespace OJSXml;

require("./app/bootstrap.php");

class csvToXmlConverter {

    var $_command;
    var $_user;
    var $_sourceDir;
    var $_destinationDir;

    /**
     * csvToXmlConverter constructor.
     *
     * @param array $argv Command line arguments
     */
    function __construct($argv = array()) {
        array_shift($argv);

        if (sizeof($argv) != 4) {
            $this->usage();
        }

        $this->_command = array_shift($argv);
        $this->_user = array_shift($argv);
        $this->_sourceDir = array_shift($argv);
        $this->_destinationDir = array_shift($argv);

        if ($this->_command != "issues" && $this->_command != "users" & $this->_command != "help") {
            echo '[Error]: Valid commands are "issues" or "users"' . PHP_EOL;
            exit();
        }

        if (!is_dir($this->_sourceDir)) {
            echo "[Error]: <source_directory> must be a valid directory";
            exit();
        } else if ($this->_command == "issues" && !is_dir($this->_sourceDir . "/issue_cover_images")) {
            echo '[Error]: "The subdirectory "<source_directory>/issue_cover_images" must exist when converting issues' . PHP_EOL;
            exit();
        } else if ($this->_command == "issues" && !is_dir($this->_sourceDir . "/article_galleys")) {
            echo '[Error]: "The subdirectory "<source_directory>/article_galleys" must exist when converting issues' . PHP_EOL;
            exit();
        }
        if (!is_dir($this->_destinationDir)) {
            echo "[Error]: <destination_directory> must be a valid directory" . PHP_EOL;
            exit();
        }

    }

    /**
     * Prints CLI usage instructions to console
     */
    public function usage() {
        echo "Script to convert issue or user CSV data to OJS XML" . PHP_EOL
            . "Usage: issues|users <ojs_username> <source_directory> <destination_directory>" . PHP_EOL . PHP_EOL
            . 'NB: `issues` source directory must include "issue_cover_images" and "article_galleys" directory' . PHP_EOL;
        exit();
    }

    /**
     * Executes tasks associated with given command
     */
    public function execute() {
        switch ($this->_command) {
            case "issues":
                $this->generateIssuesXml($this->_sourceDir, $this->_destinationDir);
                break;
            case "users":
                $this->generateUsersXml($this->_sourceDir, $this->_destinationDir);
                break;
            case "help":
                $this->usage();
                break;
        }
    }

    /**
     * Converts issue CSV data to OJS Native XML files
     *
     * @param string $sourceDir Location of CSV files
     * @param string $destinationDir Target directory for XML files
     */
    private function generateIssuesXml($sourceDir, $destinationDir) {
        $dbManager = new DBManager();
        $dbManager->importIssueCsvData($sourceDir . "/*");

        $issueCoversDir = $sourceDir . "/issue_cover_images/";
        $issueCount = $dbManager->getIssueCount();

        $articleGalleysDir = $sourceDir . "/article_galleys/";

        echo "Running issue CSV-to-XML conversion..." . PHP_EOL
            . "----------------------------------------" . PHP_EOL;

        for ($i = 0; $i < ceil($issueCount / Config::get("issues_per_file") ); $i++) {
            $xmlBuilder = new IssuesXmlBuilder(
                $destinationDir . "/issues_{$i}.xml",
                $dbManager,
                $issueCoversDir,
                $articleGalleysDir,
                $this->_user);
            $xmlBuilder->setIteration($i);
            $xmlBuilder->buildXml();
        }

        echo "----------------------------------------" . PHP_EOL
            . "Successfully converted {$issueCount} issue(s)." . PHP_EOL;
    }

    /**
     * Converts user CSV data to OJS User XML files
     *
     * @param string $sourceDir Location of CSV files
     * @param string $destinationDir Target directory for XML files
     */
    private function generateUsersXml($sourceDir, $destinationDir) {
        $files = glob($sourceDir . "/*");
        $filesCount = 0;

        echo "Running user CSV-to-XML conversion..." . PHP_EOL;

        foreach ($files as $file) {
            $filesCount += 1;
            $data = csv_to_array($file, ",");

            $xmlBuilder = new UsersXmlBuilder($destinationDir . "/users_{$filesCount}.xml");
            $xmlBuilder->setData($data);
            $xmlBuilder->buildXml();
        }

        echo "Successfully converted {$filesCount} user file(s)." . PHP_EOL;
    }
}

$tool = new csvToXmlConverter(isset($argv) ? $argv : array());
$tool->execute();
