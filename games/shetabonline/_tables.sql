DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
  `profile_id` int unsigned NOT NULL PRIMARY KEY,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `private_data` mediumtext CHARACTER SET ascii DEFAULT NULL,
  `public_data` mediumtext CHARACTER SET ascii DEFAULT NULL,
  INDEX (`device_id`)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS `league_name`;
CREATE TABLE `league_total` (
  `profile_id` int unsigned NOT NULL PRIMARY KEY,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `score` int DEFAULT 0,
  `rank` int DEFAULT 0,
  `end_score` int) DEFAULT 0,
  `end_rank` int DEFAULT 0,
  INDEX (`device_id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `profile_id` int unsigned NOT NULL PRIMARY KEY,
  `data` JSON NOT NULL
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` varchar(32) CHARACTER SET ascii NOT NULL PRIMARY KEY,
  `profile_id` int unsigned NOT NULL,
  `owner_id` int unsigned NOT NULL,
  `data` JSON NOT NULL
) ENGINE=InnoDB;


INSERT INTO assets (`profile_id`, `data`) VALUES (410, '{"id630":[123,5],"id535":[123,5]}');
UPDATE assets SET `data` = JSON_SET(`data`, '$.id600', JSON_ARRAY(JSON_EXTRACT(`data`,'$.id600[0]')+1,JSON_EXTRACT(`data`,'$.id600[1]')+1)) WHERE profile_id=410;



DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` int unsigned NOT NULL,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `friend_id` int unsigned NOT NULL,
  INDEX (`profile_id`),
  INDEX (`friend_id`),
  INDEX (`device_id`)
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