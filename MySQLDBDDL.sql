CREATE DATABASE udvide;
USE udvide;

CREATE TABLE Users (
  username VARCHAR(127) NOT NULL UNIQUE, /* might think about format constraints like COMPANY/USERNAME */
  passHash VARCHAR(255),
  role TINYINT(3) DEFAULT 0, /* 0-3: 0:Editor 1:Admin 2:Client 3:Developer 4:root account*/
  PRIMARY KEY (username)
);
CREATE TABLE Maps (
  name VARCHAR(127) NOT NULL UNIQUE,
  image LONGBLOB,
  PRIMARY KEY (name)
);
CREATE TABLE Targets (
  t_id INT AUTO_INCREMENT NOT NULL UNIQUE,
  vw_id VARCHAR(32),
  t_owner VARCHAR(127),
  content TEXT,
  xPos INT,
  yPos INT,
  map VARCHAR(127),
  PRIMARY KEY (t_id),
  FOREIGN KEY (t_owner) REFERENCES Users(username),
  FOREIGN KEY (map) REFERENCES Maps(name)
);
CREATE TABLE Editors (
  t_id INT NOT NULL,
  username VARCHAR(127) NOT NULL,
  CONSTRAINT Editor PRIMARY KEY (t_id, username),
  FOREIGN KEY (username) REFERENCES Users(username),
  FOREIGN KEY (t_id) REFERENCES Targets(t_id)
);
CREATE TABLE TransactionLog (
  tr_id VARCHAR(32) NOT NULL UNIQUE,
  username VARCHAR(127),
  t_id INT NOT NULL,
  PRIMARY KEY (tr_id),
  FOREIGN KEY (username) REFERENCES Users(username),
  FOREIGN KEY (t_id) REFERENCES Targets(t_id)
);
