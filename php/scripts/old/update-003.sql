DROP TABLE DWF_wedstrijden;
DROP TABLE DWF_punten;
CREATE TABLE DWF_wedstrijden (
  id varchar(16) COLLATE latin1_general_cs NOT NULL,
  team1 varchar(64) NOT NULL,
  team2 varchar(64) NOT NULL,
  setsTeam1 int(11) NOT NULL,
  setsTeam2 int(11) NOT NULL
);
CREATE TABLE DWF_punten (
  id int(11) NOT NULL AUTO_INCREMENT,
  matchId varchar(16) COLLATE latin1_general_cs NOT NULL,
  currentSet int(11) NOT NULL,
  isThuisService char(1) NOT NULL,
  isThuisPunt char(1) NOT NULL,
  puntenTeam1 int(11) NOT NULL,
  puntenTeam2 int(11) NOT NULL,
  ra int(11) DEFAULT NULL,
  rv int(11) DEFAULT NULL,
  mv int(11) DEFAULT NULL,
  lv int(11) DEFAULT NULL,
  la int(11) DEFAULT NULL,
  ma int(11) DEFAULT NULL,
  PRIMARY KEY (id)
);
CREATE INDEX DWF_wedstrijden_id ON DWF_wedstrijden(id);
CREATE INDEX DWF_punten_matchId ON DWF_punten(matchId);
CREATE INDEX DWF_punten_ra ON DWF_punten(ra);
CREATE INDEX DWF_punten_rv ON DWF_punten(rv);
CREATE INDEX DWF_punten_mv ON DWF_punten(mv);
CREATE INDEX DWF_punten_lv ON DWF_punten(lv);
CREATE INDEX DWF_punten_la ON DWF_punten(la);
CREATE INDEX DWF_punten_ma ON DWF_punten(ma);