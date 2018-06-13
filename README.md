# Nelliel Imageboard #

A lightweight and flexible imageboard based on Futallaby.

Nelliel was originally a modified version of Futallaby made to host Nigrachan. The software had good potential and was split off to independent development. While retaining an interface similar to Futallaby it has been rewritten and expanded into a unique codebase.

**Nelliel should be considered early beta software. It is still buggy, incomplete and will probably have further breaking changes. You're free to try it out but don't use it for anything important yet.**

## Minimum Requirements ##
These are only the bare minimum requirements for Nelliel to function. **It is strongly recommended to use the latest software versions available.**

Required:
- PHP 5.4.16+
- PDO with MySQL, SQLite or PostgreSQL support
- MySQL 5.5.52+, MariaDB 5.5.52+, SQLite 3.6.20+ or PostgreSQL 9.2.18+
- iconv
- libxml 2.6+
- PHP GD
- PHP DOM

In most cases these requirements will be fulfilled by a standard PHP install and everything will run out of the box. In the case a component is not present it will usually be available in the default system repos and can be easily installed.

## Optional Requirements ##
These are optional things that Nelliel or one of its libraries can utilize for extra features or performance increases. They are not required and the software will work fine without them:
- ImageMagick
- Imagick (PECL extension)
- mbstring

## Installation ##
Just a basic guide, subject to likely changes and improvements.

1. If using MySQL or PostgreSQL you need a database and a user that has CREATE, ALTER and DROP permissions for that database. If not sure how to set this up, check with your host. For SQLite you just need the path to where you wish the database file to be stored (this must not be a web-accessible location!).
2. Copy files to the directory Nelliel will be run from. Be sure PHP is able to read and write to this directory and any files in the Nelliel install.
3. Go to the `configuration` directory and rename `config.php.example` to `config.php` then edit the settings as needed.
4. Access `imgboard.php` in a browser and give it a moment to run the install routines.
5. If errors are displayed, address those and try again. If installation is successful you should be redirected to the default home page. From there you can log in to create boards and further configure the script.

## Plugin API ##
Nelliel currently has a very basic API for making plugins in the works. This will eventually allow extension of the software without having to worry about mods that can be easily broken during updates or having to mess with core code. **Do not use the plugin system yet! It is still undocumented and changing!**

## License ##
Nelliel is released under the Modified BSD License. This can be viewed in LICENSE.txt or the imageboard's About Nelliel page.