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
  `data` JSON NOT NULL
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `profile_id` int unsigned NOT NULL,
  `version` varchar(16) NOT NULL,
  `market` varchar(16) NOT NULL,
  `sku` varchar(32) NOT NULL,
  `price` int NOT NULL,
  `token` varchar(32) NOT NULL
) ENGINE=InnoDB;


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


-- age of stored PROCEDURE:
DROP FUNCTION IF EXISTS fix_username;
DELIMITER $$
CREATE FUNCTION fix_username(pid INT) RETURNS CHAR(32) DETERMINISTIC
BEGIN
    SET @res = LOWER(CONV(18000 + pid, 10, 26));
    SET @res = REPLACE(@res, '0', 'q');
    SET @res = REPLACE(@res, '1', 'r');
    SET @res = REPLACE(@res, '2', 's');
    SET @res = REPLACE(@res, '3', 't');
    SET @res = REPLACE(@res, '4', 'u');
    SET @res = REPLACE(@res, '5', 'v');
    SET @res = REPLACE(@res, '6', 'w');
    SET @res = REPLACE(@res, '7', 'x');
    SET @res = REPLACE(@res, '8', 'y');
    SET @res = REPLACE(@res, '9', 'z');
    return @res;
END;



DROP FUNCTION IF EXISTS assets_update_data;
DELIMITER $$
CREATE FUNCTION assets_update_data(data JSON, j_name CHAR(32), a_view INT, a_like INT) RETURNS JSON DETERMINISTIC
BEGIN
	SET @js = JSON_EXTRACT(data, j_name);
	SET @a0 = JSON_EXTRACT(@js, '$[0]');
    SET @a1 = JSON_EXTRACT(@js, '$[1]');
    SET @a0 = IF(@a0 IS NULL, 0, @a0);
    SET @a1 = IF(@a1 IS NULL, 0, @a1);
	return JSON_SET(data, j_name, JSON_ARRAY(@a0 + a_view, @a1 + a_like));
END;


DROP PROCEDURE IF EXISTS assets_update;
DELIMITER !!
CREATE PROCEDURE assets_update(p_id INT, a_name CHAR(32), a_view INT, a_like INT)
BEGIN
	INSERT INTO `assets` (`profile_id`, `data`) 
    VALUES (p_id, assets_update_data("{}", a_name, a_view, a_like)) 
    ON DUPLICATE KEY UPDATE `data`=assets_update_data(`data`, a_name, a_view, a_like);
END;

DROP PROCEDURE IF EXISTS likes_update;
DELIMITER !!
CREATE PROCEDURE likes_update(id varchar(32), a_name CHAR(32), a_liked INT)
BEGIN
	INSERT INTO `likes` (`id`, `data`) 
    VALUES (id, JSON_SET("{}", a_name, a_liked)) 
    ON DUPLICATE KEY UPDATE `data`=JSON_SET(`data`, a_name, a_liked);
END;

DROP PROCEDURE IF EXISTS social_get_public;
DELIMITER !!
CREATE PROCEDURE social_get_public(o_id INT, p_id INT)
BEGIN
    SELECT profile_data.public_data, assets.data as `assets`, likes.data as `likes` 
    from `profile_data` 
    LEFT JOIN `assets` ON assets.profile_id=o_id
    LEFT JOIN `likes` ON likes.id=CONCAT(p_id,'_',o_id)
    WHERE profile_data.profile_id=o_id;
END;

DROP PROCEDURE IF EXISTS social_set;
DELIMITER !!
CREATE PROCEDURE social_set(p_id INT, o_id INT, a_name varchar(32), a_views INT, a_likes INT, a_liked INT)
BEGIN
    CALL assets_update(o_id, a_name, a_views, a_likes);
    CALL likes_update(CONCAT(p_id, '_', o_id), a_name, a_liked);
END;

CALL social_get_public(410, 450);
CALL assets_update(410, '$.a200', 1, 0);
CALL likes_update('410_415', '$.a200', 1);



DROP PROCEDURE IF EXISTS league_total_update;
DELIMITER !!
CREATE PROCEDURE league_total_update(s_min INT, r_count INT)
BEGIN
	UPDATE league_total SET rank=0;
    
    SET @r=0;
    UPDATE league_total SET rank=@r:=(@r+1) WHERE score>s_min ORDER BY score DESC LIMIT 100000;
    
    SELECT profile.username, profile.nickname, profile.status, profile.avatar, league_total.score, league_total.rank 
    FROM profile 
    LEFT JOIN profile on league_total.profile_id=profile.id 
    WHERE league_total.score>s_min && league_total.rank>0 ORDER BY league_total.rank ASC LIMIT r_count;
END;


DROP FUNCTION IF EXISTS league_score_add;
DELIMITER $$
CREATE FUNCTION league_score_add(l_curr INT, s_curr INT, s_value INT) RETURNS INT DETERMINISTIC
BEGIN
	RETURN IF (l_curr <> s_curr, l_curr, s_curr + s_value);
END;

DROP PROCEDURE IF EXISTS league_total_add_score;
DELIMITER !!
CREATE PROCEDURE league_total_add_score(p_id INT, p_dv varchar(64), s_curr INT, s_value INT)
BEGIN
	UPDATE league_total SET score=league_score_add(score, s_curr, if (s_value < 11, s_value, 0)) WHERE profile_id=p_id AND device_id=p_dv;
END;
