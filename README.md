# CSV to OJS XML Import for OJS 3.5
This application will convert a CSV file into the OJS XML native import file.
The XSD is included with this project in the `docroot/output` directory.
Sample CSV files for both users and issues are included in the `examples`
 directory.

Note: This is NOT a comprehensive CSV to OJS XML conversion, and many fields are left out.

## Known Issues

* The converter defaults to one issue per XML file. This can be adjusted with `issues_per_file` in `config.ini` after testing against the target journal.
* The journal's current issue must be manually set upon import completion. This conversion tool does not indicate which issue should be the current one.
* User-group definitions are journal-specific. User conversion therefore requires an OJS 3.5 user export from the target journal.
* CSV files must be UTF-8 encoded.

## How to Use

From the CLI `--help` command:
```bash
Script to convert issue or user CSV data to OJS XML.
Usage: issues <ojs_username> <source_directory> <destination_directory>
       users|users:test <ojs_username> <source_directory> <destination_directory> [user_groups_xml]
NB: issues source directory must include "issue_cover_images" and "article_galleys" directory
users:test appends "test" to user email addresses
```

Example:
```bash
php csvToXmlConverter.php issues username ./input_directory ./output_directory
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

#### Update May 2024
Added extra fields for basic multilingual support. The extra fields are: locale_2,issueTitle_2,sectionTitle_2,articleTitle_2,articleAbstract_2 
locale_2 should use the same format (ie fr_CA) that ojs uses for it's Locale field.


#### Instructions

1. Install PHP with the `dom`, `xmlwriter`, and `sqlite3` extensions, then set up `config.ini`.
2. Place CSV file(s) in a single directory (optionally `docroot/csv/abstracts`, which has already been created)
   * The `abstracts` input directory must contain an `article_galleys` and `issue_cover_images` directory (both of which exist within `docroot/csv/abstracts`)
   * You can place multiple csv files in the directory however do not split a single issue across multiple csv files, but you can have multiple issues in a single csv file.
3. Place all PDF galleys in the `article_galleys` directory
4. If you have cover images place them in the `issue_cover_images` directory
4. Run `php csvToXmlConverter.php issues ojs_username ./docroot/csv/abstracts ./docroot/output`.
5. The XML files will be written to the specified output directory.

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

1. Export users from the target OJS 3.5 journal:
   1. Sign in as a Journal Manager or Administrator.
   2. Open the journal dashboard and go to **Tools > Import/Export**.
   3. Select **Users XML Plugin**.
   4. Choose **Export All**, or select one or more users and export them.
   5. Download the resulting XML file. It contains the journal-specific `<user_groups>` definitions required for import.
2. Place CSV files in one directory, such as `docroot/csv/users`.
3. Pass the complete OJS user export directly to the converter:

   ```bash
   php csvToXmlConverter.php users ojs_username ./docroot/csv/users ./docroot/output ./user_groups.xml
   ```

   The converter extracts `<user_groups>` and does not copy the exported `<users>` into its output. You may instead set `user_groups_file` in `config.ini` and omit the final argument.
4. Import the generated XML from `docroot/output` into the same target journal.

Each CSV role must exactly match a localized `<name>` in the supplied user export. Unknown roles stop conversion with an error instead of silently assigning `Reader`. OJS 3.5 user-group associations, including the required `masthead` value, are generated automatically.

OJS user exports can contain names, email addresses, and password hashes. Keep the source export outside this repository, restrict access to it, and delete it when it is no longer needed.

## Validation

The bundled schemas are pinned to official OJS 3.5 source revisions; see `docroot/output/SCHEMA_VERSION.md`. Run the end-to-end schema tests with:

```bash
php tests/run.php
```

Before production use, import the generated files into a disposable copy of the target OJS 3.5 journal and verify metadata, roles, covers, and galleys.
