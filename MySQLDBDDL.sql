CREATE DATABASE udvide;
USE udvide;

CREATE TABLE Users (
  username VARCHAR(127) NOT NULL UNIQUE, /* might think about format constraints like COMPANY/USERNAME */
  passHash VARCHAR(255),
  role TINYINT(3) DEFAULT 0, /* 0-3: 0:Editor 1:Admin 2:Client 3:Developer 4:root account*/
  PRIMARY KEY (username)
);
CREATE TABLE Targets (
  t_id LONG AUTO_INCREMENT NOT NULL UNIQUE,
  vw_id VARCHAR(32) NOT NULL UNIQUE,
  t_owner VARCHAR(127),
  content TEXT,
  xpos INT,
  ypos INT,
  map INT,
  PRIMARY KEY (t_id),
  FOREIGN KEY (t_owner) REFERENCES Users(username),
  FOREIGN KEY (map) REFERENCES Maps(map_id)
);
CREATE TABLE Editors (
  t_id VARCHAR(32) NOT NULL,
  username VARCHAR(127) NOT NULL,
  CONSTRAINT Editor PRIMARY KEY (t_id, username),
  FOREIGN KEY (username) REFERENCES Users(username),
  FOREIGN KEY (t_id) REFERENCES Targets(t_id)
);
CREATE TABLE TransactionLog (
  tr_id VARCHAR(32) NOT NULL UNIQUE,
  username VARCHAR(127),
  t_id VARCHAR(32) NOT NULL,
  PRIMARY KEY (tr_id),
  FOREIGN KEY (username) REFERENCES Users(username),
  FOREIGN KEY (t_id) REFERENCES Targets(t_id)
);
CREATE TABLE Maps (
  map_id VARCHAR(32) NOT NULL UNIQUE,
  image LONGBLOB,
  name VARCHAR(127),
  PRIMARY KEY (map_id)
);
