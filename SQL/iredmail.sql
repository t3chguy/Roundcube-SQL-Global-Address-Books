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
        SUBSTRING_INDEX(SUBSTRING_INDEX(`vmail`.`mailbox`.`name`, ' ', 1),
                ' ',
                -(1)) AS `firstname`,
        SUBSTRING_INDEX(SUBSTRING_INDEX(`vmail`.`mailbox`.`name`, ' ', -(1)),
                ' ',
                -(1)) AS `surname`,
        `vmail`.`mailbox`.`username` AS `email`,
        `vmail`.`mailbox`.`domain` AS `domain`,
        (SELECT
                CONCAT_WS(' ',
                            `vmail`.`mailbox`.`name`,
                            `email`,
                            `vmail`.`mailbox`.`domain`)
            ) AS `words`
    FROM
        `vmail`.`mailbox`;