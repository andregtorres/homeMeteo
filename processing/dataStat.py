#!/usr/bin/python3
import mysql.connector
import numpy as np
import datetime


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

def insertDay(mydb, day, host, data):
    mycursor = mydb.cursor()
    sql = "SELECT day FROM homeMeteoStats WHERE id = {}".format(host)
    mycursor.execute(sql)
    records = mycursor.fetchall()
    records2= np.array(records).T[0]
    rc=0
    if day not in records2:
        vals= (day, host, float(data[0][0]), float(data[0][1]), float(data[0][2]), float(data[0][3]), float(data[0][4]), float(data[0][5]), float(data[0][6]), float(data[1][0]), float(data[1][1]), float(data[1][2]), float(data[1][3]), float(data[1][4]), float(data[1][5]), float(data[1][6]))
        sql = "INSERT INTO homeMeteoStats (day, id, t_avg, t_std, t_median, t_min, t_max, t_q25, t_q75,  h_avg, h_std, h_median, h_min, h_max, h_q25, h_q75) VALUES (%s,%s, %s,%s,%s, %s,%s,%s, %s,%s,%s, %s,%s,%s, %s,%s)"
        try:
            mycursor.execute(sql, vals)
            mydb.commit()
            print("inserted day {} for host {}".format(day,host))
        except Exception as e:
            raise
            rc=1
    else:
        print("X ",day, float(data[0][0]))
    return rc

def deleteFromDb(mydb, host, interval):
    mycursor = mydb.cursor()
    sql = 'DELETE FROM homeMeteoLogs WHERE host = {} AND timestamp <= ( CURDATE() - INTERVAL {})'.format(host,interval)
    mycursor.execute(sql)
    mydb.commit()


def processDay(temp, humi):
    raw=np.array([temp, humi],dtype=np.double)
    avg=np.mean(raw, axis=-1)
    std=np.std(raw, axis=-1)
    median=np.median(raw, axis=-1)
    min=np.min(raw, axis=-1)
    max=np.max(raw, axis=-1)
    q25=np.quantile(raw, 0.25, axis=-1)
    q75=np.quantile(raw, 0.75, axis=-1)
    #print(avg, std, median, min, max, q25, q75)
    return np.array([avg, std, median, min, max, q25, q75],dtype="float32").T


# homeMeteoStats
#CREATE TABLE homeMeteoStats( day DATE, id TINYINT UNSIGNED,
#        t_avg DOUBLE, t_std DOUBLE, t_median DOUBLE, t_min DOUBLE, t_max DOUBLE, t_q25 DOUBLE, t_q75 DOUBLE
#        h_avg DOUBLE, h_std DOUBLE, h_median DOUBLE, h_min DOUBLE, h_max DOUBLE, h_q25 DOUBLE, h_q75 DOUBLE);


if __name__ == '__main__':
    db_user="ist175578"
    db_pw=""
    with open('ist_db.pw', 'r') as file:
        db_pw = file.read().rstrip()
    host=0
    #does not delete the last timeFrame period
    timeFrame="5 DAY"

    mydB=connectDB(db_user,db_pw)
    #get data older than a week
    data0=np.array(getOldData(mydB, host,"0 HOUR")).T
    #process
    times=data0[0]
    temp=data0[1]/100
    humi=data0[2]/100
    print("first timestamp: {}\n last timestamp: {}".format(times[0],times[-1]))

    curr=None
    start=0
    rc=0
    for i, time in enumerate(times):
        date=time.date()
        if curr==None:
            curr=date
        elif date!=curr:
            stats=processDay (temp[start:i],humi[start:i])
            #rc+=insertDay(mydB, curr, host, stats)
            start=i
            curr=date
     #last day
    stats=processDay (temp[start:],humi[start:])
    rc+=insertDay(mydB, curr, host, stats)


    if rc==0:
        print("Deleting from Database")
        #deleteFromDb(mydB, host, timeFrame)
    else:
        print("Found errors in {} days".format(rc))
