\t
\timing
\pset format unaligned
set session work_mem = 2097152;
truncate table times_temp;
vacuum full times_temp;
truncate table usuarios_temp;
vacuum full usuarios_temp;
begin;
 -- ----------------------------------------------------------------------------
\copy datas (data) from '/fahstats/arquivos/data_usuarios.txt'

update datas set data = date_trunc( 'hour', data + interval '30 minute')
  where data = ( select data from datas order by data_serial desc limit 1 )
  ;
analyze datas;
\copy times_temp ( n_time, time_nome, pontos, wus ) from '/fahstats/arquivos/daily_team_summary_out.txt'

analyze times_temp;
-- ----------------------------------------------------------------------------
select data_serial into temporary data_serial
from datas
order by data_serial desc
limit 1
;
analyze data_serial;
-- ----------------------------------------------------------------------------
insert into times_indice (
  n_time
  )
  select n_time from times_temp
  where pontos > 0
  except
  select n_time from times_indice
  ;
update times_indice
  set time_nome = times_temp.time_nome
  from times_temp
  where times_indice.n_time = times_temp.n_time
    and times_indice.time_nome != times_temp.time_nome
  ;
-- ----------------------------------------------------------------------------
insert into times (
  data,
  n_time,
  pontos,
  wus
  )
  select (select data_serial from data_serial) as data_serial,
    times_indice.n_time,
    pontos,
    wus
    from times_temp inner join times_indice
    on times_temp.n_time = times_indice.n_time
    where times_temp.pontos > 0
    order by data_serial, times_indice.n_time
  ;
-- ----------------------------------------------------------------------------
DROP INDEX public.usuarios_temp_ndx;
\copy usuarios_temp ( usuario, pontos, wus, n_time ) from '/fahstats/arquivos/daily_user_summary_out.txt'

CREATE INDEX usuarios_temp_ndx
  ON public.usuarios_temp
  USING btree
  (n_time, usuario)
  ;
analyze usuarios_temp;
-- ----------------------------------------------------------------------------
insert into usuarios_indice (
  usuario_nome,
  n_time
  )
  select usuario, n_time from usuarios_temp
  where pontos > 0
  except
  select usuario_nome, n_time from usuarios_indice
  ;
-- ----------------------------------------------------------------------------
insert into usuarios (
  data,
  usuario,
  pontos,
  wus
  )
  select
    (select data_serial from data_serial) as data,
    ui.usuario_serial as usuario,
    sum(pontos) as pontos,
    sum(wus) as wus
  from usuarios_temp as ut inner join usuarios_indice as ui
    on ut.usuario = ui.usuario_nome and ut.n_time = ui.n_time
  where ut.pontos > 0
  group by data, ui.usuario_serial
  order by data, ui.usuario_serial
  ;
drop table data_serial;
-- ----------------------------------------------------------------------------
commit;
-- ----------------------------------------------------------------------------
select kstime();
select update_last_date_temp();
truncate table teams_production_temp;
vacuum full teams_production_temp;
select insert_teams_production_temp();
--analyze teams_production_temp;
select update_teams_rank_temp();

drop index ndx_donor_production_temp;
drop index ndx_n_time_donor_production_temp;
truncate table donors_production_temp;
vacuum full donors_production_temp;
truncate table donors_production_matriz;
vacuum full donors_production_matriz;

select insert_donors_production_matriz();
select update_donors_rank_temp();
select create_ndx_donors_production_temp();
--analyze donors_production_temp;

select update_team_active_members_temp_2();
select update_donor_first_wu();
select update_team_new_members_temp_2();
select insert_project_total_teams_production_temp();

drop index ndx_teams_production;
drop index ndx_active_teams_production;
truncate table teams_production;
vacuum full teams_production;
insert into teams_production
  select * from teams_production_temp order by active, n_time;
create index ndx_teams_production on teams_production using btree (n_time);
create index ndx_active_teams_production
  on teams_production using btree (active);
/*
drop index ndx_donor_production;
drop index ndx_n_time_donor_production;
drop index ndx_active_donor_production;
select kstime();
*/
truncate table donors_production;
vacuum full donors_production;
insert into donors_production
  select * from donors_production_temp order by n_time, active;
/*
create index ndx_donor_production
    on donors_production using btree (usuario);
create index ndx_n_time_donor_production
    on donors_production using btree (n_time);
create index ndx_active_donor_production
    on donors_production using btree (active);
select kstime();
*/
select update_last_date();
--analyze teams_production;
--analyze donors_production;

select insert_team_active_members_history();
select insert_donor_milestones();
update processing_end set "datetime" = now();
select delete_old();
select delete_team_active_members_history();
select update_donor_yearly();
select kstime();
--select now();
--reindex table times;select now();update maintenance set in_now = true;reindex table usuarios;update maintenance set in_now = false;select now();
