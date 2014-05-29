--
-- NOU-RAU DATABASE - schema 1.0.1
--

CREATE TABLE nr_document_update (
  id              INT,
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
-- remote: [y] yes
--         [n] no


