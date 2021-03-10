<?php
/**
 * This is used when the authorStr is in the format of:
 * Lastname, Firstname;LastName, FirstName....
 */
namespace OJSXml;


class Authors
{

    protected $authors, $email, $affiliations;

    function __construct($authorStr, $email, $affiliations)
    {   
        // if part of array is blank then filter it out
        $this->authors = array_filter(explode(";",$authorStr), function($str){
            return trim($str)==''?false:true;
        });        
       // print_r($this->authors);
        
        $this->email = array_filter(explode(";",$email),function($str){
            return trim($str)==''?false:true;
        });        
      //   print_r($this->email);
        $this->affiliations = $affiliations;
    }

    /**
     * Gets authors with info, including, given name, family name, email, affiliation, and country
     *
     * @return array Array of authors
     */
    public function getAuthors() {
        $authorCountry = Config::get("author_country");

        $authorsArray = array();
        for ($i = 0; $i < $this->authorCount(); $i++) {

            if ($this->getEmail($i) == "") {
                $authorEmail = $this->getEmail(0);
            } else {
                $authorEmail = $this->getEmail($i);
            }

            $authorsArray[] = array(
                "givenname" => $this->getGivenName($i),
                "familyname" => $this->getFamilyName($i),
                "affiliation" => $this->getAffiliation($i),
                "country" => $authorCountry,
                "email" => $authorEmail,
            );
        }

        return $authorsArray;
    }

    /**
     * How many authors the object contains
     *
     * @return int Number of authors
     */
    public function authorCount(){
        return count($this->authors);
    }

    private function getEmail($idx){
        return $this->email[$idx] ?? "";
    }

    private function getGivenName($idx){
        $name = explode(",",$this->authors[$idx]);

        // if givenName is blank set the givenname to the family name ($name[0]) then make the family name blank.
        if(!isset($name[1])){
            $this->authors[$idx] = '';
            return trim($name[0]);
        }else{
            return trim($name[1]);
        }
    }

    private function getAffiliation($idx){
        $affiliation = "";

        if($this->affiliations != '') {
            $affiliationAry = explode(";",$this->affiliations);

            if(isset($affiliationAry[$idx])){
                $affiliation = $affiliationAry[$idx];
            } else if (isset($affiliationAry[0])) {
                $affiliation = $affiliationAry[0];
            }

        }

        return xmlFormat(trim($affiliation));
    }

    private function getFamilyName($idx){
        $name = explode(",",$this->authors[$idx]);

        if( isset($name[0])) {
            return trim($name[0]);
        } else {
            return "";
        }
    }
}
