CREATE FUNCTION `net_surplus_total`(`fromDate` DATE, `toDate` DATE) RETURNS float
    DETERMINISTIC
BEGIN

DECLARE netSurplus float default 0;


call net_surplus_allocation_proc(fromDate, toDate);
SELECT sum(net_surplus_allocation_proc_temp.net_surplus) into netSurplus FROM `net_surplus_allocation_proc_temp` limit 1;


return netSurplus;

END