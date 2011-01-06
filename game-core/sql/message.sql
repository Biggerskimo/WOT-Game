CREATE TABLE IF NOT EXISTS `ugml_message` (
  `messageID` int(10) unsigned NOT NULL auto_increment,
  `time` int(11) NOT NULL,
  `senderGroup` tinyint(3) unsigned NOT NULL,
  `senderID` int(10) unsigned NOT NULL,
  `recipentID` int(10) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `viewed` tinyint(1) NOT NULL,
  `checked` tinyint(1) NOT NULL,
  PRIMARY KEY  (`messageID`),
  KEY `time` (`time`),
  KEY `recipentID` (`recipentID`),
  KEY `viewed` (`viewed`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ugml_message_folder` (
  `folderID` int(10) unsigned NOT NULL auto_increment,
  `userID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `preset` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (`folderID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;

ALTER TABLE `ugml_message_folder`
  ADD CONSTRAINT `ugml_message_folder_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `ugml_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `ugml_message_notification` (
  `messageID` int(10) unsigned NOT NULL auto_increment,
  `notificationTime` int(11) NOT NULL,
  `reviewTime` int(11) NOT NULL,
  PRIMARY KEY  (`messageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ugml_message_sender` (
  `senderGroupID` tinyint(3) unsigned NOT NULL auto_increment,
  `senderGroupName` varchar(255) NOT NULL,
  PRIMARY KEY  (`senderGroupID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ugml_message_system` (
  `senderID` int(10) unsigned NOT NULL auto_increment,
  `sender` varchar(255) NOT NULL,
  PRIMARY KEY  (`senderID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=1 ;


DELIMITER //

DROP FUNCTION IF EXISTS MESSAGE_EXTRA//
CREATE FUNCTION MESSAGE_EXTRA(senderGroup INT UNSIGNED, senderID INT UNSIGNED, messageID INT UNSIGNED)
	RETURNS VARCHAR(255)
BEGIN
	DECLARE _extra VARCHAR(255);
	CASE senderGroup
		WHEN 1 THEN
			SELECT username INTO _extra
			FROM ugml_users
			WHERE id = senderID;
		WHEN 2 THEN
			SELECT CONCAT(ally_tag, ',', COALESCE(userID, ''), ',', COALESCE(username, '')) INTO _extra
			FROM ugml_alliance
			LEFT JOIN ugml_message_circular
				ON ugml_message_circular.messageID = messageID
			LEFT JOIN ugml_users
				ON ugml_users.id = ugml_message_circular.userID
			WHERE ugml_alliance.id = senderID;
		WHEN 3 THEN
			SELECT sender INTO _extra
			FROM ugml_message_system
			WHERE ugml_message_system.senderID = senderID;
	END CASE;
	RETURN _extra;
END;//

DROP FUNCTION IF EXISTS MESSAGE_FOLDER_PRESET//
CREATE FUNCTION MESSAGE_FOLDER_PRESET(userID INT, preset INT)
	RETURNS INT(10)
BEGIN
	DECLARE _folderID INT UNSIGNED;
	SELECT folderID INTO _folderID
	FROM ugml_message_folder
	WHERE ugml_message_folder.userID = userID
		AND ugml_message_folder.preset = preset;
	RETURN _folderID;
END;//

CREATE TRIGGER userMessageFolders AFTER INSERT ON ugml_users
	FOR EACH ROW
	BEGIN
		INSERT INTO ugml_message_folder
		(userID, name, holdBackTime, preset)
		VALUES
		(NEW.id, 'wot.messages.folder.espionageReports', 86400 * 7, 1),
		(NEW.id, 'wot.messages.folder.combatReports', 86400 * 7, 2),
		(NEW.id, 'wot.messages.folder.circulars', 86400 * 7, 3),
		(NEW.id, 'wot.messages.folder.directMessages', 86400 * 7, 4),
		(NEW.id, 'wot.messages.folder.others', 86400 * 7, 5);
END;//

DELIMITER ;

CREATE OR REPLACE ALGORITHM = MERGE VIEW ugml_v_message AS
SELECT messageID, `time`, senderGroup,
	senderID, recipentID, folderID, subject,
	text, viewed, checked,
	MESSAGE_EXTRA(senderGroup, senderID, messageID) AS extra
FROM ugml_message
ORDER BY `time` DESC;

INSERT INTO ugml_message_folder
(userID, name, holdBackTime, preset)
SELECT id, 'wot.messages.folder.espionageReports', 86400 * 7, 1
FROM ugml_users
UNION ALL SELECT
id, 'wot.messages.folder.combatReports', 86400 * 7, 2
FROM ugml_users
UNION ALL SELECT
id, 'wot.messages.folder.circulars', 86400 * 7, 3
FROM ugml_users
UNION ALL SELECT
id, 'wot.messages.folder.directMessages', 86400 * 7, 4
FROM ugml_users
UNION ALL SELECT
id, 'wot.messages.folder.others', 86400 * 7, 5
FROM ugml_users;
