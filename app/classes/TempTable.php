<?php


namespace OJSXml;



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
            throw new \Exception("Table '" . $this->tempTableName . "' must be blank to start the process");
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
                      `articleTitle` varchar(500)  DEFAULT NULL,
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
                      `keywords` varchar(500)  DEFAULT NULL
                    )";

        $this->db->query($sql);
        $this->db->execute();

    }
    
    function insertDataIntoTempTable($data){

        $sql = "INSERT into  " . $this->tempTableName . "
                              (issueTitle,sectionTitle,sectionAbbrev,authors,affiliations,articleTitle,`year`,datePublished,volume,issue,startPage,endPage,articleAbstract,galleyLabel,
                              authorEmail,fileName,keywords) 
                                values (:issueTitle,:sectionTitle,:sectionAbbrev,:authors,:affiliations,:articleTitle,:year,:datePublished,
                                :volume,:issue,:startPage,:endPage,
                                :articleAbstract,:galleyLabel,
                              :authorEmail,:fileName,:keywords)";
        $this->db->query($sql);
        $this->db->bind(':issueTitle',$data[0]);
        $this->db->bind(':sectionTitle',$data[1]);
        $this->db->bind(':sectionAbbrev',$data[2]);
        $this->db->bind(':authors',$data[3]);
        $this->db->bind(':affiliations',$data[4]);
        $this->db->bind(':articleTitle',$data[5]);
        $this->db->bind(':year',$data[6]);
        $this->db->bind(':datePublished',$data[7]);
        $this->db->bind(':volume',$data[8]);
        $this->db->bind(':issue',$data[9]);
        $this->db->bind(':startPage',$data[10]);
        $this->db->bind(':endPage',$data[11]);
        $this->db->bind(':articleAbstract',$data[12]);
        $this->db->bind(':galleyLabel',$data[13]);
        $this->db->bind(':authorEmail',$data[14]);
        $this->db->bind(':fileName',$data[15]);

        $val = "";
        if(key_exists(16,$data)){
            $val = $data[16];
        }
        $this->db->bind(':keywords',$val);

        $this->db->execute();
    }


    function insertAssocDataIntoTempTable($data){

        $sql = "INSERT into  " . $this->tempTableName . "
                              (issueTitle,sectionTitle,sectionAbbrev,authors,affiliations,articleTitle,`year`,datePublished,volume,issue,startPage,endPage,articleAbstract,galleyLabel,
                              authorEmail,fileName,keywords) 
                                VALUES (:issueTitle,:sectionTitle,:sectionAbbrev,:authors,:affiliations,:articleTitle,:year,:datePublished,
                                :volume,:issue,:startPage,:endPage,
                                :articleAbstract,:galleyLabel,
                              :authorEmail,:fileName,:keywords)";
        $this->db->query($sql);
        $this->db->bind(':issueTitle', $data['issueTitle']);
        $this->db->bind(':sectionTitle', $data['sectionTitle']);
        $this->db->bind(':sectionAbbrev', $data['sectionAbbrev']);
        $this->db->bind(':authors', $data['authors']);
        $this->db->bind(':affiliations', (isset($data['affiliation'])?$data['affiliation']:$data['affiliations']));
        $this->db->bind(':articleTitle', $data['articleTitle']);
        $this->db->bind(':year', $data['year']);
        $this->db->bind(':datePublished', $data['datePublished']);
        $this->db->bind(':volume', $data['volume']);
        $this->db->bind(':issue', $data['issue']);
        $this->db->bind(':startPage', $data['startPage']);
        $this->db->bind(':endPage', $data['endPage']);
        $this->db->bind(':articleAbstract', $data['articleAbstract']);
        $this->db->bind(':galleyLabel', $data['galleyLabel']);
        $this->db->bind(':authorEmail', $data['authorEmail']);
        $this->db->bind(':fileName', $data['fileName']);

        $val = "";
        if(key_exists('keywords',$data)){
            $val = $data['keywords'];
        }
        $this->db->bind(':keywords',$val);

        $this->db->execute();
    }

}