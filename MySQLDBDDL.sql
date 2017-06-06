CREATE DATABASE udvide;
USE udvide;

CREATE TABLE Users (
  username VARCHAR(127) NOT NULL UNIQUE, /* might think about format constraints like COMPANY/USERNAME */
  deleted BOOLEAN,
  passHash VARCHAR(255),
  role TINYINT(3) NOT NULL,
  PRIMARY KEY (username)
);
CREATE TABLE Maps (
  name VARCHAR(127) NOT NULL UNIQUE,
  image LONGBLOB,
  PRIMARY KEY (name)
);
CREATE TABLE Targets (
  t_id INT AUTO_INCREMENT NOT NULL UNIQUE,
  deleted BOOLEAN,
  vw_id VARCHAR(32),
  t_owner VARCHAR(127),
  content TEXT,
  xPos INT,
  yPos INT,
  map VARCHAR(127),
  PRIMARY KEY (t_id),
  FOREIGN KEY (t_owner) REFERENCES Users(username) ON DELETE SET NULL,
  FOREIGN KEY (map) REFERENCES Maps(name) ON DELETE SET NULL
);
CREATE TABLE Editors (
  t_id INT NOT NULL,
  username VARCHAR(127) NOT NULL,
  CONSTRAINT Editor PRIMARY KEY (t_id, username),
  FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE,
  FOREIGN KEY (t_id) REFERENCES Targets(t_id) ON DELETE CASCADE
);
CREATE TABLE TransactionLog (
  tr_id VARCHAR(32) NOT NULL UNIQUE,
  username VARCHAR(127),
  t_id INT NOT NULL,
  PRIMARY KEY (tr_id),
  FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE, /* transactions and editor permissions are logged until a certain time after deletion by deactivating them instead */
  FOREIGN KEY (t_id) REFERENCES Targets(t_id) ON DELETE CASCADE
);
