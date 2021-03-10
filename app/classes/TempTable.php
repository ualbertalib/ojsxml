<?php


namespace OJSXml;



use Exception;

class TempTable
{

    protected $db;
    protected $tempTableName;

    /**
     * TempTable constructor.
     * @param \App\Database $db
     * @param string $tempTableName
     */
    function __construct(Database $db, $tempTableName='ojs_import_helper')
    {
        $this->db = $db;
        $this->tempTableName=$tempTableName;

        $this->createTable();

    }

    function truncate(){
        $this->db->query("DELETE FROM " . $this->tempTableName);
        $this->db->execute();
    }

    function isEmpty(){

        $row = $this->db->single("Select count(*) as counter from " .$this->tempTableName);
        if($row['counter']>0){
            throw new Exception("Table '" . $this->tempTableName . "' must be blank to start the process");
        }
        return true;
    }



    private function createTable(){

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->tempTableName . " (      
                      `issueTitle` varchar(500)  DEFAULT NULL,
                      `sectionTitle` varchar(500)  DEFAULT NULL,
                      `sectionAbbrev` varchar(500)  DEFAULT NULL,
                      `authors` varchar(500)  DEFAULT NULL, 
                      `affiliations` varchar(500) DEFAULT NULL, 
                      `DOI` varchar(500) DEFAULT NULL,                       
                      `articleTitle` varchar(500)  DEFAULT NULL, 
                      `subTitle` varchar(500)  DEFAULT NULL, 
                      `year` int(11) DEFAULT NULL,
                      `datePublished` datetime DEFAULT NULL,
                      `volume` int(11) DEFAULT NULL,
                      `issue` int(11) DEFAULT NULL,
                      `startPage` int(11) DEFAULT NULL,
                      `endPage` varchar(50)  DEFAULT NULL,
                      `articleAbstract` varchar(2000)  DEFAULT NULL,
                      `galleyLabel` varchar(500)  DEFAULT NULL,
                      `authorEmail` varchar(500)  DEFAULT NULL,
                      `fileName` varchar(500)  DEFAULT NULL,
                      `supplementary_files` varchar(500)  DEFAULT NULL,                      
                      `dependent_files` varchar(500)  DEFAULT NULL,   
                      `keywords` varchar(500)  DEFAULT NULL,
                      `cover_image_filename` varchar(500) DEFAULT NULL,
                      `cover_image_alt_text` varchar(500) DEFAULT NULL,
                      `language` varchar(10) DEFAULT NULL
                    )";

        $this->db->query($sql);
        $this->db->execute();
 
    }
    


    function insertAssocDataIntoTempTable($data){


        $sql = "INSERT into  " . $this->tempTableName . "
                              (issueTitle,sectionTitle,sectionAbbrev,authors,affiliations,DOI,articleTitle,subTitle,`year`,datePublished,volume,issue,startPage,endPage,articleAbstract,galleyLabel,
                              authorEmail,fileName,supplementary_files,dependent_files,keywords,cover_image_filename,cover_image_alt_text,language) 
                                VALUES (:issueTitle,:sectionTitle,:sectionAbbrev,:authors,:affiliations,:DOI, :articleTitle,:subTitle,:year,:datePublished,
                                :volume,:issue,:startPage,:endPage, :articleAbstract,:galleyLabel, 
                              :authorEmail,:fileName,:supplementary_files,:dependent_files,:keywords,:cover_image_filename,:cover_image_alt_text,:language)";
        $this->db->query($sql);
        $this->db->bind(':issueTitle', $data['issueTitle']??'');
        $this->db->bind(':sectionTitle', $data['sectionTitle']);
        $this->db->bind(':sectionAbbrev', $data['sectionAbbrev']);
        $this->db->bind(':authors', $data['authors']);

        if(isset($data['affiliation']) || isset($data['affiliations'])){
        $this->db->bind(':affiliations', (isset($data['affiliation'])?$data['affiliation']:$data['affiliations']));
        }elseif(isset($data['authorAffiliation'])){
            $this->db->bind(':affiliations', $data['authorAffiliation']);
        }else{
            $this->db->bind(':affiliations', '');
        }

        $this->db->bind(':DOI', $data['DOI']??'');
        $this->db->bind(':articleTitle', $data['articleTitle']);
        $this->db->bind(':subTitle', $data['subTitle']??'');
        $this->db->bind(':year', $data['year']);
        $this->db->bind(':datePublished', $data['datePublished']);
        $this->db->bind(':volume', $data['volume']);
        $this->db->bind(':issue', $data['issue'] ?? $data['Issue']);
        $this->db->bind(':startPage', $data['startPage']);
        $this->db->bind(':endPage', $data['endPage']);
        $this->db->bind(':articleAbstract', $data['articleAbstract']);
        $this->db->bind(':galleyLabel', $data['galleyLabel']);
        $this->db->bind(':authorEmail', $data['authorEmail']);
        $this->db->bind(':fileName', $data['fileName']);
        $this->db->bind(':supplementary_files', $data['supplementary_files']?? '' );
        $this->db->bind(':dependent_files', $data['dependent_files']?? '' );
        


        $val = "";
        if(key_exists('keywords',$data)){
            $val = $data['keywords'];
        }
        $this->db->bind(':keywords',$val);
        $this->db->bind(':cover_image_filename', $data['cover_image_filename']??'');
        $this->db->bind(':cover_image_alt_text', $data['cover_image_alt_text']??'');
        $this->db->bind(':language', $data['language']??'');
        
        $this->db->execute();
    }

}