alter table TeamPortal_wedstrijden drop COLUMN telteam_id;
alter TABLE TeamPortal_wedstrijden
ADD teller1_id int null,
    ADD FOREIGN KEY teller1(teller1_id) REFERENCES J3_users(id),
    ADD teller2_id int null,
    ADD FOREIGN KEY teller2(teller2_id) REFERENCES J3_users(id);
alter table TeamPortal_zaalwacht drop COLUMN team_id;
alter TABLE TeamPortal_zaalwacht
ADD team1_id int(10) unsigned null,
    ADD FOREIGN KEY team1(team1_id) REFERENCES J3_usergroups(id),
    ADD team2_id int(10) unsigned null,
    ADD FOREIGN KEY team2(team2_id) REFERENCES J3_usergroups(id);