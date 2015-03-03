--
-- Notes:
--
-- * Don't forget to uncomment lines under `Required by PostgreSQL x` to
--   create dblink function, If you're running PostgreSQL 8.x, you can find
--   `dblink.sql` in package 'postgresql-contrib'.
--
-- * of course you should replace `xxx` in below sample SQL command by the
--   real SQL username/password/database name.
--
-- * it's better to use a SQL user who has read-only permission on the
--   source database. On iRedMail server, please use `vmail` user, do NOT
--   use `vmailadmin` or `postgres` user.

--
-- Known issue on iRedMail server:
--
-- * Column `mailbox.created` stores timestamp when this account was created,
--   if the timestamp contains timezone info (e.g. 2015-02-28 22:31:16.562376),
--   this plugin cannot display any contact (causes SQL syntax error).
--   Changing the timestamp to 2015-02-28 22:31:16 (without time zone info)
--   fixes this issue.

--
-- Required by PostgreSQL 8.x (RHEL/CentOS 6)
--
-- CREATE LANGUAGE plpgsql;                                                           
-- \i /usr/share/pgsql/contrib/dblink.sql;

--
-- Required by PostgreSQL 9.x (RHEL/CentOS 7, Debian 7, Ubuntu 14.04, ...)
--
-- CREATE EXTENSION dblink;

CREATE VIEW global_addressbook AS
    SELECT * FROM dblink('host=127.0.0.1 port=5432 user=vmail password=xxx dbname=vmail', 'SELECT extract(epoch FROM created), name, username, domain FROM mailbox WHERE active=1')
    AS global_addressbook ("ID" BIGINT, name VARCHAR(255), email VARCHAR(255), domain VARCHAR(255));

ALTER TABLE global_addressbook OWNER TO roundcube;
