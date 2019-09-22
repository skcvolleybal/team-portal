delete from J3_user_usergroup_map 
where group_id in (
    select id from J3_usergroups where title like "Coach %" or title like "Trainer %"
);

insert into J3_user_usergroup_map (user_id, group_id) values ((select id from J3_users where name = 'Naam van Speler'), (select id from J3_usergroups where title = 'Trainer Dames X'));
insert into J3_user_usergroup_map (user_id, group_id) values ((select id from J3_users where name = 'Naam van Speler'), (select id from J3_usergroups where title = 'Coach Heren XY'));