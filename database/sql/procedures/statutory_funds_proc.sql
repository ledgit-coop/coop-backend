DELIMITER $$
CREATE PROCEDURE `statutory_funds_proc`(IN `fromDate` DATE, IN `toDate` DATE)
BEGIN

DROP TEMPORARY TABLE IF EXISTS statutory_funds_proc_temp;
  CREATE TEMPORARY TABLE statutory_funds_proc_temp(
    allocation_reserve_fund FLOAT(10.5),
    educational_training_fund FLOAT(10.5),
    educational_training_fund_due_cetf FLOAT(10.5),
    educational_training_fund_due_etf FLOAT(10.5),
    optional_fund FLOAT(10.5)
  );


call net_surplus_allocation_proc(fromDate, toDate);

insert into statutory_funds_proc_temp
SELECT

    (
        `net_surplus_allocation_proc_temp`.`net_surplus` *(
        SELECT
            (`settings`.`value` / 100)
        FROM
            `settings`
        WHERE
            (
                `settings`.`key` = 'allocation-reserve-fund'
            )
        LIMIT 1
    )
    ) AS `allocation_reserve_fund`,(
        `net_surplus_allocation_proc_temp`.`net_surplus` *(
        SELECT
            (`settings`.`value` / 100)
        FROM
            `settings`
        WHERE
            (
                `settings`.`key` = 'educational-training-fund'
            )
        LIMIT 1
    )
    ) AS `educational_training_fund`,(
        (
            `net_surplus_allocation_proc_temp`.`net_surplus` *(
            SELECT
                (`settings`.`value` / 100)
            FROM
                `settings`
            WHERE
                (
                    `settings`.`key` = 'educational-training-fund'
                )
            LIMIT 1
        )
        ) * 0.50
    ) AS `educational_training_fund_due_cetf`,(
        (
            `net_surplus_allocation_proc_temp`.`net_surplus` *(
            SELECT
                (`settings`.`value` / 100)
            FROM
                `settings`
            WHERE
                (
                    `settings`.`key` = 'educational-training-fund'
                )
            LIMIT 1
        )
        ) * 0.50
    ) AS `educational_training_fund_due_etf`,(
        `net_surplus_allocation_proc_temp`.`net_surplus` *(
        SELECT
            (`settings`.`value` / 100)
        FROM
            `settings`
        WHERE
            (`settings`.`key` = 'optional-fund')
        LIMIT 1
    )
    ) AS `optional_fund`
FROM
    `net_surplus_allocation_proc_temp`;


    
END$$
DELIMITER ;