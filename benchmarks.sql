
## Impressions

EXPLAIN ANALYZE
SELECT
    date_trunc('hour', created_at) AS hour,
    COUNT(*)                       AS impressions
FROM impressions
WHERE user_id = 666 AND
    created_at BETWEEN '2016-06-01 00:00:00' AND '2016-06-07 23:59:59'
GROUP BY date_trunc('hour', created_at);

*Execution time*

- Without index on user_id: 6174.735 ms

- With index on user_id: *1.101 ms*


## Conversions

EXPLAIN ANALYZE
SELECT 
    date_trunc('hour', created_at) AS hour,
    SUM(payout)                    AS payout
FROM conversions
WHERE user_id = 666 AND 
    created_at BETWEEN '2016-06-01 00:00:00' AND '2016-06-07 23:59:59'
GROUP BY date_trunc('hour', created_at);

*Execution time*

- Without any indexes: 11344.974 ms

- With index on user_id: 72.576 ms

- With index on user_id and created_at: 170.268 ms

- With composite index on user_id and created_at: *0.773 ms*


## Current month by user_id
EXPLAIN ANALYZE
SELECT *
FROM impressions_current_month;

Execution time: *12.956 ms*


EXPLAIN ANALYZE
SELECT *
FROM conversions_current_month;

Execution time: *20.887 ms*


## Past year
EXPLAIN ANALYZE
SELECT COUNT(*)
FROM impressions_current_month;