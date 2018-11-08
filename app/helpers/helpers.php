<?php
/**
 * output debug
 * @param $s
 */
function pre($s){

    echo "<pre>";
    print_r($s);
    echo "</pre>";
}

function semiColonFix($keywords){
    $keywords = str_replace("; ", ";",$keywords);
    $keywords = str_replace(";", "; ", $keywords);

    return $keywords;
}

function parseSemiColon($keywords){
    $keywords = semiColonFix($keywords);

    return explode(";",$keywords);
}

function xmlFormat($string_from_hell){
    $string_from_hell  = htmlspecialchars($string_from_hell, ENT_QUOTES, "UTF-8");
    $string_from_hell = str_replace('—','&#8212;',$string_from_hell);
    $string_from_hell = str_replace('’','&#8217;',$string_from_hell);
    $string_from_hell = str_replace('‘','&#8216;',$string_from_hell);
    $string_from_hell = str_replace('“','&#8220;',$string_from_hell);
    $string_from_hell = str_replace('”','&#8221;',$string_from_hell);
    return $string_from_hell;
}

function getFiletype($ext){
    $ext = strtolower($ext);
    if ($ext == 'pdf') {
        $type = "application/pdf";
    }elseif($ext == 'html' || $ext == 'htm'){
        $type='plain/html';
    }elseif($ext == 'docx' || $ext == 'doc'){
        $type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }else{
        $type = 'plain/text';
    }
    return $type;
}

function userGroupRef($role){
    $role = trim($role);
    if ($role == "Journal Manager") {
        return $role;
    } else if ($role=='Section Editor'){
        return $role;
    } else if ($role=='Reviewer'){
        return $role;
    }else{
        return "Reader";
    }

}

function fileExtensionAppender($fileName, $desiredExtention='pdf'){
    $file = explode(".",$fileName);
    if (! isset($file[1])){
        return $fileName . "." . $desiredExtention;
    }
     return $fileName;
}


/**
 * https://gist.github.com/jaywilliams/385876
 * Convert a comma separated file into an associated array.
 * The first row should contain the array keys.
 *
 * Example:
 *
 * @param string $filename Path to the CSV file
 * @param string $delimiter The separator used in the file
 * @return array
 * @link http://gist.github.com/385876
 * @author Jay Williams <http://myd3.com/>
 * @copyright Copyright (c) 2010, Jay Williams
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
function csv_to_array($filename='', $delimiter=',')
{
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else{
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}