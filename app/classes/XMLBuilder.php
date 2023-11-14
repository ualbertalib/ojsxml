<?php


namespace OJSXml;

use XMLWriter;

abstract class XMLBuilder {
    /** @var XMLWriter $_xmlWriter */
    private $_xmlWriter;
    /** @var DBManager $_dbManager */
    private $_dbManager;
    /** @var string $_locale */
    private $_locale;

    /**
     * IssuesXmlBuilder constructor.
     *
     * @param $filePath
     */
    function __construct($filePath, &$dbManager = null) {

        $this->_xmlWriter = new XmlWriter();
        $this->_xmlWriter->openUri($filePath);
        $this->_xmlWriter->startDocument();
        $this->_xmlWriter->setIndent(true);

        if ($dbManager != null) {
            $this->_dbManager = $dbManager;
        }

        $this->_locale = Config::get("locale");
    }

    /**
     * Builds and closed xml file
     */
    abstract function buildXml();

    /**
     * @return XMLWriter
     */
    protected function getXmlWriter() {
        return $this->_xmlWriter;
    }

    /**
     * @return DBManager
     */
    protected function getDBManager() {
        return $this->_dbManager;
    }

    protected function addLocaleAttribute() {
        $this->_xmlWriter->writeAttribute("locale", $this->_locale);
    }

}
