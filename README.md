# CSV to OJS XML Import for OJS 3.1.1-4
This application will convert a csv file into the ojs xml native import file.
The XSD is here:
https://github.com/pkp/ojs/blob/ojs-stable-3_1_0/plugins/importexport/native/native.xsd
and
https://github.com/pkp/pkp-lib/blob/master/plugins/importexport/native/pkp-native.xsd


 The CSV must be in the format of:
 issueTitle,sectionTitle,sectionAbbrev,authors,affiliation,articleTitle,year,datePublished,volume,issue,startPage,endPage,articleAbstract,galleyLabel,authorEmail,fileName,keywords,cover_image_filename,cover_image_alt_text

 You can have multiple authors in the "authors" field by separating them with a semi-colon.
 Also, use a comma to separating first and last names.
 Example:
 Smith, John;Johnson, Jane ...
 
 The same rules for authors also apply to affiliation. Separate different affiliations with a semi-colon. 
 If there is only 1 affiliation and multiple authors that 1 affiliation will be applied to all authors.

Note: This is NOT a comprehensive csv to ojs xml conversion and many fields are left out.


## How to Use
1. Setup the variables in docroot/config.inc.php file.
* The $PDF_URL variable is the URL where the PDF files are located the filename in the csv field is appended to the value of the $PDF_URL. So the PDF files will need to be web accessible.
2. Place CSV file(s) in docroot/csv/abstracts directory
* You can place multiple csv files in the directory however do not split a single issue across multiple csv files. But you can have multiple issues in a single csv file
3. New for 2019: If you have cover images place them in the docroot/abstracts/issue_cover_images directory
4. Run php generateXml.php
5. the xml file(s) will be output in the docroot/output directory

# Users to XML Import

This will convert a CSV file into ojs 3 users xml import.

## How to Use
1. Enter in user data into the examples\users.csv
2. Save the file to  docroot\csv\users\\*.csv
3. Run "php generateUsersXml.php" from the command line
