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

function get_file_extension($file_name) {
    return substr(strrchr($file_name,'.'),1);
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
        $type='text/html';
    }elseif($ext == 'docx' || $ext == 'doc'){
        $type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }else{
        $type = 'plain/text';
    }
    return $type;
}
/**
 * Returns the mime type based on the filename As per: https://stackoverflow.com/questions/35299457/getting-mime-type-from-file-name-in-php 
 * @param type $filename
 * @return string 
 */
function get_mime_type($filename) {
    $idx = explode( '.', $filename );
    $count_explode = count($idx);
    $idx = strtolower($idx[$count_explode-1]);

    $mimet = array( 
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',


        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    if (isset( $mimet[$idx] )) {
     return $mimet[$idx];
    } else {
     return 'application/octet-stream';
    }
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
    if(!file_exists($filename) || !is_readable($filename) || is_dir($filename))
        return false;

    $header = null;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== false)
    {
        while (($row = fgetcsv($handle, 10000, $delimiter,'"')) !== FALSE)
        {

            if(!$header){
                $header = $row;
            }else{
                $data[] = array_combine($header, $row);

            }
        }

        fclose($handle);
    }
    return $data;
}