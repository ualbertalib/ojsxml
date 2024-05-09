<?php


namespace OJSXml;

use XMLWriter;

define("ISSUE_COVERS_DIR", "csv/abstracts/issue_cover_images/");

class IssuesXmlBuilder extends XMLBuilder {
    /** @var array $_sectionAbbreviations */
    private array $_sectionAbbreviations = array();
    private int $_iteration = 0;
    private string $_issueCoversDir;
    private string $_articleGalleysDir;
    private string $_user;
    private int $_issueIdPrefix;

    /**
     * IssuesXmlBuilder constructor.
     *
     * @param string $filePath
     * @param DBManager $dbManager
     * @param string $issueCoversDir
     * @param string $articleGalleysDir
     * @param string $user
     */
    public function __construct($filePath, &$dbManager, $issueCoversDir, $articleGalleysDir, $user) {
        parent::__construct($filePath, $dbManager);
        $this->_issueCoversDir = $issueCoversDir;
        $this->_articleGalleysDir = $articleGalleysDir;
        $this->_user = $user;
    }

    /**
     * @param int $iteration Current loop of all files being written
     */
    public function setIteration($iteration) {
        $this->_iteration =$iteration;
    }

    /**
     * Issue builder where one builder results one xml file for up to ISSUES_PER_FILE issues
     */
    public function buildXml() {
        $this->getXmlWriter()->startElement("issues");
        $this->_setXmlnsAttributes(true);

        $issuesData = $this->getDBManager()->getIssuesData($this->_iteration);
        $this->_issueIdPrefix = 10;
        foreach ($issuesData as $issueData) {
            $this->writeIssue($issueData);

            $issueMessage = empty($issueData['issue']) ? "" : ", Iss. {$issueData['issue']}";
            Logger::print("Vol. {$issueData['volume']}{$issueMessage} - {$issueData["issueTitle"]} successfully converted");
            $this->_issueIdPrefix += 10;
        }

        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->endDocument();
        $this->getXmlWriter()->flush();
    }

    /**
     * Writes complete data on an issue including articles
     *
     * @param array $issueData
     */
    function writeIssue($issueData) {
        $this->getXmlWriter()->startElement("issue");
        $this->_setXmlnsAttributes();
        $this->getXmlWriter()->writeAttribute("published", "1");

        $this->writeIssueMetadata($issueData);
        $this->writeSections($issueData["issueTitle"], $issueData["volume"], $issueData["issue"]);
        $this->writeIssueCover($issueData);
        $this->writeArticles($issueData);

        $this->getXmlWriter()->endElement();
    }

    /**
     * Writes issue metadata including, volume, issue, date, title
     *
     * @param array $issueData Metadata about a single issue
     */
    function writeIssueMetadata($issueData) {
        $this->getXmlWriter()->startElement("issue_identification");

        if ($issueData['volume'] != "") {
            $this->getXmlWriter()->startElement("volume");
            $this->getXmlWriter()->writeRaw($issueData["volume"]);
            $this->getXmlWriter()->endElement();
        }

        if ($issueData["issue"] != "") {
            $this->getXmlWriter()->startElement("number");
            $this->getXmlWriter()->writeRaw($issueData["issue"]);
            $this->getXmlWriter()->endElement();
        }

        $this->getXmlWriter()->startElement("year");
        $this->getXmlWriter()->writeRaw($issueData["year"]);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("title");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(xmlFormat($issueData["issueTitle"]));
        $this->getXmlWriter()->endElement();
		
		if(trim($issueData["issueTitle_2"])!=''){
		$this->getXmlWriter()->startElement("title");
        $this->addLocaleAttribute($issueData["locale_2"]);
        $this->getXmlWriter()->writeRaw(xmlFormat($issueData["issueTitle_2"]));
        $this->getXmlWriter()->endElement();
		}
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("date_published");
        $this->getXmlWriter()->writeRaw(date("Y-m-d", strtotime($issueData["datePublished"])));
        $this->getXmlWriter()->endElement();
    }

    /**
     * Handles sections and stores abbreviations for future use
     *
     * @param string $titleName Issue title
     * @param string $volume Volume number
     */
    function writeSections($titleName, $volume, $issue) {
        $sectionsData = $this->getDBManager()->getSectionsData($titleName, $volume, $issue);

        $this->getXmlWriter()->startElement("sections");
        foreach ($sectionsData as $sectionData) {
            $this->writeSection($sectionData);
            $this->_sectionAbbreviations[] = $sectionData["sectionAbbrev"];
        }

        $this->getXmlWriter()->endElement();
    }

