CREATE DATABASE udvide;
USE udvide;

CREATE TABLE Users (
  userID INT NOT NULL AUTO_INCREMENT, 
  salt VARCHAR(63),
  passHash VARCHAR(255),
  username VARCHAR(127) NOT NULL UNIQUE, 
  role TINYINT(3) DEFAULT 0, /* 0-3: 0:Editor 1:Admin 2:Kunde */
  PRIMARY KEY (userID)
);
CREATE TABLE Targets (
	t_id VARCHAR(47) NOT NULL,
    t_owner INT,
    serverCache BLOB,
    PRIMARY KEY (t_id)
);
CREATE TABLE Editors (
	t_id VARCHAR(47) NOT NULL,
    userID INT NOT NULL,
    CONSTRAINT Editor PRIMARY KEY (t_id, userID),
    FOREIGN KEY (userID) REFERENCES Users(UserID),
	FOREIGN KEY (t_id) REFERENCES Targets(t_id)
);