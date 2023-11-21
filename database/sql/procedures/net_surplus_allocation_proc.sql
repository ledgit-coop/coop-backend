DELIMITER $$
CREATE PROCEDURE `net_surplus_allocation_proc`(IN `fromDate` DATE, IN `toDate` DATE)
BEGIN

DROP TEMPORARY TABLE IF EXISTS net_surplus_allocation_proc_temp;
  CREATE TEMPORARY TABLE net_surplus_allocation_proc_temp(
    loan_interest FLOAT(10.5),
    service_fee FLOAT(10.5),
    membership_fee FLOAT(10.5),
    gross_surplus FLOAT(10.5),
    operating_expenses FLOAT(10.5),
    net_surplus FLOAT(10.5)
  );

insert into net_surplus_allocation_proc_temp
SELECT
    COALESCE(SUM(
        (
            CASE WHEN(
                `transaction_sub_types`.`key` = 'loan-interest-payment'
            ) THEN `transactions`.`amount` ELSE 0
        END
    )
),0) AS `loan_interest`,
COALESCE(SUM(
    (
        CASE WHEN(
            `transaction_sub_types`.`key` = 'service-fee'
        ) THEN `transactions`.`amount` ELSE 0
    END
)
),0) AS `service_fee`,
COALESCE(SUM(
    (
        CASE WHEN(
            `transaction_sub_types`.`key` = 'membership-fee'
        ) THEN `transactions`.`amount` ELSE 0
    END
)
),0) AS `membership_fee`,
COALESCE(SUM(
    (
        CASE WHEN(
            `transaction_sub_types`.`key` IN(
                'loan-interest-payment',
                'service-fee',
                'membership-fee'
            )
        ) THEN `transactions`.`amount` ELSE 0
    END
)
),0) AS `gross_surplus`,
COALESCE(SUM(
    (
        CASE WHEN(
            `transaction_sub_types`.`type` = 'expenses'
        ) THEN `transactions`.`amount` ELSE 0
    END
)
),0) AS `operating_expenses`,
(
    COALESCE(SUM(
        (
            CASE WHEN(
                `transaction_sub_types`.`key` IN(
                    'loan-interest-payment',
                    'service-fee',
                    'membership-fee'
                )
            ) THEN `transactions`.`amount` ELSE 0
        END
    )
),0) - COALESCE(SUM(
    (
        CASE WHEN(
            `transaction_sub_types`.`type` = 'expenses'
        ) THEN `transactions`.`amount` ELSE 0
    END
)
),0)
) AS `net_surplus`
FROM
    (
        `transactions`
    JOIN `transaction_sub_types` ON
        (
            (
                `transaction_sub_types`.`id` = `transactions`.`transaction_sub_type_id`
            )
        )
    ) where transactions.transaction_date BETWEEN fromDate and toDate;
    
END$$
DELIMITER ;