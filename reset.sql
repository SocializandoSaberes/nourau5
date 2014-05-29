-- fake PHP header for xgettext: <?php

SELECT SETVAL('notice_seq','0');
SELECT SETVAL('topic_seq', '0');
SELECT SETVAL('users_seq', '0');

DELETE FROM log;
DELETE FROM notice;
DELETE FROM topic;
DELETE FROM topic_path;
DELETE FROM users;
DELETE FROM user_registration;
DELETE FROM version;

INSERT INTO users (username,password,name,email,level) VALUES ('admin','nqzva',_('Administrator'),'nou-rau@localhost','3');

INSERT INTO version (schema) VALUES ('1.0.2');
