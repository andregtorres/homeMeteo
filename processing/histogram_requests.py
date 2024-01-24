#!/usr/bin/python3
import numpy as np
import datetime
import matplotlib.pyplot as plt
import requests
from pathlib import Path
import json
from sys import argv

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
                fname=save+"homeMeteo"+"_full_"+self.date+".png"
            else:
                fname=save+"homeMeteo"+"_"+self.date+".png"
            plt.savefig(fname)
            return fname

if __name__ == '__main__':
    #definition
    url = 'http://homemeteo.atorres.eu/exportData.php'
    loc1=Path(__file__).parent.parent/"plots"
    loc = str(loc1)+"/"

    #parameters
    host=0       #default to 0
    startDate="" #dfault to last date not on Stats table
    if len(argv)>1:
        inDate=argv[1]
        if isinstance(inDate, str):
            if len(inDate)==10: #format yyyy-mm-dd
                startDate=inDate


    myobj = {'id': str(host)}
    if startDate!= "":
        myobj.update({"startDate": startDate})
    x = requests.post(url, data = myobj,timeout=3)
    try:
        data=x.json()
    except:
        print("ERROR parsing request")
        exit(-1)

    if data is None:
        print("No days to plot")
        exit(0)

    for each in data:
        print(each["seq"])
        values = json.loads(each["payload"])
        time=values["time"]
        day=values["day"]
        temp=[int(numeric_string)/100. for numeric_string in values["temp"]]
        humi=[int(numeric_string)/100. for numeric_string in values["humi"]]


        hist=histogramData(day,temp,humi)
        plot=hist.bigPlot(full=True, save=loc)
        #hist.bigPlot(full=False, save=loc)
        with open(loc+"newPlots.txt", 'a') as file:
            file.write(plot+"\n")
