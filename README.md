# RoundCube SQL Multi-Contacts
Roundcube Plugin to create an Address Book from list of users in the SQL View.
Currently Natively Supporting:
+ iRedMail [Aliases Supported]
+ [anything you create a MySQL View for]

By Default ALL Books are DISABLED.
You will have to enable them in the config after you enable the plugin!

#Installation#

1.  To Install, extract this archive and copy the folder
    `wdgrc_sql_contacts` into `roundcube/plugins/`

2.  Choose one of the SQL Scripts from `./SQL/`, dependant on your Mail System
    and whether or not you wish to have Alias Support, `[%name%+alias.sql]`
    or you would like just to have the primary emails, `[%name%.sql]`
    and run it on your `MySQL/MariaDB` Server as `root`, for example from `PHPMyAdmin`

3.  And then add `wdgrc_sql_contacts` to your `roundcube/config/config.inc.php`
    so it looks like: `$config['plugins'] = array(..., 'wdgrc_sql_contacts');`

4.  Rename `roundcube/plugins/wdgrc_sql_contacts/config.inc.php.dist` to
    `config.inc.php` and fill in the configuration file.


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
