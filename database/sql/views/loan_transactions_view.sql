create view loan_transactions_view as
SELECT 
id,
loan_number,
(
    select sum(transactions.amount) from transactions
    join transaction_sub_types tst on tst.id = transactions.transaction_sub_type_id and tst.key='loan-principal-payment'
    where JSON_CONTAINS(transactions.parameters, concat(loans.id), '$.loan_id')
) as loan_principal_payment,
(
    select sum(transactions.amount) from transactions
    join transaction_sub_types tst on tst.id = transactions.transaction_sub_type_id and tst.key='loan-penalties-payment'
    where JSON_CONTAINS(transactions.parameters, concat(loans.id), '$.loan_id')
) as loan_penalty_payment,
(
    select sum(transactions.amount) from transactions
    join transaction_sub_types tst on tst.id = transactions.transaction_sub_type_id and tst.key='loan-interest-payment'
    where JSON_CONTAINS(transactions.parameters, concat(loans.id), '$.loan_id')
) as loan_interest_payment

FROM `loans` WHERE loans.released = true and loans.status <> 'closed'  and loans.deleted_at is null;