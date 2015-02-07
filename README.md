# RoundCube SQL Global Address Books
Roundcube Plugin to create an Address Book from list of users in the SQL View.
Currently Natively Supporting:
+ iRedMail [Aliases Supported]
+ [anything you create a MySQL View for]

By Default The `DomainBook` is enabled for all users. All other books will have to be configured prior to use.

#License#

This software adheres to the MIT License, please see LICENSE File!

#Installation#

1.  To Install, extract this archive and copy the folder
    `sql_global_addressbooks` into `roundcube/plugins/`

2.  Choose one of the SQL Scripts from `./SQL/`, dependant on your Mail System
    and whether or not you wish to have Alias Support, `[%name%+alias.sql]`
    or you would like just to have the primary emails, `[%name%.sql]`
    and run it on your `MySQL/MariaDB` Server as `root`, for example from `PHPMyAdmin`

3.  And then add `sql_global_addressbooks` to your `roundcube/config/config.inc.php` so it looks like: `$config['plugins'] = array(..., 'sql_global_addressbooks');`

4.  Rename `roundcube/plugins/sql_global_addressbooks/config.inc.php.dist` to
    `config.inc.php` and fill in the configuration file.


#Book Types#

###Domain Book###
This Book Type shows all users which are in the same Domain Realm as the Authenticated User.

###Global Book###
This Book Type shows all users, with the exception of the blacklisted ones.

###Support Book###
This Book Type shows a specific set of domains, to all users except those which are part of any of the domains in the set.


#Security#

This plugin is considered secure to use for the following reasons:
+ Security through Obscurity - The plugin uses a MySQL View instead of directly accessing the SQL Schema+Table because it means it is given no access to such things as User Hashes [Passwords] but it also means that the whole Plugin remains the same and the View Defines how it interacts with the Existing DB.
+ Read Only MySQL View - The View containing Functions and SubQueries [Alias Support] means that the View is Read Only which is another layer of security between your Data and any Potential Damaging Code.
+ It uses the Internal Roundcube Address Book classes so if any bugs exist in them they will be patched accordingly, hopefully without breaking the functionality of this plugin.
+ This plugin features no accessible endpoints, it can be Modelled as a processing node, as it does not address the client directly. Ever. All the communication between DB -> Itself -> Client is managed and administered by the RoundCube Plugin Hooks+API.

#####If you do not trust me, just look through the source code, it should be pretty straight forward and you can then rest assured!#####

#Support#

The Configuration File has been annotated and should prove easy to understand.
Feel free to contact me directly at postmaster@webdevguru.co.uk if you have any queries or requests!
