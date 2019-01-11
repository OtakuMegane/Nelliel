# Nelliel Changelog
## v0.9.16 (2019/01/10)
### Breaking
 - Database overhauls
 - JSON API changes

### Added
 - Default index.html
 - CAPTCHA/ReCAPTCHA support
 - Separate Users and Roles panels
 - ModMode class
 - Poster IDs

### Changed
 - JSON uses domain now
 - Update NellielTemplates library
 - Update phpDOMExtend
 - Update honeypot system
 - Update database version tracking
 - Update Users and Roles panels
 - Simplify login code
 - Update board settings and database
 - Update and debug filetypes
 - More conversion to domain
 - Tripcode salt changed to pepper and now autogens
 - Cleanup temporary globals in imgboard.php
 - Pass modmode status to js
 - Convert some things to the new null coalesce operator
 - Change abstract class naming
 - Various tweaks and fixes

### Fixed
 - wat pages
 - Hide thread not working
 - Return link incorrect
 - User update not working
 - Modmode errors
 - Expand/Collapse not working in dynamic render
 
### Removed
  - html5shiv
  - Some unused things from footer
  - Old Staff panel and output
  - The section parameter

## v0.9.15 (2018/12/15)
### Breaking
 - Database schema change
 - Raise minimum requirements and dev target version

### Added
 - Permissions panel
 - Delete option for boards, with interstitial warning page

### Changed
 - Now requires a minimum of PHP 7.0
 - Filetype settings now render dynamically from database
 - Permissions now render dynamically from database
 - Improve ContentID class
 - Update dispatch variable names
 - Fix up Reports panel
 - Update time format handling
 - Clear out some unused gettext attributes from templates
 - Assorted cleanup in code and templates
 
### Fixed
 - Gettext extractor
 - Some filetype entries
 - Ban stuff
 - User edit panel
 - Threads panel
 - Moderator Mode
 - Small issue in settings panels
 - Index navigation menu
 - Manage link being to main-panel when it should be login
 
### Removed
  - Ununsed isIgnored method from Session class

## v0.9.14 (2018/12/14)
### Breaking
 - Lots of things

### Added
 - INIParser class
 - Customizable templates, icon sets and styles and easier installation

### Changed
 - Truncate filenames over 255 characters
 - Change board_language to language and add site setting
 - Trim down permissions and update role editor
 - More dynamic category handling for filetypes

### Fixed
 - null id being sent to Domain by derp

## v0.9.13 (2018/12/12)
### Added
 - Templates panel
 - Ability to add more templates
 - Board locking

### Changed
 - Board class changed to Domain
 - Handle render instances inside Dmain class
 
### Fixed
 - A couple errors in `module_dispatch.php`
 - An error in HTML class output
 - Site settings not updating

