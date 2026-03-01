CREATE FUNCTION `patronage_refund_rate_interest`(`fromDate` DATE, `toDate` DATE) RETURNS float
    DETERMINISTIC
BEGIN

DECLARE totalInterest float DEFAULT 0;
    

CALL `net_surplus_allocation_proc`(fromDate, toDate);
SELECT sum(loan_interest) into totalInterest FROM `net_surplus_allocation_proc_temp`;

return patronage_refund_allocation(fromDate, toDate) / totalInterest;
END