--
-- Notes:
--
-- * if you're running RHEL/CentOS 6 or 7, don't forget to uncomment lines
--   under `Required by RHEL/CentOS X` to create dblink function.
--
-- * of course you should replace `xxx` in below sample SQL command by the
--   real SQL username/password/database name.
--
-- * it's better to use SQL user `vmail` which has read-only permission to
--   `vmail` database. Don't use `vmailadmin` or `postgres` user.

--
-- Known issue on iRedMail server:
--
-- * Column `mailbox.created` stores timestamp when this account was created,
--   if the timestamp contains timezone info (e.g. 2015-02-28 22:31:16.562376),
--   this plugin cannot display any contact (causes SQL syntax error).
--   Changing the timestamp to 2015-02-28 22:31:16 (without time zone info)
--   fixes this issue.

--
-- Required by RHEL/CentOS 6.
--
-- CREATE LANGUAGE plpgsql;                                                           
-- \i /usr/share/pgsql/contrib/dblink.sql;

--
-- Required by RHEL/CentOS 7
--
-- CREATE EXTENSION dblink;

CREATE VIEW global_addressbook AS
    SELECT * FROM dblink('host=127.0.0.1 port=5432 user=vmail password=xxx dbname=vmail', 'SELECT extract(epoch FROM created), name, username, domain FROM mailbox WHERE active=1')
    AS global_addressbook ("ID" BIGINT, name VARCHAR(255), email VARCHAR(255), domain VARCHAR(255));

ALTER TABLE global_addressbook OWNER TO roundcube;
