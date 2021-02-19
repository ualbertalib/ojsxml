# CSV to OJS XML Import for OJS 3.2.1
This application will convert a csv file into the OJS XML native import file.
The XSD is included with this project in the `docroot/output` directory.


 The CSV must be in the format of:
 issueTitle,sectionTitle,sectionAbbrev,authors,affiliation,DOI,articleTitle,year,datePublished,volume,issue,startPage,endPage,articleAbstract,galleyLabel,authorEmail,fileName,keywords,cover_image_filename,cover_image_alt_text

 You can have multiple authors in the "authors" field by separating them with a semi-colon.
 Also, use a comma to separating first and last names.
 Example:
 Smith, John;Johnson, Jane ...
 
 The same rules for authors also apply to affiliation. Separate different affiliations with a semi-colon. 
 If there is only 1 affiliation and multiple authors that 1 affiliation will be applied to all authors.

The following fields are optional and can be left empty:
DOI, volume, issue, subtitle, keywords, affiliation, cover image (both cover_image_filename and cover_image_alt_text must be included or omitted),

Note: This is NOT a comprehensive CSV to OJS XML conversion, and many fields are left out.

## Known Issues

* Each issue export XML file can contain __only one issue__. This is a current limitation with the OJS 3.2 issue importer. Multiple issues/XML file can lead to database corruption.
* The journal's current issue must be manually set upon import completion. This conversion tool does not indicate which issue should be the current one.

## How to Use

From the CLI `--help` command:
```bash
Script to convert issue or user CSV data to OJS XML.
Usage: issues|users <ojs_username> <source_directory> <destination_directory>
NB: issues source directory must include "issue_cover_images" and "article_galleys" directory
```

Example:
```bash
php csvToXmlConverter issues ./input_directory ./output_directory
```

### Issue CSVs
1. Set up the variables in the config.ini file.
2. Place CSV file(s) in a single directory (optionally `docroot/csv/abstracts`, which has already been created)
   * The `abstracts` input directory must contain an `article_galleys` and `issue_cover_images` directory (both of which exist within `docroot/csv/abstracts`)
   * You can place multiple csv files in the directory however do not split a single issue across multiple csv files, but you can have multiple issues in a single csv file.
3. Place all PDF galleys in the `article_galleys` directory
4. If you have cover images place them in the `issue_cover_images` directory
4. Run `php csvToXmlConverter.php issues ojs_username ./docroot/csv/abstracts ./docroot/output`
5. The XML file(s) will be output in the specified output directory (`docroot/output` directory in this case)

### User CSVs
1. Set up the variables in the config.ini file.
2. Place CSV file(s) in a single directory (optionally `docroot/csv/users`)
3. Run `php csvToXmlConverter.php users ojs_username ./docroot/csv/users ./docroot/output`
4. The XML file(s) will be output in the specified output directory (`docroot/output` directory in this case)
