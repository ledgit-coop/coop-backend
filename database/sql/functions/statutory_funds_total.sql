DELIMITER $$
CREATE FUNCTION `statutory_funds_total`(`fromDate` DATE, `toDate` DATE) RETURNS float
    DETERMINISTIC
BEGIN

DECLARE staturyReserves float default 0;

call statutory_funds_proc(fromDate, toDate);
SELECT sum(`allocation_reserve_fund` + `educational_training_fund` + `optional_fund`) into staturyReserves FROM `statutory_funds_proc_temp` limit 1;

return staturyReserves;

END$$
DELIMITER ;