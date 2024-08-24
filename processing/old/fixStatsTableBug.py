#!/usr/bin/python3
import mysql.connector
import numpy as np
import datetime
#previous dataStat script was adding the dat wrong. specifically, the data was offset by 1 days

db_host="db.tecnico.ulisboa.pt"

def connectDB(user, password):
    mydb = mysql.connector.connect(
        host=db_host,
        user=user,
        password=password,
        database="ist175578"
        )
    return mydb


def getData(mydb, host):
    mycursor = mydb.cursor()
    sql = "SELECT day FROM homeMeteoStats WHERE id = {}".format(host)
    mycursor.execute(sql)
    records = mycursor.fetchall()
    return(records)


def modifyData(mydb, host, offset, dates):
    mycursor = mydb.cursor()
    for date in dates:
        newDate=date+datetime.timedelta(days=offset)
        print("old:",date,"  new:", newDate)
        vals= (newDate, date)
        sql = "UPDATE homeMeteoStats SET day = %s WHERE day = %s"
        print(sql,vals)
        mycursor.execute(sql, vals)


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

    #offset to apply in days
    offset=1

    mydB=connectDB(db_user,db_pw)
    #get data
    data0=np.array(getData(mydB, host)).T
    times=data0[0]
    print("first timestamp: {}\n last timestamp: {}".format(times[0],times[-1]))
    print("---")
    #modifyData(mydB, host,offset,times)
    #data0=np.array(getData(mydB, host)).T
    #times=data0[0]
    #print("first timestamp: {}\n last timestamp: {}".format(times[0],times[-1]))
    #print("---")
    for date in data0[0]:
        print(date)

    print("OK? (y/n)")
    if input()=="y":
        #mydb.commit()
        print("commited")
    else:
        print("did not commit")
