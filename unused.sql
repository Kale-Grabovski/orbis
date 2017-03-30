
CREATE INDEX pair_created_at_idx ON impressions (created_at);
CREATE INDEX pair_user_id_idx ON impressions (user_id);

CREATE FUNCTION insert_month_and_hour () RETURNS trigger AS ' 
BEGIN 
NEW.hour=date_trunc(''hour'', NEW.created_at);
NEW.month=date_trunc(''month'', NEW.created_at);
return NEW;
END; 
' LANGUAGE  plpgsql;

DROP FUNCTION IF EXISTS insert_month_and_hour() CASCADE;

-- Attach the trigger
CREATE TRIGGER tg_impressions_month_hour 
BEFORE INSERT ON impressions FOR EACH ROW 
EXECUTE PROCEDURE insert_month_and_hour ();

DROP TRIGGER IF EXISTS tg_impressions_month_hour ON impressions;

-- Population of database
time psql tetrius -c "INSERT INTO impressions(user_id, created_at) SELECT trunc(random() * 100000 + 1), cast(now() - '1 years'::interval * random() as timestamp(0)) FROM generate_series(1, 10000000)"

INSERT INTO conversions(user_id, payout, created_at) 
SELECT 
    trunc(random() * 100000 + 1), 
    random() * 3, 
    cast(now() - '1 years'::interval * random() as timestamp(0)) 
FROM generate_series(1, 23000000);



-- For existing large table with empty partitions
select to_char(i, 'YYYY_MM_DD') 
from 
    generate_series
        ( '2016-03-01'::timestamp 
        , '2018-03-03'::timestamp
        , '1 day'::interval) i;

-- In psql
\pset format unaligned
\pset tuples_only true
\o /tmp/impressions.batch.migration.sql
SELECT
    format(
        'with x as (DELETE FROM ONLY impressions WHERE created_at >= ''%s 00:00:00'' AND created_at <= ''%s 23:59:59'' returning *) INSERT INTO impressions_%s SELECT * FROM x;',
        i::date,
        i::date,
        to_char(i, 'YYYY_MM_DD') 
    )
FROM
    generate_series
        ( '2016-03-01'::timestamp 
        , '2018-03-03'::timestamp
        , '1 day'::interval) i;
\o