    /**
     * Writes out section metadata for an issue
     *
     * @param array $sectionData
     */
    function writeSection($sectionData) {
        $this->getXmlWriter()->startElement("section");
        $sectionAbbrev = xmlFormat($sectionData["sectionAbbrev"]);
        $this->getXmlWriter()->writeAttribute("ref", $sectionAbbrev);

        $this->getXmlWriter()->startElement("abbrev");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw($sectionAbbrev);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("policy");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("title");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(xmlFormat($sectionData["sectionTitle"]));
        $this->getXmlWriter()->endElement();
		
		if($sectionData["sectionTitle_2"] != ''){
			$this->getXmlWriter()->startElement("title");
			$this->addLocaleAttribute($sectionData["locale_2"]);
			$this->getXmlWriter()->writeRaw(xmlFormat($sectionData["sectionTitle_2"]));
			$this->getXmlWriter()->endElement();
		}
        $this->getXmlWriter()->endElement();
    }

    /**
     * Convert and store cover image as base64
     *
     * @param array $issueData
     */
    function writeIssueCover($issueData) {
        if (trim($issueData["cover_image_filename"] == "")) return;

        $path = $this->_issueCoversDir . $issueData["cover_image_filename"];
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $coverImageBase64 = base64_encode($data);

        $this->getXmlWriter()->startElement("covers");
        $this->getXmlWriter()->startElement("cover");
        $this->addLocaleAttribute();

        $this->getXmlWriter()->startElement("cover_image");
        $this->getXmlWriter()->writeRaw($issueData["cover_image_filename"]);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("cover_image_alt_text");
        $this->getXmlWriter()->writeRaw(xmlFormat($issueData["cover_image_alt_text"]));
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("embed");
        $this->getXmlWriter()->writeAttribute("encoding", "base64");
        $this->getXmlWriter()->writeRaw($coverImageBase64);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->endElement();
        $this->getXmlWriter()->endElement();

    }

    /**
     * Writes articles by section for an issue
     *
     * @param array $issueData
     */
    function writeArticles($issueData) {
        $this->getXmlWriter()->startElement("articles");
        $this->_setXmlnsAttributes();

        $currentId = 0;
        $publicationSeq = 0;

        // Populate articles by section
        foreach ($this->_sectionAbbreviations as $key => $sectionAbbrev) {
            $articlesData = $this->getDBManager()->getArticlesDataBySection(
                $issueData["issueTitle"],
                $issueData["volume"],
                $issueData["issue"],
                $sectionAbbrev
            );

            foreach ($articlesData as $articleData) {

                $articleData["currentId"] = (int) ($this->_issueIdPrefix . $currentId);
                $articleData["publicationSeq"] = $publicationSeq;
                $articleData["pages"] = $articleData["startPage"] . "-" . $articleData["endPage"];

                $this->writeArticle($articleData);

                $currentId += 1;
                $publicationSeq += 1;
            }
        }

        $this->getXmlWriter()->endElement();
    }

    /**
     * Write article and publication data for a single article
     *
     * @param array $articleData
     */
    function writeArticle($articleData) {
        $this->getXmlWriter()->startElement("article");
        $this->getXmlWriter()->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $this->getXmlWriter()->writeAttribute("status", "3");
        $this->getXmlWriter()->writeAttribute("stage" ,"production");
        $this->getXmlWriter()->writeAttribute("current_publication_id", $articleData["currentId"]);

        $this->_writeIdElement($articleData["currentId"]);

        $this->_writeSubmissionFile($articleData);

        $this->writePublication($articleData);

        $this->getXmlWriter()->endElement();

    }

    function _writeSubmissionFile(array $articleData) {

        if (trim($articleData["fileName"] == "")) return;

        $path = $this->_articleGalleysDir . $articleData["fileName"];
		$filesize = filesize($path);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $articleGalleyBase64 = base64_encode($data);

        $this->getXmlWriter()->startElement("submission_file");
        $this->getXmlWriter()->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $this->getXmlWriter()->writeAttribute("id", $articleData["currentId"]);
		$this->getXmlWriter()->writeAttribute("file_id", $articleData["currentId"]);
        $this->getXmlWriter()->writeAttribute("stage", "proof");
        $this->getXmlWriter()->writeAttribute("viewable", "false");
        $this->getXmlWriter()->writeAttribute("genre", $this->_getGenreName());
        $this->getXmlWriter()->writeAttribute("uploader", $this->_user);
        $this->getXmlWriter()->writeAttribute("xsi:schemaLocation", "http://pkp.sfu.ca native.xsd");

		$this->getXmlWriter()->startElement("name");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw($this->_user . ", " . $articleData["fileName"]);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("file");
		$this->getXmlWriter()->writeAttribute("id", $articleData["currentId"]);
		$this->getXmlWriter()->writeAttribute("filesize", $filesize);
		$this->getXmlWriter()->writeAttribute("extension", $type);

        $this->getXmlWriter()->startElement("embed");
        $this->getXmlWriter()->writeAttribute("encoding", "base64");
        $this->getXmlWriter()->writeRaw($articleGalleyBase64);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->endElement();


        $this->getXmlWriter()->endElement();
    }

