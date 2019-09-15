delete from J3_user_usergroup_map
where group_id in (
    select id from J3_usergroups where parent_id = (
        select id from J3_usergroups where title = 'Teams'
    )
);

INSERT INTO J3_user_usergroup_map(group_id, user_id)
SELECT G.id, U.id FROM tcapp_players TC
INNER JOIN tcapp_teams T ON TC.team_id = T.id
INNER JOIN J3_usergroups G ON T.name = G.title
INNER JOIN J3_users U ON U.name = TC.name;
