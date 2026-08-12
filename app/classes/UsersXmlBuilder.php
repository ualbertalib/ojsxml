<?php


namespace OJSXml;

use DOMDocument;
use DOMXPath;
use RuntimeException;
use UnexpectedValueException;

class UsersXmlBuilder extends XMLBuilder {

    private array $_data;
    private bool $_isTest;
    private string $_userGroupsXml;
    private array $_userGroupNames = array();

    public function __construct($isTest, $filePath, $userGroupsFile, &$dbManager = null) {
        $this->_isTest = $isTest;
        $this->loadUserGroups($userGroupsFile);
        parent::__construct($filePath, $dbManager);
    }

    /**
     * Load the journal-specific user groups from an OJS 3.5 user export.
     *
     * @param string $filePath Full PKPUsers export or standalone user_groups XML
     */
    private function loadUserGroups($filePath) {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("The OJS 3.5 user groups XML file is not readable: {$filePath}");
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->load($filePath, LIBXML_NONET | LIBXML_NOBLANKS);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            $message = empty($errors) ? "Invalid XML" : trim($errors[0]->message);
            throw new RuntimeException("Unable to read user groups XML: {$message}");
        }

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query("//*[local-name()='user_groups'][1]");
        if ($nodes->length !== 1) {
            throw new RuntimeException("The user groups XML must contain one user_groups element.");
        }

        $userGroupsNode = $nodes->item(0);
        $this->_userGroupsXml = $document->saveXML($userGroupsNode);

        $groupNodes = $xpath->query("./*[local-name()='user_group']", $userGroupsNode);
        foreach ($groupNodes as $groupNode) {
            $mastheadNodes = $xpath->query("./*[local-name()='masthead']", $groupNode);
            if ($mastheadNodes->length !== 1) {
                throw new RuntimeException(
                    "Each OJS 3.5 user_group definition must contain one masthead element."
                );
            }
        }

        $nameNodes = $xpath->query("./*[local-name()='user_group']/*[local-name()='name']", $userGroupsNode);
        foreach ($nameNodes as $nameNode) {
            $name = trim($nameNode->textContent);
            if ($name !== "") {
                $this->_userGroupNames[$name] = true;
            }
        }

        if (empty($this->_userGroupNames)) {
            throw new RuntimeException("The user_groups element does not define any named groups.");
        }
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
        $this->getXmlWriter()->writeRaw($this->_userGroupsXml);
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
            $this->getXmlWriter()->startElement("name");
            $this->addLocaleAttribute();
            $this->getXmlWriter()->writeRaw(xmlFormat($userData["affiliation"]));
            $this->getXmlWriter()->endElement();
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

        $assignedRoles = array();
        for ($i = 1; $i < 6; $i++) {
            if (isset($userData["role" . $i]) && $userData["role" . $i] != "") {
                $role = trim($userData["role" . $i]);
                if (isset($assignedRoles[$role])) continue;
                if (!isset($this->_userGroupNames[$role])) {
                    throw new UnexpectedValueException(
                        "Unknown user group '{$role}' for user '{$userData["username"]}'. " .
                        "Use a group name from the supplied OJS user export."
                    );
                }

                $this->getXmlWriter()->startElement("user_user_group");
                $this->getXmlWriter()->startElement("user_group_ref");
                $this->getXmlWriter()->writeRaw(xmlFormat($role));
                $this->getXmlWriter()->endElement();
                $this->getXmlWriter()->startElement("masthead");
                $this->getXmlWriter()->writeRaw("false");
                $this->getXmlWriter()->endElement();
                $this->getXmlWriter()->endElement();
                $assignedRoles[$role] = true;
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
