--- lower rf of rips
UPDATE ugml_spec_rapidfire SET shots = 125 WHERE specID = 214 AND target = 202;
UPDATE ugml_spec_rapidfire SET shots = 125 WHERE specID = 214 AND target = 203;
UPDATE ugml_spec_rapidfire SET shots = 125 WHERE specID = 214 AND target = 204;
UPDATE ugml_spec_rapidfire SET shots = 125 WHERE specID = 214 AND target = 205;
UPDATE ugml_spec_rapidfire SET shots = 16 WHERE specID = 214 AND target = 206;
UPDATE ugml_spec_rapidfire SET shots = 15 WHERE specID = 214 AND target = 207;
UPDATE ugml_spec_rapidfire SET shots = 125 WHERE specID = 214 AND target = 208;
UPDATE ugml_spec_rapidfire SET shots = 125 WHERE specID = 214 AND target = 209;
UPDATE ugml_spec_rapidfire SET shots = 625 WHERE specID = 214 AND target = 210;
UPDATE ugml_spec_rapidfire SET shots = 12 WHERE specID = 214 AND target = 211;
UPDATE ugml_spec_rapidfire SET shots = 625 WHERE specID = 214 AND target = 212;
UPDATE ugml_spec_rapidfire SET shots = 2 WHERE specID = 214 AND target = 213;
UPDATE ugml_spec_rapidfire SET shots = 12 WHERE specID = 214 AND target = 215;
UPDATE ugml_spec_rapidfire SET shots = 100 WHERE specID = 214 AND target = 401;
UPDATE ugml_spec_rapidfire SET shots = 100 WHERE specID = 214 AND target = 402;
UPDATE ugml_spec_rapidfire SET shots = 50 WHERE specID = 214 AND target = 403;
UPDATE ugml_spec_rapidfire SET shots = 25 WHERE specID = 214 AND target = 404;
UPDATE ugml_spec_rapidfire SET shots = 50 WHERE specID = 214 AND target = 405;

--- remove battleship
DELETE FROM ugml_spec_requirement WHERE specID = 215 OR requirement = 215;
DELETE FROM ugml_spec_rapidfire WHERE specID = 215 OR target = 215;
DELETE FROM ugml_spec WHERE specID = 215;
ALTER TABLE ugml_planets DROP battleship;

--- remove refinery
DELETE FROM ugml_spec_requirement WHERE specID = 13 OR requirement = 13;
DELETE FROM ugml_spec WHERE specID = 13;
ALTER TABLE ugml_planets DROP refinery;

--- remove alliance deposit
DELETE FROM ugml_spec_requirement WHERE specID = 34 OR requirement = 34;
DELETE FROM ugml_spec WHERE specID = 34;
ALTER TABLE ugml_planets DROP ally_deposit;

--- 15% ditf
UPDATE ugml_config SET config_value = 15 WHERE config_name = 'obrona_na_zlom';

--- remove 'destroy' mission
DELETE FROM ugml_mission WHERE missionID = 5;
DELETE FROM ugml_mission_route WHERE missionID = 5;