    /**
     * Write publication data for a single article
     *
     * @param array $articleData
     */
    function writePublication($articleData) {
        $this->getXmlWriter()->startElement("publication");
        $this->getXmlWriter()->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeAttribute("version", "1");
        $this->getXmlWriter()->writeAttribute("status", "3");
        $this->getXmlWriter()->writeAttribute("date_published", date("Y-m-d", strtotime(trim($articleData["datePublished"]))));
        $this->getXmlWriter()->writeAttribute("section_ref", $articleData["sectionAbbrev"]);
        $this->getXmlWriter()->writeAttribute("seq", $articleData["publicationSeq"]);

        $this->_writeIdElement($articleData["currentId"]);

        $this->writePublicationMetadata($articleData);
        $this->writeAuthors($articleData);
        $this->writeArticleGalley($articleData);
		
		$this->writeCitations($articleData["citations"]);
		

        $this->getXmlWriter()->startElement("pages");
        $this->getXmlWriter()->writeRaw(trim($articleData["pages"], "-"));
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->endElement();
    }
	
	
	function writeCitations($citationString){
		
		if ($citationString != "") {
            $citations = parseNewLine($citationString);
            $this->getXmlWriter()->startElement("citations");
            $this->addLocaleAttribute();
            foreach ($citations as $citation) {
                $this->getXmlWriter()->startElement("citation");
                $this->getXmlWriter()->writeRaw(xmlFormat(trim($citation)));
                $this->getXmlWriter()->endElement();
            }
            $this->getXmlWriter()->endElement();
        }
		
	}
	

