CREATE TABLE impressions (
    id         serial primary key,
    user_id    int,
    created_at timestamp not null
);

CREATE TABLE conversions (
    id         serial primary key,
    user_id    int,
    payout     decimal(10, 2),
    created_at timestamp not null
);

CREATE INDEX ON conversions (user_id, created_at);

-- Making partitions
do $$
declare
    i int4;
    day text;
    table_name text;
begin
    for i in 0..732 -- ~ number of days in 2 years
    loop
        day        := to_char(timestamp '2016-03-29 12:00:00' + format('%s day', i)::interval, 'YYYY-MM-DD');
        table_name := replace(day, '-', '_');

        execute format('CREATE TABLE impressions_%s (like impressions)', table_name);
        execute format('CREATE INDEX ON impressions_%s (user_id)', table_name); -- May be skipped for even faster inserts, but slow selects (~15% slow inserts)
        execute format('ALTER TABLE impressions_%s inherit impressions', table_name);
        execute format('ALTER TABLE impressions_%s add constraint impressions_check check ( created_at >= ''%s'' AND created_at <= ''%s'' )', table_name, format('%s 00:00:00', day), format('%s 23:59:59', day));
    end loop;
end;
$$;


-- Redirect the INSERTs into partitions
CREATE FUNCTION partition_for_impressions() returns trigger AS $$
DECLARE
    v_parition_name text;
BEGIN
    v_parition_name := format('impressions_%s', to_char(NEW.created_at, 'YYYY_MM_DD'));
    execute 'INSERT INTO ' || v_parition_name || ' VALUES ( ($1).* )' USING NEW;
    return NULL;
END;
$$ language plpgsql;

CREATE TRIGGER partition_impressions before insert 
    on impressions for each row execute procedure partition_for_impressions();



-- View for impressions of current month grouped by user_id
BEGIN;

DROP MATERIALIZED VIEW IF EXISTS impressions_current_month;
CREATE MATERIALIZED VIEW impressions_current_month AS

    SELECT
      user_id  as user_id,
      count(*) as impressions
    FROM impressions
    WHERE created_at BETWEEN '2016-03-01 00:00:00' AND '2016-03-31 23:59:59'
    GROUP BY user_id
    ORDER BY user_id;

COMMIT; -- Time: 6,136 ms

-- View for conversions of current month grouped by user_id
BEGIN;

DROP MATERIALIZED VIEW IF EXISTS conversions_current_month;
CREATE MATERIALIZED VIEW conversions_current_month AS

    SELECT
      user_id     as user_id,
      sum(payout) as payout
    FROM conversions
    WHERE created_at BETWEEN '2016-03-01 00:00:00' AND '2016-03-31 23:59:59'
    GROUP BY user_id
    ORDER BY user_id;

COMMIT; -- Time: 7,849 ms


-- View for the past year
TODO:
Here we should get all materialized views from postgres tables and 
then aggregate them into one view impressions_last_year and conversions_last_year;