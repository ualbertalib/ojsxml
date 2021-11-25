<?php


namespace OJSXml;

use OJSXml\DatabaseFactory;

class DBManager {

    /** @var $_db Database */
    var $_db;
    var $_temp_table_name;

    function __construct() {
        $db_f = new DatabaseFactory();
        $this->_db = $db_f->makeDB(Config::get('db_type'), Config::get('sqlite_location'));
        $this->_temp_table_name = Config::get('temp_table_name');
    }

    /**
     * Imports csv files into SQLite database for use in CSV-to-XML conversion
     *
     * @param string $globPattern
     */
    public function importIssueCsvData($globPattern) {
        $files = glob($globPattern);
        $tempTable = $this->makeTempTable();

        foreach ($files as $filePath) {
            $data = csv_to_array($filePath, ",");
            if (empty($data)) continue;

            foreach ($data as $row) {
                $tempTable->insertAssocDataIntoTempTable($row);
            }
        }
    }

    /**
     * @return int Issue count
     */
    public function getIssueCount() {
        $issueCountQuery = "SELECT count(*) as issueCount FROM (SELECT DISTINCT issueTitle, volume, issue FROM " . $this->_temp_table_name . ")";
        return $this->_db->single($issueCountQuery)['issueCount'];

    }

    /**
     * Gets issues data for present iteration of max allowed issue per file
     *
     * @param $iteration
     * @return array Issue data
     */
    public function getIssuesData($iteration) {
        $issues_per_file = Config::get('issues_per_file');
        $q_getIssues = "SELECT trim(issueTitle) as issueTitle, issue, year, volume, datePublished, cover_image_filename, cover_image_alt_text FROM " . $this->_temp_table_name . " Group by issueTitle, volume, issue order by volume limit " . ($iteration * $issues_per_file) ." ," . $issues_per_file;
        $this->_db->query($q_getIssues);
        return $this->_db->resultset();
    }

    /**
     * Gets sections data for a given issue
     *
     * @param string $issueTitle
     * @param string $volume
     * @return array Sections data
     */
    public function getSectionsData($issueTitle, $volume, $issue) {
        $volumeQueryPart = empty($volume) ? "" : " AND volume = :volume";
        $issueQueryPart = empty($issue) ? "" : " AND issue = :issue";
        $q_getSection ="SELECT sectionTitle,sectionAbbrev FROM  " . $this->_temp_table_name
            . " WHERE (trim(issueTitle) = :issueTitle"
            . $volumeQueryPart . $issueQueryPart . ") group by sectionTitle, sectionAbbrev ";

        $this->_db->query($q_getSection);

        $this->_db->bind(":issueTitle", trim($issueTitle));
        if (!empty($volume)) { $this->_db->bind(":volume", $volume); }
        if (!empty($issue)) { $this->_db->bind(":issue", $issue); }

        return $this->_db->resultset();
    }

    public function getAllAbstracts() {
        $q_getAbstracts = "SELECT volume, issue, articleTitle, articleAbstract FROM " . $this->_temp_table_name;
        $this->_db->query($q_getAbstracts);

        return $this->_db->resultset();
    }

    /**
     * Used to get all articles for an issue for a single section in a single issue
     *
     * @param string $issueTitle
     * @param string $sectionAbbrev
     * @return array
     */
    public function getArticlesDataBySection($issueTitle, $volume, $issue, $sectionAbbrev) {
        $volumeQueryPart = empty($volume) ? "" : " AND trim(volume) = trim(:volume)";
        $issueQueryPart = empty($issue) ? "" : " AND trim(issue) = trim(:issue)";

        $articlesBySectionQuery = "SELECT issueTitle, sectionTitle,sectionAbbrev, supplementary_files, 
            dependent_files , authors, affiliations, DOI, articleTitle, subTitle, year, (datePublished) as datePublished,	
            volume, issue, startPage, COALESCE(endPage,'') as endPage,  articleAbstract as abstract, galleyLabel, 
            authorEmail, fileName, keywords, language
            FROM " . $this->_temp_table_name .
            " WHERE trim(issueTitle) = trim(:issueTitle)"
             . $volumeQueryPart . $issueQueryPart . "and trim(sectionAbbrev) = trim(:sectionAbbrev)";
        $this->_db->query($articlesBySectionQuery);

        $this->_db->bind(":issueTitle", $issueTitle);
        $this->_db->bind(":sectionAbbrev", $sectionAbbrev);
        if (!empty($volume)) { $this->_db->bind(":volume", $volume); }
        if (!empty($issue)) { $this->_db->bind(":issue", $issue); }

        return $this->_db->resultset();
    }

    /**
     * Creates SQLite table for use in CSV-to-XML conversion
     *
     * @return TempTable
     */
    function makeTempTable() {
        $tempTable = new TempTable($this->_db, $this->_temp_table_name);
        $tempTable->truncate();
        return $tempTable;
    }
}
