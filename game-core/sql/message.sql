CREATE TABLE IF NOT EXISTS `ugml_message` (
  `messageID` int(10) unsigned NOT NULL auto_increment,
  `time` int(11) NOT NULL,
  `senderGroup` tinyint(3) unsigned NOT NULL,
  `senderID` int(10) unsigned NOT NULL,
  `recipentID` int(10) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `viewed` tinyint(1) NOT NULL,
  PRIMARY KEY  (`messageID`),
  KEY `time` (`time`),
  KEY `recipentID` (`recipentID`),
  KEY `viewed` (`viewed`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=2 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=4 ;

CREATE TABLE IF NOT EXISTS `ugml_message_system` (
  `senderID` int(10) unsigned NOT NULL auto_increment,
  `sender` varchar(255) NOT NULL,
  PRIMARY KEY  (`senderID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=3 ;


DELIMITER //

DROP FUNCTION IF EXISTS MESSAGE_SENDER//
CREATE FUNCTION MESSAGE_SENDER(senderGroup INT UNSIGNED, senderID INT UNSIGNED)
	RETURNS VARCHAR(255)
BEGIN
	DECLARE _sender VARCHAR(255);
	CASE senderGroup
		WHEN 1 THEN
			SELECT username INTO _sender
			FROM ugml_users
			WHERE id = senderID;
		WHEN 2 THEN
			SELECT ally_tag INTO _sender
			FROM ugml_alliance
			WHERE id = senderID;
		WHEN 3 THEN
			SELECT sender INTO _sender
			FROM ugml_message_system
			WHERE ugml_message_system.senderID = senderID;
	END CASE;
	RETURN _sender;
END


DELIMITER ;

CREATE OR REPLACE ALGORITHM = MERGE VIEW ugml_v_message AS
SELECT messageID, `time`, senderGroup,
	senderID, recipentID, subject,
	text, viewed,
	MESSAGE_SENDER(senderGroup, senderID) AS sender
FROM ugml_message
