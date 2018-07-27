CREATE DATABASE GardenWeb;
USE GardenWeb;

CREATE TABLE Readings (
  Sensor varchar(255),
  ReadingTimeDate datetime,
  Reading decimal(6,2)
);