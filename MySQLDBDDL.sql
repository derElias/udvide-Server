DROP DATABASE IF EXISTS udvide;
CREATE DATABASE udvide;
USE udvide;

CREATE TABLE Users (
  username VARCHAR(127) NOT NULL UNIQUE, /* might think about format constraints like COMPANY/USERNAME */
  deleted BOOLEAN DEFAULT FALSE,
  passHash VARCHAR(255),
  role TINYINT(3) DEFAULT 0,
  targetCreateLimit INT DEFAULT 0,
  pluginData LONGTEXT,
  PRIMARY KEY (username)
);
CREATE TABLE Maps (
  name VARCHAR(127) NOT NULL UNIQUE,
  image LONGBLOB,
  pluginData LONGTEXT,
  PRIMARY KEY (name)
);
CREATE TABLE Targets (
  deleted BOOLEAN DEFAULT FALSE,
  owner VARCHAR(127),
  content TEXT,
  xPos INT DEFAULT 0,
  yPos INT DEFAULT 0,
  map VARCHAR(127),
  vw_id VARCHAR(32),
  image LONGBLOB,
  name VARCHAR(127) NOT NULL UNIQUE,
  pluginData LONGTEXT,
  PRIMARY KEY (name),
  FOREIGN KEY (owner) REFERENCES Users(username) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (map) REFERENCES Maps(name) ON DELETE SET NULL ON UPDATE CASCADE
);
CREATE TABLE Editors (
  tName VARCHAR(127) NOT NULL,
  uName VARCHAR(127) NOT NULL,
  CONSTRAINT Editor PRIMARY KEY (tName, uName),
  FOREIGN KEY (uName) REFERENCES Users(username) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (tName) REFERENCES Targets(name) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE TransactionLog (
  tr_id VARCHAR(32) NOT NULL UNIQUE,
  uName VARCHAR(127),
  tName VARCHAR(127) NOT NULL,
  PRIMARY KEY (tr_id),
  FOREIGN KEY (uName) REFERENCES Users(username) ON DELETE CASCADE ON UPDATE CASCADE, /* transactions and editor permissions are logged until a certain time after deletion by deactivating them instead */
  FOREIGN KEY (tName) REFERENCES Targets(name) ON DELETE CASCADE ON UPDATE CASCADE
);
