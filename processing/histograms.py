#!/usr/bin/python3
import mysql.connector
import numpy as np
import datetime
import matplotlib.pyplot as plt

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


class histogramData:
    def __init__(self,date, temp, humi):
        self.date=date
        self.temp=temp
        self.humi=humi
        self.t_bin_w=0.2 #deg
        self.h_bin_w=1 #%
        self.t_min=(np.min(temp)//self.t_bin_w)*self.t_bin_w
        self.t_max=(np.max(temp)//self.t_bin_w+1)*self.t_bin_w
        self.h_min=(np.min(humi)//self.h_bin_w)*self.h_bin_w
        self.h_max=(np.max(humi)//self.h_bin_w+1)*self.h_bin_w
        self.t_nBins=int((self.t_max-self.t_min)/self.t_bin_w)+1
        self.t_bins=np.linspace(self.t_min,self.t_max,self.t_nBins, endpoint=True)
        self.t_hist,bins=np.histogram(temp, self.t_bins)
        self.h_nBins=int((self.h_max-self.h_min)/self.h_bin_w)+1
        self.h_bins=np.linspace(self.h_min,self.h_max,self.h_nBins, endpoint=True)
        self.h_hist,bins=np.histogram(humi, self.h_bins)

    def bigPlot(self, full=True, colors=True, save=""):
        fig,ax=plt.subplots(nrows=2,ncols=2, figsize=(6,6), sharex=False, sharey=False, width_ratios=[0.8,0.2], height_ratios=[0.2,0.8])
        fig.delaxes(ax[0][1])
        ax[1][0].plot(self.temp, self.humi, "k.")
        ax[0][0].stairs(self.t_hist, self.t_bins, color="k", fill=True)
        ax[1][1].stairs(self.h_hist,self.h_bins, color="k", orientation='horizontal', fill=True)
        #axes
        if full:
            hlims=(30,90)
            tlims=(10,35)
            clims=(0,60*24/2)
        else:
            hlims=(self.h_min, self.h_max)
            tlims=(self.t_min, self.t_max)
            clims=(0,np.max([np.max(self.t_hist), np.max(self.h_hist)]))
        ax[1][0].set_ylim(hlims)
        ax[1][0].set_xlim(tlims)
        ax[0][0].set_ylim(clims)
        ax[0][0].set_xlim(tlims)
        ax[1][1].set_ylim(hlims)
        ax[1][1].set_xlim(clims)
        ax[0][0].set_yticks([])
        ax[1][1].set_xticks([])
        ax[0][0].xaxis.tick_top()
        ax[1][1].yaxis.tick_right()
        #colors
        if colors:
            a1=0.05
            ax[1][0].fill_between([*tlims],hlims[0],40, color="r", alpha=a1)
            ax[1][0].fill_between([*tlims],40,60, color="g", alpha=a1)
            ax[1][0].fill_between([*tlims],60,hlims[-1], color="r", alpha=a1)
            ax[1][1].fill_between([*clims],hlims[0],40, color="r", alpha=2*a1)
            ax[1][1].fill_between([*clims],40,60, color="g", alpha=2*a1)
            ax[1][1].fill_between([*clims],60,hlims[-1], color="r", alpha=2*a1)
            #
            ax[1][0].fill_betweenx([*hlims],tlims[0],20, color="r", alpha=a1)
            ax[1][0].fill_betweenx([*hlims],20,25, color="g", alpha=a1)
            ax[1][0].fill_betweenx([*hlims],25,tlims[-1], color="r", alpha=a1)
            ax[0][0].fill_betweenx([*clims],tlims[0],20, color="r", alpha=2*a1)
            ax[0][0].fill_betweenx([*clims],20,25, color="g", alpha=2*a1)
            ax[0][0].fill_betweenx([*clims],25,tlims[-1], color="r", alpha=2*a1)
        #grids
        ax[1][0].grid()
        ax[0][0].grid()
        ax[1][1].grid()
        #lables
        ax[1][0].set_xlabel("Temperature [$^o$C]")
        ax[1][0].set_ylabel("Rel. humidity [%]")
        fig.suptitle(self.date)
        fig.tight_layout()
        if save != "":
            if full:
                plt.savefig(save+"_full_"+self.date.strftime("%Y-%m-%d")+".png")
            else:
                plt.savefig(save+"_"+self.date.strftime("%Y-%m-%d")+".png")

if __name__ == '__main__':
    db_user="ist175578"
    db_pw=""
    with open('ist_db.pw', 'r') as file:
        db_pw = file.read().rstrip()
    host=0

    mydB=connectDB(db_user,db_pw)
    data0=np.array(getOldData(mydB, host,"0 DAY")).T
    #process
    times=data0[0]
    temp=data0[1]/100
    humi=data0[2]/100
    print("first timestamp: {}\n last timestamp: {}".format(times[0],times[-1]))

    #divide in days
    curr=None
    start=0
    rc=0
    for i, time in enumerate(times):
        date=time.date()
        if curr==None:
            curr=date
        elif date!=curr:
            hist=histogramData(curr,temp[start:i],humi[start:i])
            #plt.figure()
            #plt.stairs(hist.t_hist,hist.t_bins)
            hist.bigPlot(full=True, save="../plots/homeMeteo")
            hist.bigPlot(full=False, save="../plots/homeMeteo")
            start=i
            curr=date
     #last day
    hist=histogramData(curr,temp[start:],humi[start:])
    hist.bigPlot(full=True, save="../plots/homeMeteo")
    hist.bigPlot(full=False, save="../plots/homeMeteo")
