#!/usr/bin/python3
import mysql.connector
import numpy as np
import datetime
import csv

db_host="db.tecnico.ulisboa.pt"

def connectDB(user, password):
    mydb = mysql.connector.connect(
        host=db_host,
        user=user,
        password=password,
        database="ist175578"
        )
    return mydb


def getOldData(mydb, host, interval):
    mycursor = mydb.cursor()
    sql = "SELECT timestamp, temp, humi FROM homeMeteoLogs WHERE host = {} AND timestamp <= ( CURDATE() - INTERVAL {})".format(host,interval)
    mycursor.execute(sql)
    records = mycursor.fetchall()
    return(records)


if __name__ == '__main__':
    db_user="ist175578"
    db_pw=""
    with open('ist_db.pw', 'r') as file:
        db_pw = file.read().rstrip()
    host=0

    mydB=connectDB(db_user,db_pw)
    #get all data til last full day
    data0=np.array(getOldData(mydB, host,"0 HOUR")).T
    #process
    times=data0[0]
    temp=data0[1]/100
    humi=data0[2]/100
    data=[[time.strftime("%d-%m-%Y-%H:%M:%S"),t,h] for time, t, h in zip(times,temp,humi)]
    print("first timestamp: {}\n last timestamp: {}".format(times[0],times[-1]))
    fname="data/data_{}_{}_{}.txt".format(host,times[0].strftime("%d-%m-%Y"),times[-1].strftime("%d-%m-%Y"))
    header=["#homeMeteo data","#first timestamp: {}".format(times[0]),"#last timestamp: {}".format(times[-1]),"#Time,Temperature,RelHumidity","#%d-%m-%Y-%H:%M:%S degC %"]
    print("Saving data as {}".format(fname))
    with open(fname, 'w', encoding='UTF8', newline='\n') as f:
        writer = csv.writer(f)
        for h in header:
            writer.writerow([h])
        writer.writerows(data)
