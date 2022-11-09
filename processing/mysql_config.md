# Steps to configure the db

sudo mysql -uroot
select * from mysql.user;
CREATE USER 'agtorres'@'localhost' IDENTIFIED BY 'pw__goes_here';
The wildcard '%' can be used instead of 'localhost' to connect from any host.
GRANT ALL PRIVILEGES ON *.* TO agtorres@'localhost';

on the user:
CREATE DATABASE homeMeteo;
USE homeMeteo
CREATE TABLE logs ( host TINYINT UNSIGNED, timestamp TIMESTAMP);
ALTER TABLE logs ADD COLUMN temp TINYINT UNSIGNED AFTER timestamp;
ALTER TABLE logs ADD COLUMN humi TINYINT UNSIGNED AFTER temp;


 homeMeteoStats
CREATE TABLE homeMeteoStats( day DATE, id TINYINT UNSIGNED,
        t_avg DOUBLE, t_std DOUBLE, t_median DOUBLE, t_min DOUBLE, t_max DOUBLE, t_q25 DOUBLE, t_q75 DOUBLE,
        h_avg DOUBLE, h_std DOUBLE, h_median DOUBLE, h_min DOUBLE, h_max DOUBLE, h_q25 DOUBLE, h_q75 DOUBLE);
