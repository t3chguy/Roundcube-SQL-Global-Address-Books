CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `postfix`
    SQL SECURITY DEFINER
VIEW `global_addressbook` AS
    SELECT
        SUBSTR((UNIX_TIMESTAMP(`postfix`.`mailbox`.`created`) -
        CHAR_LENGTH(`postfix`.`mailbox`.`password`)), -(8)) AS `ID`,
        `postfix`.`mailbox`.`name` AS `name`,
        `postfix`.`mailbox`.`username` AS `email`,
        `postfix`.`mailbox`.`domain` AS `domain`
    FROM
        `postfix`.`mailbox`;

