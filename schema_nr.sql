--
-- NOU-RAU DATABASE - schema 1.0.1
--

CREATE SEQUENCE nr_category_seq MINVALUE 0;
CREATE SEQUENCE nr_format_seq   MINVALUE 0;
CREATE SEQUENCE nr_document_seq MINVALUE 0;

CREATE TABLE nr_category (
  id              INT DEFAULT NEXTVAL('nr_category_seq'),
  name            VARCHAR(50) UNIQUE,
  description     VARCHAR(150),
  max_size        INT,
  PRIMARY KEY (id)
);

CREATE TABLE nr_category_format (
  category_id     INT,
  format_id       INT,
  PRIMARY KEY (category_id,format_id)
);

CREATE TABLE nr_document (
  id              INT DEFAULT NEXTVAL('nr_document_seq'),
  title           VARCHAR(250),
  author          VARCHAR(250),
  email           VARCHAR(150), -- optional
  keywords        VARCHAR(250), -- text box
  description     VARCHAR(1000), -- text box, optional
  code            VARCHAR(50) UNIQUE,
  info            VARCHAR(1000), -- text box, optional
  topic_id        INT,
  owner_id        INT,
  category_id     INT,
  status          CHAR DEFAULT 'i',
  filename        VARCHAR(150),
  size            INT,
  format_id       INT,
  new_filename    VARCHAR(150),
  new_size        INT,
  new_format_id   INT,
  visits          INT DEFAULT '0',
  downloads       INT DEFAULT '0',
  created         TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone,
  updated         TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone,
  remote          CHAR DEFAULT 'n',
  PRIMARY KEY (id)
);

-- status: [a] archived
--         [d] deleted
--         [i] incoming
--         [v] needs verification
--         [w] waiting for approval
--         [u] needs verification of the new document
--         [p] waiting for approval of the new document
-- remote: [y] yes
--         [n] no

CREATE TABLE nr_document_queue (
  op              CHAR,
  document_id     INT
);

-- op: [u] updated
--     [d] deleted
--     [-] being processed

CREATE TABLE nr_format (
  id              INT DEFAULT NEXTVAL('nr_format_seq'),
  name            VARCHAR(50) UNIQUE,
  type            VARCHAR(20),
  subtype         VARCHAR(40),
  extension       VARCHAR(10),
  icon            CHAR(3),
  compress        CHAR,
  verify          CHAR,
  PRIMARY KEY (id)
);

-- compress: [y] yes
--           [n] no
-- verify: [y] yes
--         [n] no

CREATE TABLE nr_htdig_status (
  running         CHAR,
  updated         TIMESTAMP WITHOUT TIME ZONE DEFAULT ('now'::text)::timestamp(6) with time zone
);

-- running: [y] yes
--          [n] no

CREATE TABLE nr_topic_category (
  topic_id        INT,
  category_id     INT,
  PRIMARY KEY (topic_id,category_id)
);

CREATE TABLE nr_version (
  schema          VARCHAR(10)
);

-- log op: [da] document approved
--         [dc] document created
--         [dd] document deleted
--         [de] incoming document expired and was removed
--         [di] incoming document
--         [dr] document rejected
--         [du] document updated
--         [dv] document verified
--         [ir] search index rebuilt
--         [iu] search index updated
--         [ie] indexing error
--         [is] search index statistics
