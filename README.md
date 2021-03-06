## Orbis

Было решено использовать партиционирование по дню, получилось около
740 партиций за 2 года сгенеренных данных. Данных удалось создать только 2 млрд записей,
больше сгенерить не было ни места ни времени, так что все бенчмарки можно умножать на 10. 
На каждую партицию около 3.5 млн. записей. У партиций есть ключ по user_id. Код SQL можно посмотреть
в файле commands.sql. Остальные *.sql файлы можно не смотреть.

## По user_id и дате с группировкой по часам

```
EXPLAIN ANALYZE
SELECT 
    date_trunc('hour', created_at) AS hour,
    COUNT(*)                       AS impressions
FROM impressions
WHERE user_id = 666 AND 
    created_at BETWEEN '2016-06-01 00:00:00' AND '2016-06-07 23:59:59'
GROUP BY date_trunc('hour', created_at);

-- Execution time: ~1.101ms
```

У таблицы с конверсиями композитный ключ по user_id, created_at:

```
EXPLAIN ANALYZE
SELECT 
    date_trunc('hour', created_at) AS hour,
    SUM(payout)                    AS payout
FROM conversions
WHERE user_id = 666 AND 
    created_at BETWEEN '2016-06-01 00:00:00' AND '2016-06-07 23:59:59'
GROUP BY date_trunc('hour', created_at);

-- Execution time: ~0.773 ms
```

Отказался от джоина двух таблицы, решил сделать мердж на стороне приложения.

## Текущий месяц с группировкой по user_id

Создал MATERIALIZED VIEW для текущего месяца, можно поставить на крон раз в пол часа - час,
генерится недолго (6,136 ms на моей машине). При начале нового месяца эту вьюху можно перегенерить
с другим названием (impressions_2016_06) и генерить новую на новых данных.

```
EXPLAIN ANALYZE
SELECT *
FROM impressions_current_month;

-- Execution time: 12.956 ms
```


## Общая стата по месяцам за предыдущий год

Генерим вьюху из вьюх по месяцам. Это делается один раз, т.к. предыдущий год.

При увеличении кол-ва селектов я бы добавил больше слэйвов, с которых и должно происходить чтение.
При увеличении инсертов добавить еще мастер. Еще можно подумать в сторону ClickHouse и дальше Hadoop
если > ~10Тб.
