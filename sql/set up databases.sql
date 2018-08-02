CREATE DATABASE GardenWeb;
USE GardenWeb;

CREATE TABLE Readings (
  Sensor varchar(255),
  ReadingTimeDate datetime,
  Reading decimal(6,2)
);



CREATE USER 'rasp'@'%' IDENTIFIED BY 'rasprasp';
GRANT ALL PRIVILEGES ON * . * TO 'rasp'@'%';


CREATE USER 'website'@'%' IDENTIFIED BY 'rasprasp';
GRANT SELECT ON * . * TO 'website'@'%';
