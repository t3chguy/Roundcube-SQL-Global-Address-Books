CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`localhost`
    SQL SECURITY DEFINER
VIEW `MultiBook` AS
    SELECT
        SUBSTR((UNIX_TIMESTAMP(`vmail`.`mailbox`.`created`) -
        CHAR_LENGTH(`vmail`.`mailbox`.`password`)), -(8)) AS `ID`,
        `vmail`.`mailbox`.`name` AS `name`,
        (SELECT GROUP_CONCAT(`vmail`.`alias`.`address` SEPARATOR ',')
            FROM
                `vmail`.`alias`
            WHERE
                ((`vmail`.`alias`.`goto` = `vmail`.`mailbox`.`username`)
                    AND (`vmail`.`alias`.`address` LIKE '%@%'))) AS `email`,
        `vmail`.`mailbox`.`domain` AS `domain`
    FROM
        `vmail`.`mailbox`;