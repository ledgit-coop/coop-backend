CREATE FUNCTION `patronage_refund_allocation`(`fromDate` DATE, `toDate` DATE) RETURNS float
    DETERMINISTIC
BEGIN

    DECLARE patronageInt float default 0;
    DECLARE netSurplus float default 0;
    DECLARE staturyReserves float default 0;
    
select value into patronageInt from settings where settings.key = 'remainder-patronage-refund';

SELECT statutory_funds_total(fromDate, toDate), net_surplus_total(fromDate, toDate) into staturyReserves, netSurplus;

return ((netSurplus-staturyReserves) * (patronageInt / 100));

END