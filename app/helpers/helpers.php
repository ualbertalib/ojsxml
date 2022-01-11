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
    if ($role == "Journal manager") {
        return $role;
    } else if ($role=='Section editor'){
        return $role;
    } else if ($role=='Reviewer'){
        return $role;
    } else if ($role == 'Author') {
        return $role;
    } else{
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
            $cleanedUpRow = removeZeroWidthSpaces($row);
            if (empty($cleanedUpRow[0])) continue;

            if (!$header) {
                $header = $cleanedUpRow;
            } else {
                $cleanedUpRow = array_combine($header, $cleanedUpRow);
                formatDateInRow($cleanedUpRow);
                formatKeywords($cleanedUpRow);
                cleanAbstracts($cleanedUpRow);
                $data[] = $cleanedUpRow;
            }
        }

        fclose($handle);
    }
    return $data;
}

/**
 * Removes zero width space characters that affect the rows in the CSV files
 * From: https://gist.github.com/ahmadazimi/b1f1b8f626d73728f7aa
 *
 * @param $text
 * @return string|string[]|null
 */
function removeZeroWidthSpaces($text) {
    return preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $text );
}

function formatDateInRow(&$dataArray) {
    if (empty($dataArray['datePublished'])) return;

    $dirtyDate = $dataArray['datePublished'];
    $dateTime = DateTime::createFromFormat(\OJSXml\Config::get("dateFormat"), $dirtyDate);
    $sanitizedDate = $dateTime->format("Y-m-d");
    $dataArray['datePublished'] = $sanitizedDate;
}

function formatKeywords(&$dataArray) {
    if (empty($dataArray['keywords'])) return;
    $sanitizedKeywords = str_replace(',',';',$dataArray['keywords']);
    $dataArray['keywords'] = $sanitizedKeywords;
}

function cleanAbstracts(&$dataArray) {
    if (empty($dataArray['articleAbstract'])) return;

    $abstract =& $dataArray['articleAbstract'];

    $openTagCount = substr_count($abstract, '<p>');
    $closeTagCount = substr_count($abstract, '</p>');

    if ($openTagCount == $closeTagCount) {
        return;
    }

    // 1) Always ensure tags are properly formatted
    str_replace($abstract,'<p>','<P>');
    str_replace($abstract, '</p>', '</P>');


    $newAbstract = '';
    // 2) Handle extra open tags
    if ($openTagCount > $closeTagCount) {
        $paragraphs = explode('<p>', $abstract);

        foreach ($paragraphs as $paragraph) {
            if (empty($paragraph)) {continue;}
            $paragraph = trim($paragraph);

            $paragraph = '<p>' . $paragraph;

            if (strpos($paragraph, '</p>') === false) {
                $paragraph = $paragraph . '</p>';
            }

            $newAbstract = $newAbstract . $paragraph;
        }

    }
    // 3 Handle extra close tags
    else {
        $paragraphs = explode('</p>', $abstract);

        foreach ($paragraphs as $paragraph) {
            if (empty($paragraph)) {continue;}
            $paragraph = trim($paragraph);

            if (strpos($paragraph, '<p>') === false) {
                $paragraph = '<p>' . $paragraph;
            }

            $paragraph = $paragraph . '</p>';

            $newAbstract = $newAbstract . $paragraph;
        }

    }
    $abstract = $newAbstract;
}

/**
 * Prepends 0s to file name so imports will happen sequentially (i.e. 01, 02, 03 instead of 1, 11, 2, etc)
 *
 * @param int $totalItemCount
 * @param int $iteration
 * @return string
 */
function formatOutputFileNumber($totalItemCount, $iteration) {
    $totalDigitCount = 0;

    if ($totalItemCount > 999) {
        $totalDigitCount = 4;
    } else if ($totalItemCount < 999 && $totalItemCount > 99) {
        $totalDigitCount = 3;
    } else if ($totalItemCount < 99 && $totalItemCount > 9) {
        $totalDigitCount = 2;
    } else {
        $totalDigitCount = 1;
    }

    $outputPrefix = '';
    if ($iteration < 1000 && $iteration > 99) {
        if ($totalDigitCount > 3) {
            $outputPrefix = '0';
        }
    } else if ($iteration < 100 && $iteration > 9) {
        if ($totalDigitCount > 3) {
            $outputPrefix = '00';
        } else if ($totalDigitCount > 2) {
            $outputPrefix = '0';
        }
    } else {
        if ($totalDigitCount > 3) {
            $outputPrefix = '000';
        } else if ($totalDigitCount > 2) {
            $outputPrefix = '00';
        } else if ($totalDigitCount > 1) {
            $outputPrefix = '0';
        }
    }

    return $outputPrefix . (string) $iteration;

}