## v0.9.12 (2018/12/6)
### Added
 - v0.2 of JSON API
 - `reply_to` column in posts table
 - JSONPost and JSONContent classes
 - Add board lock option (doesn't do anything yet)

### Changed
 - Restructuring of JSON API processing
 - Update documentation
 - Convert `post.php` to NewPost class
 
### Fixed
 - Pop-ups for post links

## v0.9.11.4 (2018/12/4)
### Added
 - v0.1 of JSON API
 - New site setting for custom output filenames

### Changed
 - Regen no longer calls the archive/prune functions
 - Archive and prune access simplified
 - Perm override so thread pruning works again
 - Move `loadArrayFromCache` to `CacheHandler` class
 
### Fixed
 - Not deleting when moved to archive

### Removed
 - Old `JSONAPI.php`

## v0.9.11.3 (2018/11/29)
### Added
 - Standard redirect function
 - New `FileTypes` class
 - Support for .ai, .ps, .eps, .xlsx, .pptx, .docx extensions

### Changed
 - Overhauls of HTML and CSS
 - Update Javascript functions to use content ids
 - Replace $base_content_id with post/thread specific IDs
 - Some database columns changed to DEFAULT NULL
 - Update filetype handling
 
### Fixed
 - Non-noko redirect going to home page
 - File formats showing in rules even when parent category is disabled

## v0.9.11.2 (2018/11/09)
### Breaking
 - Change file table to content table

### Added
 - Option to only allow sessions over secure connections

### Changed
 - Update schema markers
 - Permissions for regen options
 - Cleanup rules and posting limits
 - Update ban page
 - Add post borders to Burichan and Futaba styles
 - Various layout and rendering tweaks
 
### Fixed
 - WebM regex
 - Session check during posting
 - IP not being stored for posts

## v0.9.11.1 (2018/11/07)
### Changed
 - Cleanup of header and footer templates and rendering
 - Session default length changed to 2 hours
 - Cleanup rules and posting limits
 - Update ban page
 - Add post borders to Burichan and Futaba styles
 - Various layout and rendering tweaks
 
### Fixed
 - Newlines in `ERROR-REF.md`
 - Session age checks

## v0.9.11 (2018/11/05)
### Added
 - Option to delete boards from control panel
 - Translator class

### Changed
 - `language` directory renamed to `Language`
 - Update language handling
 - $dbh references renamed to $database
 - Moved Regen class
 - Check if language locale exists, make sure we switch to default if not
 - CSS tweaks
 - Split post header into separate lines
 - Clean up in rendering functions and templates
 - Clean up posting form HTML
 - Furthwer work on session handling
 - `Sessions.php` renamed to `Session.php`
 - Add parameter to Content classes to immediately load content from database when needed
 - Update `ERROR-REF.md`

### Fixed
 - Formatting in `DEV-GUIDE.md`
 - Missing locale handling
 - Parameters missing for PostData class

### Removed
 - nel_authorize()

## v0.9.10 (2018/10/26)
### Added
 - Additional permission checks at beginning of rendering
 - URLConstructor class
 - Setting for dynamic page rendering

### Changed
 - Clean up CHANGELOG.md a bit
 - Reports panel links open in modmode
 - Numerous permissions updates
 - Rename Panel classes to broader-purpose Admin classes
 - Improve permission checks
 - Reports and File Filters panels can now be board-specific
 - Prepare for changing create board to broader board management
 - Extract text button changed to link
 - Some updates to the Threads panel
 - Overhaul of dispatch
 - Update and improve Moderator Mode and its handling
 - Overhaul of Sessions class and session handling
 - Error ID now displayed on error page

### Fixed
 - Fix content not being deletable from Threads panel
 - Fix various bugs in new Admin classes
 - Fix content reporting

### Removed
 - general_dispatch.php

## v0.9.9.5 (2018/10/19)
## Added
 - ERROR-REF.md to document error codes and location
 - Add permissions to reports panel and output

### Changed
 - Update reports panel and report handling
 - Make checkbox for selecting OP post separate from thread checkbox
 - Update SQL tables
 - Update `DEV-GUIDE.md`
 - Clean up of error codes

### Removed
 - Dropped `url` field from posts database

## v0.9.9.4 (2018/10/18)
### Changed
 - Fix the broken setup routines
 - Add checks for main directory and board_files being writable
 - Assorted debugging
 - Improve installation directions
 - Update SmallPHPText library

## v0.9.9.3 (2018/10/16)
### Changed
 - Converted all the control panels to a more standard Panel class
 - Fixed permissions overwriting other roles
 - Moved login.php to the include directory
 - Most classes now use dependency injection

### Removed
 - admin directory

## v0.9.9.2 (2018/10/16)
### Changed
 - Changed some storage variables to statics
 - snacks.php converted into a class

## v0.9.9.1 (2018/10/15)
### Changed
 - Update FGSFDS handling
 - Clean up some code and namespacing
 - Rename more dbh to database
 - Most classes now use dependency injection

## v0.9.9 (2018/10/10)
### Added
 - Basic plugin API documentation

### Changed
 - Overhaul of plugin system
 - Documentation update
 - Cleaned up some singletons and random bits of code
 - Overhaul of authorization system
 - Debugging

## v0.9.8 (2018/10/15)
### Breaking
 - Bump minimum requirements to PHP 5.6.25

### Added
 - Mostly finished implementing a standard ContentID
 - ContentThread, ContentPost and ContentFile objects for handling most functions of threads, posts and files respectively
 - Add moveDirectory to FileHandler

### Changed
 - Update thread control panel
 - Update libraries
 - Moved most of the thread and post functions inside their respective objects
 - Refine some of the FileHandler functions
 - Lots of cleanup and debugging

### Fixed
 - Fix show/hide thread js

### Removed
 - second_last_post which was no longer used

## v0.9.7.1 (2018/08/19)
### Added
 - Add very basic reports system

### Changed
 - Improve ban functions

## v0.9.7 (2018/08/09)
### Added
 - Moderator Mode restored and updated

### Changed
 - Convert panel markup
 - Clean up perms
 - Various other cleanup and fixes
 
### Fixed
 - Fix javascript initializing
 - Fixed login page

## v0.9.6.19 (2018/07/13)
### Added
 - display-block class in CSS files
 - Checkbox to toggle posting as staff or normal when in modmode

### Changed
 - Dispatch converted to GET parameters
 - Ban page and ban application fixed up considerably

## v0.9.6.18 (2018/07/11)
### Added
 - Links for ban, delete, sticky and lock posts and files
 - Checkbox to toggle posting as staff or normal when in modmode

### Changed
 - Update phpDOMExtend library
 - Small improvement on filename filter
 - Convert more forms to links in management panels
 - Code and HTML cleanup
 - Update CSS
 
### Fixed
 - Fix numerous bugs

### Removed
 - Now-redundant output functions for footer and derp

## v0.9.6.17 (2018/07/06)
### Added
 - Mod Mode is in again
 - Can make posts as a staff member

### Changed
 - Updated perms
 - Overhaul of Authorization class
 - Debugging

## v0.9.6.16 (2018/07/05)
### Added
 - Regen.php

### Changed
 - Default admin check moved to setup
 - Collect autoloads together
 - Some cleanup of templates, language strings

### Removed
 - regen.php

## v0.9.6.15 (2018/07/05)
### Changed
 - Move setup files into classes
 - have setup check handle board as well if ID is set

## v0.9.6.14 (2018/07/05)
### Added
 - random_compat library

### Changed
 - nel_gen_salt now uses random_bytes
 - added PluginAPI.php to Nelliel namespace

## v0.9.6.13 (2018/07/05)
### Added
 - post/Postdata.php and post/PostDatabaseFunctions.php

### Changed
 - Fix up rules output
 - Make use of footer HTML element
 - Some rearrangement of files and namespaces
 - Move more post functions to classes
 - Other code cleanup

### Removed
 - post/database_functions.php
 - post/post_data.php

## v0.9.6.12 (2018/07/03)
### Added
 - Config setting to disable plugins

### Changed
 - Finish primary work on plugin API
 - Moved language handling to Language class

### Removed
 - language/language.php

## v0.9.6.11 (2018/07/03)
### Added
 - Filter hooks

### Changed
 - Updated example plugin

## v0.9.6.10 (2018/07/02)
### Breaking
 - Overhaul of plugin system (WIP)

### Added
 - Recursive file list function
 - Add caching for language files

### Changed
 - Path info uses SPL functions now
 - Default time limit on sessions increased
 - Improve error handler
 - writeFile now has better atomic function
 - Many tweaks and cleanup

### Removed
 - plugins.php, old plugin hooks

## v0.9.6.9 (2018/06/24)
### Added
 - Option to extract gettext strings to a .pot file

### Removed
 - Old lang.en-us.php file

## v0.9.6.8 (2018/06/24)
### Added
 - SmallPHPGettext library

### Changed
 - Converted language handling to gettext

# Nelliel Changelog
## v0.9.6.7 (2018/06/19)
### Added
 - Configurable defaults for new boards

### Changed
 - Improve file and post deletion functions
 - Updated database schema markers
 - nel_generate_salted_hash can now take an optional parameter for salt length
   
## v0.9.6.6 (2018/06/19)
### Changed
 - Preview generation moved to its own class
 - Set PDO to stringify fetches for now so we can use strict checking again
 - Handle the remaining likely case of filename collisions

### Removed
 - file_functions.php

### Fixed
 - No longer generate empty posts when a thread's post count is wrong
 - Add alt text field is now available for all files in the posting form
   
## v0.9.6.5 (2018/06/19)
### Breaking
 - Each post now gets a directory for files within the thread directory. This should avoid most filename collisions.

### Changed
 - Upload functions moved to new class

## v0.9.6.4 (2018/06/17)
### Added
 - More configurable parameters for file duplication checks
 - Now including changelog

### Changed
 - Improvements on preview generation code
 - Backup charset conversion for tripcode in case iconv is not present
 - Some work on tripcode generation
 - Small updates to README and DEV-GUIDE
 
###  Fixed
 - If tripcode is present but no name given, don't fill in Anonymous default name
 - A bit of derp in the standard tripcode gen

## v0.9.6.3 (2018/06/14)
### Added
 - Beginnings of JSON API

### Changed
 - `type`, `format` and `extension` columns in file table can no longer be null
 - Finish converting the insert data functions to the new prepared queries
 - InnoDB engine unavailable error now uses the standard error function
 - Store the file extension for preview files separately

## v0.9.6.2 (2018/06/14)
### Changed
 - Simplify the millisecond time function
 - Insert data in setup returns to individual queries

### Fixed
 - Generate internal caches after running setup and board creation

## v0.9.6.1 (2018/06/13)
### Added
 - Argon2I support for passwords when using PHP >7.2.0

### Changed
 - Move date check from NellielPDO (wtf) to initializations.php
 - MySQL encoding and charset changed to utf8mb4
 - Converting 4-byte UTF-8 to entities no longer needed for MySQL
 - Overhaul of hashing and password functions

### Removed
 - general_salt config
 - Dropping support for SHA256/SHA512 in passwords; can still be used for basic hashes

## v0.9.6 (2018/06/12)
### Breaking
 - Minimum requirements updated:
  - PHP 5.4.16
  - MySQL 5.5.52
  - MariaDB 5.5.52
  - PostgreSQL 9.2.18

### Added
 - SHA512 can be stored for files (off by default)
 - When available, SHA256 and SHA512 hashes are now displayed in file meta
 - Functions to show/hide threads or posts
 - Namespacing for javascript
 - Simple MP4 and WEBM embedding
 - MariaDB support

### Changed
 - Update to NellielTemplates 1.0.2
 - Javascript refactoring and tweaks
 - Split regen functions for site and board
 - Minor CSS and HTML tweaks
 - Copyright update

## v0.9.5.1 (2018/5/27)
### Added
 - File filter system
 - Basic unit test setup
 
### Fixed
 - Preview size calculations for certain cases

## v0.9.5 (2018/5/17)
### Breaking
 - Full conversion to multi-board. No backwards compatibility with pre-v0.9.5
 
### Added
 - Foreign key constraints for easier and cleaner deletion
 - Board data table
 - Board ID for board-specific input/output
 - Per-board settings
 - Very basic install instructions
 - Initial dev guidelines
 - Support for .ogv, .webm, .3gp, .cel, .kcf, .art file extensions
 
### Changed
 - Each board gets a thread, post and file table plus the archive versions
 - Redid preview generation code
 - Cleanup and sync themes
 - Cleanup header/footer
 - Just copy animated GIF for preview if smaller dimensions than preview limits
 - Redo dispatch
 - Better handling of Unicode/UTF-8
 - Some decoupling and conversion to classes
 - File format detection regexes updated
 - Filetype handling redone
 - Combine configs into config.php with minimal parameters
 - Setup updates
 
### Removed
 - The old $dataforce god-variable is gone
 - No more MD5 support for passwords

## Fixed
 - Many derps

## v0.9.4.12 (2018/01/17)
### Breaking
 - Begin conversion to multi-board
 - Board id and directory updated
 - Restructure of files
 - Database now uses binary type for hashes and IPs
 
### Changed
 - Work on rendering code

## v0.9.4.11 (2018/01/11)
### Changed
 - Javascript updates


## v0.9.4.10 (2018/01/11)
### Changed
 - Change some settings defaults
 - Update README
 - Javascript updates
 
### Fixed
 - Various CSS fixes


## v0.9.4.9 (2018/01/04)
### Added
 - Alt text can now be added when uploading files

### Changed
 - Various tweaks and fixes

## v0.9.4.8 (2018/01/03)
### Changed
 - Expand thread panel
 - Work on removing $dataforce god-variable

## v0.9.4.7 (2018/01/03)
### Added
Add unicode to entities conversion functions

### Changed
 - Rearrange some of imgboard.php

### Fixed
 - Got post quotes and links working again
 - HTML5 fixes

## v0.9.4.6 (2018/01/01)
### Changed
 - File handling moved to FileHandler class
 - Bits of cleanup and fixing
 - Improve handling of FGSFDS field input
 - Improve login throttling

## v0.9.4.5 (2018/01/01)
### Changed
 - Improvements on the dispatch system
 - Update post/thread archiving system
 - Update error handling system
 - Debugging and cleanup

## v0.9.4.4 (2017/12/28)
### Changed
 - Cleanup

## v0.9.4.3 (2017/12/26)
### Changed
 - Redo ban system

## v0.9.4.2 (2017/12/16)
### Added
 - Autoloading
 - Add NellielTemplates library
 - Add phpDOMExtend library

### Changed
 - Fix up sessions
 - Update login system
 - Update some paths
 - Got a couple TODOs done
 - Begin converting templating to DOM-based system
 - Set up i18n system for language handling
 - Improve internal caching code

## v0.9.4.1 (2017/12/16)
### Changed
 - Prepare for PSR-4 complaince
 - Update posting form input
 - Database overhaul and new RDBMS support
 - Query updates
  - Thread and file handling changes and fixes
  
### Fixed
 - Fix regen
 - Fix bans being applied

### Removed
 - Retire the old rendering system

## v0.9.4 (2017/10/15)
### Breaking
 - Requirements update:
  - PHP 5.3.3 minimum

### Added
 - Basic staff structure and permissions

### Changed
 - Many many minor fixes and tweaks
 - Update code for handling tripcodes
 - Update config system
 - Language updates