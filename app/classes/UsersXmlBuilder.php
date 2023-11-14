<?php


namespace OJSXml;


class UsersXmlBuilder extends XMLBuilder {

    private array $_data;
    private bool $_isTest;

    public function __construct($isTest, $filePath, &$dbManager = null) {
        $this->_isTest = $isTest;
        parent::__construct($filePath, $dbManager);
    }


    /**
     * Set data to object used for creating xml
     *
     * @param array $data
     */
    function setData($data) {
        $this->_data = $data;
    }


    /**
     * Converts single csv file of users to import xml
     */
    public function buildXml() {
        $this->getXmlWriter()->startElement("PKPUsers");
        $this->getXmlWriter()->writeAttribute("xmlns", "http://pkp.sfu.ca");
        $this->getXmlWriter()->writeAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $this->getXmlWriter()->writeAttribute("xsi:schemaLocation", "http://pkp.sfu.ca pkp-users.xsd");
        $this->getXmlWriter()->startElement("users");

        foreach ($this->_data as $userData) {
            $this->writeUser($userData);
        }

        $this->getXmlWriter()->endElement();
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->endDocument();
        $this->getXmlWriter()->flush();
    }

    /**
     * @param array $userData
     */
    function writeUser($userData) {
        $this->getXmlWriter()->startElement("user");

        $this->getXmlWriter()->startElement("givenname");
        $this->addLocaleAttribute();
        $this->getXmlWriter()->writeRaw($userData["firstname"]);
        $this->getXmlWriter()->endElement();

        if (!empty($userData["lastname"])) {
            $this->getXmlWriter()->startElement("familyname");
            $this->addLocaleAttribute();
            $this->getXmlWriter()->writeRaw($userData["lastname"]);
            $this->getXmlWriter()->endElement();
        }

        if (!empty($userData["affiliation"])) {
            $this->getXmlWriter()->startElement("affiliation");
            $this->addLocaleAttribute();
            $this->getXmlWriter()->writeRaw($userData["affiliation"]);
            $this->getXmlWriter()->endElement();
        }

        if (!empty($userData["country"])) {
            $this->getXmlWriter()->startElement("country");
            $this->getXmlWriter()->writeRaw($userData["country"]);
            $this->getXmlWriter()->endElement();
        }

        $this->getXmlWriter()->startElement("email");
        $firstEmail = explode(',', $userData["email"]);
        if (sizeof($firstEmail) > 1) {
            Logger::print($userData["username"] . ' email truncated to first provided.');
        }
        $this->getXmlWriter()->writeRaw($this->_isTest ? htmlspecialchars($firstEmail[0]) . "test" : htmlspecialchars($firstEmail[0]));
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("username");
        $this->getXmlWriter()->writeRaw($userData["username"]);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("password");
        $this->getXmlWriter()->writeAttribute("must_change", "true");
        if (empty($userData["tempPassword"])) {
            $this->getXmlWriter()->writeAttribute("encryption", "plaintext");
        }

        $this->getXmlWriter()->startElement("value");
        $this->getXmlWriter()->writeRaw(empty($userData["tempPassword"]) ? '' : $userData["tempPassword"]);
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("date_registered");
        $this->getXmlWriter()->writeRaw(date("Y-m-d H:i:s"));
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("date_last_login");
        $this->getXmlWriter()->writeRaw(date("Y-m-d H:i:s"));
        $this->getXmlWriter()->endElement();

        $this->getXmlWriter()->startElement("inline_help");
        $this->getXmlWriter()->writeRaw("true");
        $this->getXmlWriter()->endElement();

        for ($i = 1; $i < 6; $i++) {
            if (isset($userData["role" . $i]) && $userData["role" . $i] != "") {
                $this->getXmlWriter()->startElement("user_group_ref");
                $this->getXmlWriter()->writeRaw(userGroupRef($userData["role" . $i]));
                $this->getXmlWriter()->endElement();
            }
        }

        if (!empty($userData["reviewInterests"])) {
            $this->getXmlWriter()->startElement("review_interests");
            $this->getXmlWriter()->writeRaw($userData["reviewInterests"]);
            $this->getXmlWriter()->endElement();
        }

        $this->getXmlWriter()->endElement();
    }



}
