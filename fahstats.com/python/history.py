#!/usr/bin/env python

import psycopg2 as dbd, setup
from convertArray import convertArray

def div0(a, b):

    try:
        return a / b
    except ZeroDivisionError:
        return 0

donor_update_query = """\
select
    points,
    wus,
    to_char(date_trunc('day', data), 'YYYY-MM-DD') as "day",
    extract('hour' from data) as dow
from (
    select
        d.data,
        pontos - lead(pontos, 1, 0::real) over w as points,
        wus - lead(wus, 1, 0) over w as wus
    from usuarios u
    inner join datas d on d.data_serial = u.data
    where
      usuario = %(id)s
      and
      d.data > (select max(data) - interval '15 days' from datas)
    window w as (order by d.data desc)
    ) ss
where
    points is not null
    and
    data > (select max(data) - interval '14 days' from datas)
order by day desc, dow
;"""

donor_daily_query = """\
select
    points, wus,
    to_char(date_trunc('day', data), 'YYYY-MM-DD') as "day",
    isodow(data) as dow
from (
    select
        pontos - lead(pontos, 1, 0::real) over w as points,
        wus - lead(wus, 1, 0) over w as wus,
        d.data
    from usuarios u
    inner join datas d on d.data_serial = u.data
    where
        usuario = %(id)s
        and
        d.data in (
            select sq.data
            from (
                select
                    date_trunc('day', data) as day,
                    max(data) as data
                from datas
                where
                    data > date_trunc('day', (select max(data) - interval '56 days' from datas))
                group by day
                ) sq
            )
    window w as (order by d.data desc)
) ss
where
    points is not null
    and
    data > date_trunc('day', (select max(data) - interval '56 days' from datas) + interval '1 day')
order by yearweek(data) desc, isodow(data)
;"""

team_update_query = """\
select
    points,
    wus,
    to_char(date_trunc('day', data), 'YYYY-MM-DD') as day,
    extract('hour' from data) as dow
from (
    select
        d.data,
        pontos - lead(pontos, 1, 0::real) over w as points,
        wus - lead(wus, 1, 0) over w as wus
    from times t
    inner join datas d on d.data_serial = t.data
    where
      n_time = %(id)s
      and
      d.data > (select max(data) - interval '15 days' from datas)
    window w as (order by d.data desc)
    ) ss
where
    points is not null
    and
    data > (select max(data) - interval '14 days' from datas)
order by day desc, dow
;"""

team_daily_query = """\
select
    points, wus,
    to_char(date_trunc('day', data), 'YYYY-MM-DD') as day,
    isodow(data) as dow
from (
    select
        pontos - lead(pontos, 1, 0::real) over w as points,
        wus - lead(wus, 1, 0) over w as wus,
        d.data
    from times t
    inner join datas d on d.data_serial = t.data
    where
        n_time = %(id)s
        and
        d.data in (
            select sq.data
            from (
                select
                    date_trunc('day', data) as day,
                    max(data) as data
                from datas
                where
                    data > date_trunc('day', (select max(data) - interval '56 days' from datas))
                group by day
                ) sq
            )
    window w as (order by d.data desc)
) ss
where
    points is not null
    and
    data > date_trunc('day', (select max(data) - interval '56 days' from datas) + interval '1 day')
order by yearweek(data) desc, isodow(data)
;"""

def next(g):
    try:
        return g.next()
    except StopIteration:
        return None

def table_list(id, table):

    dia = dict([(x, x) for x in range(7)])
    hora =  dict([(x, int(x/3)) for x in range(24)])

    if table == "team_daily":
        columns = 7
        query = team_daily_query
        conversao = dia
    elif table == "donor_daily":
        columns = 7
        query = donor_daily_query
        conversao = dia
    elif table == "team_update":
        columns = 8
        query = team_update_query
        conversao = hora
    else: # donor_update
        columns = 8
        query = donor_update_query
        conversao = hora

    conn = dbd.connect(setup.connStr["frontend"])
    conn.autocommit = True
    cursor =  conn.cursor()
    cursor.execute(query, {'id':id})
    g = (day for day in cursor.fetchall())
    cursor.close()
    conn.close()

    l = list()
    row_total = list()
    column_total = list()
    for i in range(columns):
        column_total.append([0, 0])
    i = 0
    day = next(g)
    total_column_total = [0, 0]

    while day:
        l.append((day[2], list())) # date-day
        row_total = [0, 0]
        j = 0
        while j < columns:
            if day and conversao[day[3]] == j:
                l[i][1].append((day[0], int(day[1]), div0(day[0], day[1])))
                row_total[0] += day[0]
                row_total[1] += day[1]
                column_total[j][0] += day[0]
                column_total[j][1] += day[1]
                day = next(g)
            else:
                l[i][1].append((0 ,0 ,0))
            j += 1
        l[i][1].append((row_total[0], int(row_total[1]), \
                        div0(row_total[0], row_total[1])))
        total_column_total[0] += row_total[0]
        total_column_total[1] += row_total[1]
        i += 1

    rows = float(i -1)
    column_avg = [(div0(points, rows), div0(wus, rows), div0(points, wus)) \
                        for points, wus in column_total]
    points_avg_sum =  sum([t[0] for t in column_avg])
    wus_avg_sum = sum([t[1] for t in column_avg])
    column_avg.append((0, 0, 0))
    averages = (
        (points_avg_sum / columns, wus_avg_sum / columns,
            div0(points_avg_sum, wus_avg_sum)),
        (div0(total_column_total[0], rows), div0(total_column_total[1], rows),
            div0(total_column_total[0], total_column_total[1])),
        )
    l.append(('Average', column_avg))

    return (l, averages)

def main(argv):

    id = int(argv[0])
    argv = argv[1:]

    l = list()
    for table in argv:
        l.append(table_list(id, table))

    for row in l:
        print convertArray(row[0])
        #print
        print convertArray(row[1])
        #print row[0]
        #print
        #print row[1]

    return
    #print l

    #sys.exit(0)
    for i in range(len(l)):
        print l[i][0],
        for j in range(len(l[i][1])):
            print l[i][1][j][0],
        print

if __name__ == '__main__':
    import sys
    main(sys.argv[1:])
