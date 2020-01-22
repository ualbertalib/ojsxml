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
        // if part of array is blank then filter it out
        $this->authors = array_filter(explode(";",$authorStr), function($str){
            return trim($str)==''?false:true;
        });        
        print_r($this->authors);
        
        $this->email = array_filter(explode(";",$email),function($str){
            return trim($str)==''?false:true;
        });        
         print_r($this->email);
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
            $this->email($i);
            $this->xml->endElement();
        }
        $this->xml->endElement();
        return $this->xml;

    }

    private function email($idx){
        
        $this->xml->startElement('email');
        $this->xml->writeRaw($this->email[$idx] ?? '');
        $this->xml->endElement();
        
    }
   private function firstName($idx){
        $name = explode(",",$this->authors[$idx]);

        $this->xml->startElement("givenname");
      //  $this->xml->writeAttribute("locale","en_US");
        
             // if givenName is blank set the givenname to the family name ($name[0]) then make the family name blank.
            if(!isset($name[1])){
                $this->xml->writeRaw(trim($name[0])); 
                $this->authors[$idx] = '';  
            }else{
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

        $this->xml->startElement("familyname");
      //  $this->xml->writeAttribute("locale","en_US");
        if( isset($name[0])) {
            $this->xml->writeRaw(trim($name[0]));
        }        
        $this->xml->endElement();

    }


    function authorCount(){
        return count($this->authors);
    }

}