    /**
     * Writes out publication metadata, including, title, abstract, keywords, etc.
     *
     * @param array $articleData
     */
    function writePublicationMetadata($articleData) {

        $doi = trim($articleData["DOI"]);
        if ($doi != "") {
            $this->getXmlWriter()->startElement("id");
            $this->getXmlWriter()->writeAttribute("type", "doi");
            $this->getXmlWriter()->writeAttribute("advice", "update");
            $this->getXmlWriter()->writeRaw(xmlFormat($doi));
            $this->getXmlWriter()->endElement();
        }

        $this->getXmlWriter()->startElement("title");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["articleTitle"])));
        $this->getXmlWriter()->endElement();
		
		if($articleData["articleTitle_2"] != ''){
			$this->getXmlWriter()->startElement("title");
			$this->addLocaleAttribute($articleData["locale_2"]);
			$this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["articleTitle_2"])));
			$this->getXmlWriter()->endElement();
		}

        if (trim($articleData["subTitle"]) != "") {
            $this->getXmlWriter()->startElement("subtitle");
            $this->addLocaleAttribute();
            $this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["subTitle"])));
            $this->getXmlWriter()->endElement();
        }

        $this->getXmlWriter()->startElement("abstract");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["abstract"])));
        $this->getXmlWriter()->endElement();
		
		if($articleData["articleAbstract_2"] != ''){
			$this->getXmlWriter()->startElement("abstract");
			$this->addLocaleAttribute($articleData["locale_2"]);
			$this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["articleAbstract_2"])));
			$this->getXmlWriter()->endElement();
		}
		
		
		
		$this->getXmlWriter()->startElement("licenseUrl");        
        $this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["licenseUrl"])));
        $this->getXmlWriter()->endElement();
		
		$this->getXmlWriter()->startElement("copyrightHolder"); 
		$this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["copyrightHolder"])));
        $this->getXmlWriter()->endElement();
		
		if(trim($articleData["copyrightYear"]) != ""){
			$this->getXmlWriter()->startElement("copyrightYear");        
			$this->getXmlWriter()->writeRaw(xmlFormat(trim($articleData["copyrightYear"])));
			$this->getXmlWriter()->endElement();
		}
		
		

        if (semiColonFix($articleData["keywords"] != "")) {
            $keywordArray = parseSemiColon($articleData["keywords"]);
            $this->getXmlWriter()->startElement("keywords");
            $this->addLocaleAttribute();
            foreach ($keywordArray as $keyword) {
                $this->getXmlWriter()->startElement("keyword");
                $this->getXmlWriter()->writeRaw(xmlFormat(trim($keyword)));
                $this->getXmlWriter()->endElement();
            }
            $this->getXmlWriter()->endElement();
        }
		
		
		
		
		
    }

    /**
     * Adds all author objects
     *
     * @param array $articleData
     */
    function writeAuthors($articleData) {
        $authors = new Authors($articleData["authors"], $articleData["authorEmail"], $articleData["affiliations"]);

        $this->getXmlWriter()->startElement("authors");
        $this->_setXmlnsAttributes();

        $authorIndex = 0;
        foreach ($authors->getAuthors() as $authorData) {
            $authorData["seq"] = $authorIndex;
            $authorData["currentId"] = $articleData["currentId"];
            $this->writeAuthor($authorData);
            $authorIndex += 1;
        }

        $this->getXmlWriter()->endElement();

    }

    /**
     * Adds an individual author
     *
     * @param array $autorData
     */
    function writeAuthor($autorData) {
        $this->getXmlWriter()->startElement("author");
        $this->getXmlWriter()->writeAttribute("user_group_ref", "Author");
        // First author in list is considered primary contact
        if ($autorData["seq"] == 0) {
            $this->getXmlWriter()->writeAttribute("primary_contact", "true");
        }
        $this->getXmlWriter()->writeAttribute("seq", $autorData["seq"]);
        $this->getXmlWriter()->writeAttribute("id", $autorData["currentId"]);

        $this->getXmlWriter()->startElement("givenname");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(trim($autorData["givenname"]));
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("familyname");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(trim($autorData["familyname"]));
        $this->getXmlWriter()->endElement();

        if (trim($autorData["affiliation"]) != "") {
            $this->getXmlWriter()->startElement("affiliation");
            $this->addLocaleAttribute();
            $this->getXmlWriter()->writeRaw(trim($autorData["affiliation"]));
            $this->getXmlWriter()->endElement();
        }

        $this->getXmlWriter()->startElement("country");
        $this->getXmlWriter()->writeRaw(trim($autorData["country"]));
        $this->getXmlWriter()->endElement();

        if (trim($autorData["email"]) != "") {
            $this->getXmlWriter()->startElement("email");
            $this->getXmlWriter()->writeRaw(trim($autorData["email"]));
            $this->getXmlWriter()->endElement();
        }

        $this->getXmlWriter()->endElement();
    }

    function writeArticleGalley($articleData) {
        $fileName = $articleData["fileName"];
        $fileExt = get_file_extension($fileName);
        // Disabled for OJS 3.2
//        $pdfUrl = Config::get("pdf_url");

        $this->getXmlWriter()->startElement("article_galley");
        $this->getXmlWriter()->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeAttribute("approved", "false");
        $this->getXmlWriter()->writeAttribute("xsi:schemaLocation","http://pkp.sfu.ca native.xsd");

        $this->_writeIdElement($articleData["currentId"]);

        $this->getXmlWriter()->startElement("name");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw(xmlFormat(strtoupper($fileExt)));
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("seq");
        $this->getXmlWriter()->writeRaw("0");
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("submission_file_ref");
        $this->getXmlWriter()->writeAttribute("id", $articleData["currentId"]);
        $this->getXmlWriter()->endElement();

        // Disabled for OJS 3.2
//        $this->getXmlWriter()->startElement("remote");
//        $this->getXmlWriter()->writeAttribute("src", $pdfUrl . xmlFormat($fileName));
//        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->endElement();
    }

    // Helper functions

    /**
     * @param false $includeSchemaLocation Includes xsi schema location
     */
    function _setXmlnsAttributes($includeSchemaLocation = false) {
        $this->getXmlWriter()->writeAttribute("xmlns","http://pkp.sfu.ca");
        $this->getXmlWriter()->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        if ($includeSchemaLocation) {
            $this->getXmlWriter()->writeAttribute("xsi:schemaLocation","http://pkp.sfu.ca native.xsd");
        }
    }



    /**
     * Writes an ID field for linking submissions/publications
     *
     * @param $currentId
     */
    function _writeIdElement($currentId) {
        $this->getXmlWriter()->startElement("id");
        $this->getXmlWriter()->writeAttribute("type", "internal");
        $this->getXmlWriter()->writeAttribute("advice", "ignore");
        $this->getXmlWriter()->writeRaw($currentId);
        $this->getXmlWriter()->endElement();
    }

    function _getGenreName() {
        $customFileGenre = Config::get('genreName');
        if (!empty($customFileGenre)) {
            return $customFileGenre;
        }
        return 'Article Text';
    }
}
