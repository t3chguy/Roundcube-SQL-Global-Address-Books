# RoundCube SQL Multi-Contacts
Roundcube Plugin to create an Address Book from list of users in the SQL View.
Currently Natively Supporting:
+ iRedMail
+ [anything you create a MySQL View for]

By Default ALL Books are DISABLED.
You will have to enable them after you enable the plugin!

***
#Installation#

1.  To Install, extract this archive and copy the folder
    `wdgrc_sql_contacts` into roundcube/plugins/

2.  Choose one of the SQL Scripts from ./SQL/, dependant on your Mail System
    and whether or not you wish to have Alias Support, [%name%+alias.sql]
    or you would like just to have the primary emails, [%name%.sql]
    and run it on your MySQL/MariaDB Server as root, for example from PHPMyAdmin

3.  And then add `wdgrc_sql_contacts` to your roundcube/config/config.inc.php
    $config['plugins'] = array(..., 'wdgrc_sql_contacts');

4.  Rename roundcube/plugins/wdgrc_sql_contacts/config.inc.php.dist to
    config.inc.php and fill in the configuration file.
***

The Configuration File has been annotated and should prove easy to understand.
Feel free to contact me directly at postmaster@webdevguru.co.uk if you have any queries or requests!
