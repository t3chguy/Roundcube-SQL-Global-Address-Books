CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`localhost`
    SQL SECURITY DEFINER
VIEW `global_addressbook` AS
    SELECT
        SUBSTR((UNIX_TIMESTAMP(`vmail`.`mailbox`.`passwordlastchange`) +
                UNIX_TIMESTAMP(`vmail`.`mailbox`.`modified`)) -
                UNIX_TIMESTAMP(`vmail`.`mailbox`.`created`),
            -(8)) AS `ID`,
        `vmail`.`mailbox`.`name` AS `name`,
        `vmail`.`mailbox`.`username` AS `email`,
        `vmail`.`mailbox`.`domain` AS `domain`
    FROM
        `vmail`.`mailbox`;