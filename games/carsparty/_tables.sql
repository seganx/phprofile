DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `username` varchar(64) CHARACTER SET ascii DEFAULT NULL,
  `password` varchar(64) CHARACTER SET ascii DEFAULT NULL,
  `nickname` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `status` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `avatar` varchar(256) CHARACTER SET ascii DEFAULT NULL,
  INDEX (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=404;

DROP TABLE IF EXISTS `profile_data`;
CREATE TABLE `profile_data` (
  `profile_id` int(10) unsigned NOT NULL PRIMARY KEY,
  `private_data` mediumtext CHARACTER SET ascii DEFAULT NULL,
  `public_data` mediumtext CHARACTER SET ascii DEFAULT NULL,
  INDEX (`username`)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS `league`;
CREATE TABLE `league` (
  `profile_id` int(10) unsigned NOT NULL,
  `league_id` int(10) unsigned NOT NULL,
  `score` int(10) DEFAULT 0,
  `rank` int(10) DEFAULT 0,
  `end_score` int(10) DEFAULT 0,
  `end_rank` int(10) DEFAULT 0,
  INDEX (`profile_id`),
  INDEX (`league_id`)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` int(10) unsigned NOT NULL,
  `friend_id` int(10) unsigned NOT NULL,
  INDEX (`profile_id`),
  INDEX (`friend_id`)
) ENGINE=InnoDB;


-- people who have gems but have not purchase
SELECT p.id, s.version, s.gems, p.nickname, p.device_id 
FROM profile p
INNER JOIN stats s ON p.id=s.profile_id 
where s.gems>50000 AND (p.id not in (select profile_id from purchases))
ORDER BY s.gems DESC

-- display duplicated fields in a table
SELECT token, COUNT(token) FROM purchases GROUP BY token HAVING COUNT(token) > 1;

-- remove duplicated rows from a table
DELETE c1 FROM purchases c1
INNER JOIN purchases c2 
WHERE c1.time > c2.time AND c1.token = c2.token;