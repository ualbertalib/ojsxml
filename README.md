# CSV to OJS XML Import for OJS 3.3.0
This application will convert a CSV file into the OJS XML native import file.
The XSD is included with this project in the `docroot/output` directory.
Sample CSV files for both users and issues are included in the `examples`
 directory.

Note: This is NOT a comprehensive CSV to OJS XML conversion, and many fields are left out.

## Known Issues

* Each issue export XML file can contain __only one issue__. This is a current limitation with the OJS 3.3 issue importer. Multiple issues/XML file can lead to database corruption.
* The journal's current issue must be manually set upon import completion. This conversion tool does not indicate which issue should be the current one.
* The `user_groups` section of the User XML must be manually added and is journal specific. This can be found at the top of a User export XML from the current journal (see below for example).
* CSV files should be UTF8 encoded or non-ASCII characters will not appear correctly

## How to Use

From the CLI `--help` command:
```bash
Script to convert issue or user CSV data to OJS XML.
Usage: issues|users|users:test <ojs_username> <source_directory> <destination_directory>
NB: issues source directory must include "issue_cover_images" and "article_galleys" directory
user:test appends "test" to user email addresses
```

Example:
```bash
php csvToXmlConverter issues username ./input_directory ./output_directory
```

### Issue CSVs

#### Description
The CSV must be in the format of:
issueTitle,sectionTitle,sectionAbbrev,authors,affiliation,DOI,articleTitle,year,datePublished,volume,issue,startPage,endPage,articleAbstract,galleyLabel,authorEmail,fileName,keywords,citations,cover_image_filename,cover_image_alt_text,licenseUrl,copyrightHolder,copyrightYear,locale_2,issueTitle_2,sectionTitle_2,articleTitle_2,articleAbstract_2

You can have multiple authors in the "authors" field by separating them with a semi-colon.
Also, use a comma to separating first and last names.
Example:
Smith, John;Johnson, Jane ...

The same rules for authors also apply to affiliation. Separate different affiliations with a semi-colon.
If there is only 1 affiliation and multiple authors that 1 affiliation will be applied to all authors.

citations can be seperated with a new line.

The following fields are optional and can be left empty:
DOI, volume, issue, subtitle, keywords, citations, affiliation, cover image (both cover_image_filename and cover_image_alt_text must be included or omitted),licenseUrl,copyrightHolder,copyrightYear,locale_2,issueTitle_2,sectionTitle_2,articleTitle_2,articleAbstract_2

####Update May 2024
Added extra fields for basic multilingual support. The extra fields are: locale_2,issueTitle_2,sectionTitle_2,articleTitle_2,articleAbstract_2 
locale_2 should use the same format (ie fr_CA) that ojs uses for it's Locale field.


#### Instructions

1. Set up the variables in the config.ini file.
2. Place CSV file(s) in a single directory (optionally `docroot/csv/abstracts`, which has already been created)
   * The `abstracts` input directory must contain an `article_galleys` and `issue_cover_images` directory (both of which exist within `docroot/csv/abstracts`)
   * You can place multiple csv files in the directory however do not split a single issue across multiple csv files, but you can have multiple issues in a single csv file.
3. Place all PDF galleys in the `article_galleys` directory
4. If you have cover images place them in the `issue_cover_images` directory
4. Run `php csvToXmlConverter.php issues ojs_username ./docroot/csv/abstracts ./docroot/output`
5. The XML file(s) will be output in the specified output directory (`docroot/output` directory in this case)

### User CSVs

#### Description

The CSV must be in the format of:
firstname,lastname,email,affiliation,country,username,tempPassword,role1,role2,role3,role4,reviewInterests

Review interests should be separated by a comma
Example: interest one, interest two ...

The following fields are optional and can be left empty:
lastname, affiliation, country, password, role1, role2, role3, role4, reviewInterests.

NB: If a temporary password is not supplied, a new password will be created and the user will be notified by email.

#### Instructions

1. Set up the variables in the config.ini file.
2. Place CSV file(s) in a single directory (optionally `docroot/csv/users`)
3. Run `php csvToXmlConverter.php users ojs_username ./docroot/csv/users ./docroot/output`
4. The XML file(s) will be output in the specified output directory (`docroot/output` directory in this case)
5. Add the `user_groups` section from a User export from the journal to the newly created XML file(s).

The `user_groups` section of the XML is specific to each journal and should therefore be taken from a sample user export from the intended journal. Any role added in the import CSV must match the `name` tag for the given user group or it will default to `Reader`.

Current valid roles include:
- Journal manager
- Section editor
- Reviewer
- Author
- Reader

The user export XML should be in the following format:

```xml
<?xml version="1.0"?>
<PKPUsers xmlns="http://pkp.sfu.ca" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://pkp.sfu.ca pkp-users.xsd">
  <user_groups xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:schemaLocation="http://pkp.sfu.ca pkp-users.xsd">
    [... add journal specific user groups here]
  </user_groups>
  <users>
    [...generated by conversion tool]
  </users>
</PKPUsers>
```

At least one `user_group` must be included inside the `user_groups` tag. The `user_group` XML will look something like this:

```xml
<user_group>
  <role_id>1048576</role_id>
  <context_id>1</context_id>
  <is_default>true</is_default>
  <show_title>false</show_title>
  <permit_self_registration>true</permit_self_registration>
  <permit_metadata_edit>false</permit_metadata_edit>
  <name locale="en_US">Reader</name>
  <abbrev locale="en_US">Read</abbrev>
  <stage_assignments/>
</user_group>
```
