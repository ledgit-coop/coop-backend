CREATE PROCEDURE `member_share_capital_shares`(IN `fromDate` DATE, IN `toDate` DATE)
BEGIN

DECLARE months float default 0;

DROP TEMPORARY TABLE IF EXISTS member_share_capital_shares_temp;
  CREATE TEMPORARY TABLE member_share_capital_shares_temp(
    total FLOAT(10.5),
    average_share FLOAT(10.5)
  );

-- Determine the months based on two dates ---
SELECT TIMESTAMPDIFF(MONTH, fromDate, toDate) into months;

insert into member_share_capital_shares_temp
SELECT
  
SUM(`account_transactions`.`amount`) AS `total`,
(
    SUM(`account_transactions`.`amount`) / 12
) AS `average_share`
FROM
    
        `account_transactions`
    JOIN `member_accounts` ON
        (
            (
                (
                    `member_accounts`.`id` = `account_transactions`.`member_account_id`
                ) AND EXISTS(
                SELECT
                    1
                FROM
                    `accounts`
                WHERE
                    (
                        (
                            `accounts`.`id` = `member_accounts`.`account_id`
                        ) AND(`accounts`.`type` = 'share-capital')
                    )
            )
            )
        )
    
    where account_transactions.transaction_date BETWEEN fromDate and toDate
ORDER BY
    `account_transactions`.`member_account_id` ASC;

END