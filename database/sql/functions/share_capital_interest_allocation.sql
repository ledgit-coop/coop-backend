DELIMITER $$
CREATE FUNCTION `share_capital_interest_allocation`(`fromDate` DATE, `toDate` DATE) RETURNS float
    DETERMINISTIC
BEGIN

    DECLARE shareCapitalInt float default 0;
    DECLARE netSurplus float default 0;
    DECLARE staturyReserves float default 0;
    
select value into shareCapitalInt from settings where settings.key = 'remainder-interest-share-capital';

SELECT statutory_funds_total(fromDate, toDate), net_surplus_total(fromDate, toDate) into staturyReserves, netSurplus;

return ((netSurplus-staturyReserves) * (shareCapitalInt / 100));

END$$
DELIMITER ;