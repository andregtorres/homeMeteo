#!/usr/bin/python3
import plotly.graph_objects as go
from plotly.subplots import make_subplots
import mysql.connector
import numpy as np

db_host="db.tecnico.ulisboa.pt"

def connectDB(user, password):
    mydb = mysql.connector.connect(
        host=db_host,
        user=user,
        password=password,
        database="ist175578"
        )
    return mydb

def plot(times,temp,humi):
    data=go.Scatter(
        x=times,
        y=temp,
        mode='lines+markers',
        name='Temperature',
        marker_color=temp,
        )
    data2=go.Scatter(
        x=times,
        y=humi,
        mode='lines+markers',
        name='Relative humidity',
        marker_color=humi,
        )
    #fig = go.Figure([data,data2])
    fig = make_subplots(rows=2, cols=1, shared_xaxes=True)
    fig.add_trace(data, row=1,col=1)
    fig.add_trace(data2, row=2,col=1)

    # Update yaxis properties
    fig.update_yaxes(title_text="Temperature [ºC]", row=1, col=1)
    fig.update_yaxes(title_text="Relative humidity [%]", row=2, col=1)

    return fig

def getDB(mydb, host, interval):
    mycursor = mydb.cursor()
    sql = "SELECT timestamp, temp, humi FROM homeMeteoLogs WHERE host = {} AND timestamp >= ( CURDATE() - INTERVAL {})".format(host,interval)
    mycursor.execute(sql)
    records = mycursor.fetchall()
    return(records)

if __name__ == '__main__':
    db_user="" #CHANGE HERE
    db_pw="" #CHANGE HERE
    mydB=connectDB(db_user,db_pw)

    #Last 2 days
    data0=np.array(getDB(mydB, 0,"1 DAY")).T

    #process
    times=data0[0]
    temp=data0[1]/100
    humi=data0[2]/100
    print("first timestamp: {}\n last timestamp: {}".format(times[0],times[-1]))
    #plot
    fig= plot(times,temp,humi)

    with open('/afs/.ist.utl.pt/users/7/8/ist175578/web/homeMeteo/plots/lastDay.html', 'w') as f:
        f.write(fig.to_html(include_plotlyjs='cdn', full_html=False))

    #last month
    data0=np.array(getDB(mydB, 0,"1 WEEK")).T

    #process
    times=data0[0]
    temp=data0[1]/100
    humi=data0[2]/100
    print("first timestamp: {}\n last timestamp: {}".format(times[0],times[-1]))
    #plot
    fig= plot(times,temp,humi)

    with open('/afs/.ist.utl.pt/users/7/8/ist175578/web/homeMeteo/plots/lastWeek.html', 'w') as f:
        f.write(fig.to_html(include_plotlyjs='cdn', full_html=False))
