DELIMITER $$
CREATE FUNCTION `share_capital_rate_interest`(`fromDate` DATE, `toDate` DATE) RETURNS float
    DETERMINISTIC
BEGIN

DECLARE total float default 0;
DECLARE totalShareAvMonth float DEFAULT 0;
    

call member_share_capital_shares(fromDate, toDate);
SELECT sum(member_share_capital_shares_temp.total) into totalShareAvMonth FROM `member_share_capital_shares_temp` limit 1;

select share_capital_interest_allocation(fromDate, toDate) / totalShareAvMonth into total;
return total;

END$$
DELIMITER ;