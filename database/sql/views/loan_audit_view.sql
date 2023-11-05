create or replace view loan_audit_view as select `loans`.`loan_number` AS `loan_number`,sum(`loan_schedules`.`amount_paid`) AS `amount_paid`,`member_accounts`.`balance` AS `account_balance` from `loan_schedules`
join `loans` on((`loans`.`id` = `loan_schedules`.`loan_id`))
join `member_accounts` on((`member_accounts`.`id` = `loans`.`member_account_id`))
where loans.deleted_at is null
group by `loans`.`loan_number`;