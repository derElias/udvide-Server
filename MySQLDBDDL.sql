CREATE DATABASE udvide;
USE udvide;

CREATE TABLE Users (
  username VARCHAR(127) NOT NULL UNIQUE, /* might think about format constraints like COMPANY/USERNAME */
  passHash VARCHAR(255),
  role TINYINT(3) DEFAULT 0, /* 0-3: 0:Editor 1:Admin 2:Client */
  PRIMARY KEY (username)
);
CREATE TABLE Targets (
	t_id VARCHAR(32) NOT NULL UNIQUE,
  t_owner VARCHAR(127),
  serverCache BLOB,
  PRIMARY KEY (t_id),
  FOREIGN KEY (t_owner) REFERENCES Users(username)
);
CREATE TABLE Editors (
	t_id VARCHAR(47) NOT NULL,
  userID INT NOT NULL,
  CONSTRAINT Editor PRIMARY KEY (t_id, userID),
  FOREIGN KEY (userID) REFERENCES Users(userID),
	FOREIGN KEY (t_id) REFERENCES Targets(t_id)
);