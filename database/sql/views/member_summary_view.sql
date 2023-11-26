create view member_summary_view as SELECT 
id,
member_number,
first_name,
middle_name,
surname,
(
    select sum(transactions.amount) from transactions
    join transaction_sub_types tst on tst.id = transactions.transaction_sub_type_id and tst.key='membership-fee'
    where JSON_CONTAINS(transactions.parameters, concat(members.id), '$.member_id')
) as member_ship_fee,

(
    select sum(transactions.amount) from transactions
    join transaction_sub_types tst on tst.id = transactions.transaction_sub_type_id and tst.key='orientation-fee'
    where JSON_CONTAINS(transactions.parameters, concat(members.id), '$.member_id')
) as orientation_fee

from members where members.deleted_at is null;