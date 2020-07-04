---------------------------------------------------------
---------------------------------------------------------
------    as SYS:
---------------------------------------------------------
---------------------------------------------------------


create user itservice identified by itservice
 default   tablespace userspace
 temporary tablespace temp ;
grant create session   to itservice;
grant create procedure to itservice;
grant create synonym   to itservice;

-- set password
-- alter user itservice identified by ****;


grant alter system             to system;
grant alter user               to system;
grant select on sys.v_$process to system;
grant select on dba_users      to system;




---------------------------------------------------------
---------------------------------------------------------
------    as system:
---------------------------------------------------------
---------------------------------------------------------

create or replace package system.exposed_to_low_privs_users as
---------------------------------------------------------------
procedure reset_password (     p_username     varchar2
                             , p_new_password varchar2
                         );
---------------------------------------------------------------
procedure get_own_sessions (   p_username IN  varchar2
                             , p_out      OUT sys_refcursor
                           );
---------------------------------------------------------------
procedure kill_own_session (   p_username     varchar2
                             , p_sid          number
                             , p_serial       number
                           );
---------------------------------------------------------------
procedure get_account_statuses (   p_out      OUT sys_refcursor
                                 , p_username IN  varchar2      default null
                               );
---------------------------------------------------------------
end exposed_to_low_privs_users;
/




create or replace package body system.exposed_to_low_privs_users as
-------------------------------------------------------------------------------------------------------------------------------------------------
procedure reset_password (     p_username     varchar2
                             , p_new_password varchar2
                         )
as
  l_username varchar2(30) := sys.dbms_assert.ENQUOTE_NAME(sys.dbms_assert.SCHEMA_NAME(p_username),FALSE);
  l_password varchar2(30) := sys.dbms_assert.ENQUOTE_NAME(sys.dbms_assert.SIMPLE_SQL_NAME(p_new_password),FALSE);
begin
     --
     --execute immediate 'alter user '||l_username||' identified by '||substr(l_password, 2, length(l_password) -2)||' account unlock';
     execute immediate 'alter user '||l_username||' identified by '||l_password||' account unlock';
     --
end reset_password;
-------------------------------------------------------------------------------------------------------------------------------------------------
procedure get_own_sessions (   p_username IN  varchar2
                             , p_out      OUT sys_refcursor
                           )
as
  l_username varchar2(30) := sys.dbms_assert.SCHEMA_NAME(p_username);
begin
     open p_out for 
                    select   substr(p.spid             , 1,  5)  as pid
                           , substr(to_char(s.sid    ) , 1,  5)  as sid
                           , substr(to_char(s.serial#) , 1,  5)  as ser#
                           , substr(s.machine          , 1, 25)  as box
                           , substr(s.username         , 1, 17)  as username
                           , substr(s.osuser           , 1,  8)  as os_user
                           , substr(s.program          , 1, 30)  as program
                      from   v$session  s
                           , v$process  p
                     where s.username     =  l_username
                       and s.paddr        =  p.addr
                       and s.type         =  'USER'
                       and s.program      <> 'OMS'
                       and s.service_name not in ('SYS$BACKGROUND','SYS$USERS')
                     order by p.spid 
                    ;
--
end get_own_sessions;
-------------------------------------------------------------------------------------------------------------------------------------------------
procedure kill_own_session (   p_username     varchar2
                             , p_sid          number
                             , p_serial       number
                           )
as
  l_username varchar2(30) := sys.dbms_assert.SCHEMA_NAME(p_username);
  l_session_exists number(1,0) := 0;
begin
     --
     select count(*)
       into l_session_exists
       from dual
      where exists (
                      select null
                        from v$session
                       where username = l_username
                         and sid      = p_sid
                         and serial#  = p_serial
                   )
     ;
     --
     if l_session_exists > 0  then
          execute immediate 'alter system kill session '''||to_char(p_sid)||','||to_char(p_serial)||'''';
     end if;
     --
end kill_own_session;
-------------------------------------------------------------------------------------------------------------------------------------------------
procedure get_account_statuses (   p_out      OUT sys_refcursor
                                 , p_username IN  varchar2      default null
                               )
as
  l_username varchar2(30) := sys.dbms_assert.SIMPLE_SQL_NAME(nvl(p_username,'p_username_is_null'));
begin
     open p_out for 
                     select   username
                            , account_status
                       from dba_users
                      where default_tablespace not in
                                                      (
                                                           'SYSTEM'
                                                         , 'SYSAUX'
                                                         , 'STREAM_TBS'
                                                      ) 
                        and username           not in
                                                      (
                                                           'APPQOSSYS'
                                                         , 'DIP'
                                                         , 'ORACLE_OCM'
                                                         , 'MDDATA'
                                                         , 'XS$NULL'
                                                      )
                        and username not like 'SPATIAL%'
                        and username     like replace(l_username,'p_username_is_null','')||'%'
                      order by username
     ;
     --
end get_account_statuses;
-------------------------------------------------------------------------------------------------------------------------------------------------
end exposed_to_low_privs_users;
/


grant execute on system.exposed_to_low_privs_users to itservice;



---------------------------------------------------------
---------------------------------------------------------
------    as itservice:
---------------------------------------------------------
---------------------------------------------------------



create synonym exposed_to_low_privs_users for system.exposed_to_low_privs_users ;


