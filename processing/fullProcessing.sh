#!/bin/bash

#####OLD METHOD USING MATPLOTLIB
#python /home/agtorres/homeMeteo/processing/histogram_requests.py
#cd /home/agtorres/homeMeteo/plots
#./ftpPlots.sh
####

source /home/agtorres/homeMeteo/processing/httpConn.pw
#curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=0' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/binData.php
#curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=0' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/dataStat.php
#curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=0' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/deleteLogs.php
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=1' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/binData.php
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=1' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/dataStat.php
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=1' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/deleteLogs.php
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=2' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/binData.php
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=2' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/dataStat.php
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=2' --user $USER:$PASSWORD http://homemeteo.atorres.eu/API/deleteLogs.php
