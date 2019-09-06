<?php
/**
 * This is used when the authorStr is in the format of:
 * Lastname, Firstname;LastName, FirstName....
 */
namespace OJSXml;


class Authors
{

    protected $authors, $xml, $email, $affiliations;

    function __construct($authorStr, \XMLWriter & $xml, $email, $affiliations)
    {
        $this->authors = explode(";",$authorStr);
        $this->email = $email;
        $this->xml = $xml;
        $this->affiliations = $affiliations;
    }

    function getAuthorXML(){

        $this->xml->startElement("authors");
        for($i=0; $i<$this->authorCount(); $i++){
            $this->xml->startElement("author");
            $this->xml->writeAttribute("user_group_ref","Author");
            $this->xml->writeAttribute("include_in_browse","true"); 
            // the first author in the list is considered the primary contact
            if($i==0){
                $this->xml->writeAttribute("primary_contact","true");
            }
            $this->firstName($i);
            $this->lastName($i);
            $this->affiliation($i);
            $this->email();
            $this->xml->endElement();
        }
        $this->xml->endElement();
        return $this->xml;

    }

    private function email(){
        $this->xml->startElement('email');
        $this->xml->writeRaw($this->email);
        $this->xml->endElement();
    }
   private function firstName($idx){
        $name = explode(",",$this->authors[$idx]);

        $this->xml->startElement("firstname");
        if( isset($name[1])){
        $this->xml->writeRaw(trim($name[1]));
        }
        $this->xml->endElement();

    }
    private function affiliation($idx){
        $affiliation = "";

        if($this->affiliations != ''){
        $affiliationAry = explode(";",$this->affiliations);

        if(isset($affiliationAry[$idx])){
            $affiliation = $affiliationAry[$idx];
        }elseif(isset($affiliationAry[0])){
            $affiliation = $affiliationAry[0];
        }

        $this->xml->startElement("affiliation");
            $this->xml->writeRaw( xmlFormat(trim($affiliation)));
        $this->xml->endElement();

        }

    }

    private function lastName($idx){
        $name = explode(",",$this->authors[$idx]);

        $this->xml->startElement("lastname");
        if( isset($name[0])) {
            $this->xml->writeRaw(trim($name[0]));
        }
        $this->xml->endElement();

    }


    function authorCount(){
        return count($this->authors);
    }

}