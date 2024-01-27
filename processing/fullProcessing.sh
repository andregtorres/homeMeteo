#!/bin/bash
python /home/agtorres/homeMeteo/processing/histogram_requests.py
cd /home/agtorres/homeMeteo/plots
./ftpPlots.sh
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=0' http://homemeteo.atorres.eu/dataStat.php
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'id=0' http://homemeteo.atorres.eu/deleteLogs.php
