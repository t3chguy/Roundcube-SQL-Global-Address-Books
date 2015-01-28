CREATE 
    ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `global_addressbook` AS
    SELECT 
        SUBSTR(UNIX_TIMESTAMP(`vmail`.`mailbox`.`created`),
            -(6)) AS `ID`,
        `vmail`.`mailbox`.`name` AS `name`,
        SUBSTRING_INDEX(SUBSTRING_INDEX(`vmail`.`mailbox`.`name`, ' ', 1),
                ' ',
                -(1)) AS `firstname`,
        SUBSTRING_INDEX(SUBSTRING_INDEX(`vmail`.`mailbox`.`name`, ' ', -(1)),
                ' ',
                -(1)) AS `surname`,
        `vmail`.`mailbox`.`username` AS `email`,
        `vmail`.`mailbox`.`domain` AS `domain`
    FROM
        `vmail`.`mailbox`;