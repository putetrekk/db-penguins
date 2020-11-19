CREATE SCHEMA IF NOT EXISTS adb;
USE adb;

DROP TABLE IF EXISTS adb.fact_cases;
DROP TABLE IF EXISTS adb.fact_fatalities;
DROP TABLE IF EXISTS adb.disease_dim;
DROP TABLE IF EXISTS adb.loc_dim;
DROP TABLE IF EXISTS adb.time_dim;

CREATE TABLE time_dim (
     timeId INT PRIMARY KEY AUTO_INCREMENT,
     year INT NOT NULL UNIQUE
);

CREATE TABLE  loc_dim (
    locId INT PRIMARY KEY AUTO_INCREMENT,
    locName VARCHAR (25) UNIQUE
);

CREATE TABLE disease_dim (
    diseaseId INT PRIMARY KEY,
    diseaseName VARCHAR (100) UNIQUE
);

CREATE TABLE  fact_cases (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    timeId INT NOT NULL,
    locId INT NOT NULL,
    diseaseId INT NOT NULL,
    caseCount INT CHECK (value >= 0),

    FOREIGN KEY (timeId) REFERENCES time_dim (timeId),
    FOREIGN KEY (locId) REFERENCES loc_dim (locId),
    FOREIGN KEY (diseaseId) REFERENCES disease_dim (diseaseId)
);

CREATE TABLE  fact_fatalities (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    timeId INT NOT NULL,
    locId INT NOT NULL,
    diseaseId INT NOT NULL,
    fatalitiesCount INT CHECK (value >= 0),

    FOREIGN KEY (timeId) REFERENCES time_dim (timeId),
    FOREIGN KEY (locId) REFERENCES loc_dim (locId),
    FOREIGN KEY (diseaseId) REFERENCES disease_dim (diseaseId)
);
