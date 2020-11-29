CREATE SCHEMA IF NOT EXISTS odb;
USE odb;

DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS diseases;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS sources;

create table diseases (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    ConditionSNOMED INT,
    ConditionName VARCHAR (100),
    PathogenName VARCHAR (100),
    PathogenTaxonId INT
);

create table locations (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    CountryName VARCHAR (100),
    CountryIso VARCHAR (2),
    StateName VARCHAR (25),
    StateIso VARCHAR (5),
    CountyName VARCHAR (25),
    CityName VARCHAR (25)
);

create table sources (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    SourceName VARCHAR (100)
);

create table cases (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    PeriodStart DATE,
    PeriodEnd DATE,
    DiseaseId INT NOT NULL,
    LocationId INT NOT NULL,
    SourceId INT NOT NULL,
    Fatalities BOOL,
    CountValue INT,
    FOREIGN KEY (DiseaseId) references diseases (Id),
    FOREIGN KEY (LocationId) references locations (Id),
    FOREIGN KEY (SourceId) references sources (Id)
);

