--
-- COMMON DATABASE - schema 1.0.0
--

CREATE FUNCTION _ (VARCHAR) RETURNS VARCHAR AS 'SELECT $1' LANGUAGE 'SQL';

CREATE SEQUENCE notice_seq MINVALUE 0;
CREATE SEQUENCE topic_seq  MINVALUE 0;
CREATE SEQUENCE users_seq  MINVALUE 0;
CREATE SEQUENCE position_seq  MINVALUE 0;

CREATE TABLE log (
  scope           CHAR,
  op              CHAR(2),
  user_id         INT,
  logged          TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone,
  level           CHAR,
  info            VARCHAR(150)
);

-- scope: [c] common system
--        [n] nou-rau
--        [r] rau-tu
-- level: [f] fatal
--        [e] error
--        [w] warning
--        [i] information
--        [d] debug
-- op: [tc] topic created
--     [td] topic deleted
--     [tu] topic updated
--     [ua] user approved
--     [uc] user created
--     [ud] user deleted
--     [ul] user login
--     [um] password reminded
--     [up] password changed
--     [ur] user rejected
--     [uu] user updated

CREATE TABLE notice (
  id              INT DEFAULT NEXTVAL('notice_seq'),
  subject         VARCHAR(100),
  notice          VARCHAR(1000), -- text box
  user_id         INT,
  posted          TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone,
  PRIMARY KEY (id)
);

CREATE TABLE topic (
  id              INT DEFAULT NEXTVAL('topic_seq'),
  name            VARCHAR(100),
  description     VARCHAR(150),
  parent_id       INT DEFAULT '0',
  maintainer_id   INT,
  options         VARCHAR(2000) DEFAULT '', -- serialized array
  created         TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone,
  position        INT DEFAULT NEXTVAL('position_seq'),
  PRIMARY KEY (id)
);

CREATE TABLE topic_path (
  topic_id        INT,
  parent_ids      VARCHAR(200), -- serialized list
  parent_names    VARCHAR(2000), -- serialized list
  changed         CHAR DEFAULT 'n',
  PRIMARY KEY (topic_id)
);

-- changed: [y] yes
--          [n] no
--          [-] being processed

CREATE TABLE users (
  id              INT DEFAULT NEXTVAL('users_seq'),
  username        VARCHAR(10) UNIQUE,
  password        VARCHAR(10),
  name            VARCHAR(100),
  email           VARCHAR(50),
  info            VARCHAR(500) DEFAULT '', -- text box, optional
  options         VARCHAR(2000) DEFAULT '', -- serialized array
  level           CHAR DEFAULT '1',
  accessed        TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone,
  PRIMARY KEY (id)
);

-- level: [1] normal user
--        [2] maintainer
--        [3] administrator

CREATE TABLE user_registration (
  email           VARCHAR(50),
  code            INT,
  motive          VARCHAR(250) DEFAULT '', -- text box
  status          CHAR DEFAULT 'w',
  requested       TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone,
  PRIMARY KEY (email)
);

-- status: [a] approved
--         [w] waiting for approval

CREATE TABLE version (
  schema          VARCHAR(10)
);
