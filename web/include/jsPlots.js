const colors = [
  '#5988ff',
  '#ff8559',
  '#59ff8b',
  '#bd59ff',
  '#17becf'
];
const colorsTransp = [
  'rgba(89, 136, 255,0.2)',
  'rgba(255, 133, 89,0.2)',
  'rgba(89, 255, 139,0.2)',
  'rgba(189, 89, 255,0.2)',
  'rgba(23, 190, 207,0.2)'
];
const cs1 = [
  ['0.0',                 'rgba( 89, 136, 255,0.5)'],
  ['0.14285714285714285', 'rgba(115, 154, 255,0.5)'],
  ['0.2857142857142857',  'rgba(140, 173, 255,0.5)'],
  ['0.42857142857142855', 'rgba(166, 191, 255,0.5)'],
  ['0.5714285714285714',  'rgba(191, 209, 255,0.5)'],
  ['0.7142857142857143',  'rgba(217, 227, 255,0.5)'],
  ['0.8571428571428571',  'rgba(242, 246, 255,0.5)'],
  ['1.0',                 'rgba(255, 255, 255,0.5)']
];
const cs2 = [
  ['0.0',                 'rgba(255, 133, 89 ,0.5)'],
  ['0.14285714285714285', 'rgba(255, 152, 115,0.5)'],
  ['0.2857142857142857',  'rgba(255, 170, 140,0.5)'],
  ['0.42857142857142855', 'rgba(255, 189, 166,0.5)'],
  ['0.5714285714285714',  'rgba(255, 208, 191,0.5)'],
  ['0.7142857142857143',  'rgba(255, 227, 217,0.5)'],
  ['0.8571428571428571',  'rgba(255, 245, 242,0.5)'],
  ['1.0',                 'rgba(255, 255, 255,0.5)']
];
const colorscales = [
  cs1,
  cs2,
  cs1,
  cs1,
  cs1
];

function doPlot(divName, Nitems_, times_, temp_, humi_, labels_, plots_){
  var data=[];
  for (var i = 0; i < Nitems_ ; i++) {
    var trace1 ={
        x:times_[i],
        y:temp_[i],
        mode:'lines+markers',
        name:labels_[i],
        marker: {
          color:colors[i],
        },
    };
    var trace2 ={
        x:times_[i],
        y:humi_[i],
        mode:'lines+markers',
        name: labels_[i],
        yaxis: 'y2',
        marker: {
          color:colors[i],
        },
        showlegend: false,
    };
    if (plots_[i]){
      data.push(trace1,trace2);
    }

  }
  var layout = {
    grid: {rows: 2, columns: 1},
    shared_xaxes: true,
    margin: { t: 5, b:20 },
    yaxis: {
      title:{text: "Temperature [ºC]"},
      row:1,
      col:1,
    },
    yaxis2: {
      title:{text: "Relative humidity [%]"},
      row:2,
      col:1,
    },
  };
  Plotly.newPlot(divName, data, layout);
}

function doPlotStats(divName, N_devices, stats, labels, plots){
  var dataStats=[];
  for (var i = 0; i < N_devices ; i++) {
    var devStats=JSON.parse(stats[i.toString()])
    var trace1 ={
      x:devStats["day"],
      y:devStats["t_q25"],
      line: {color: "transparent"},
      fillcolor: colorsTransp[i],
      name: "q25",
      showlegend: false,
      type: "scatter",
      mode: 'lines',
      hoverinfo:"x+y"
    };
    var trace2 ={
      x:devStats["day"],
      y:devStats["t_avg"],
      mode:'lines+markers',
      name:labels[i],
      line: {color:colors[i]},
      marker: {color:colors[i]},
      fillcolor: colorsTransp[i],
      type: "scatter",
      fill: "tonexty",
      hoverinfo:"x+y"
    };
    var trace3 ={
      x:devStats["day"],
      y:devStats["t_q75"],
      fill: "tonexty",
      fillcolor: colorsTransp[i],
      line: {color: "transparent"},
      name: "q75",
      showlegend: false,
      type: "scatter",
      mode: 'lines',
      hoverinfo:"x+y"
    };

    var trace4 ={
      x:devStats["day"],
      y:devStats["h_q25"],
      line: {color: "transparent"},
      fillcolor: colorsTransp[i],
      name: "q25",
      showlegend: false,
      type: "scatter",
      mode: 'lines',
      yaxis: 'y2',
      hoverinfo:"x+y"
    };
    var trace5 ={
      x:devStats["day"],
      y:devStats["h_avg"],
      mode:'lines+markers',
      yaxis: 'y2',
      line: {color:colors[i]},
      marker: {color:colors[i]},
      fillcolor: colorsTransp[i],
      showlegend: false,
      type: "scatter",
      fill: "tonexty",
      hoverinfo:"x+y"
    };
    var trace6 ={
      x:devStats["day"],
      y:devStats["h_q75"],
      fill: "tonexty",
      fillcolor: colorsTransp[i],
      line: {color: "transparent"},
      name: "q75",
      showlegend: false,
      type: "scatter",
      mode: 'lines',
      yaxis: 'y2',
      hoverinfo:"x+y"
    };
    if (plots[i]){
      dataStats.push(trace1,trace2,trace3,trace4,trace5,trace6);
    }
  }
  var layout = {
    grid: {rows: 2, columns: 1},
    shared_xaxes: true,
    margin: { t: 5, b:20 },
    yaxis: {
      title:{text: "Temperature [ºC]"},
      row:1,
      col:1,
    },
    yaxis2: {
      title:{text: "Relative humidity [%]"},
      row:2,
      col:1,
    },
    hovermode:'closest',
  };
  Plotly.newPlot('plotlyStatsDiv', dataStats, layout);
}

function plotMeasurements(divName, times, temp, humi){
  var trace1 ={
      x:times,
      y:temp,
      mode:'lines+markers',
      name:'Temperature',
  };
  var trace2 ={
      x:times,
      y:humi,
      mode:'lines+markers',
      name:'Relative humidity',
      yaxis: 'y2',
  };

  var data = [ trace1, trace2];

  var layout = {
    grid: {rows: 2, columns: 1},
    shared_xaxes: true,
    yaxis: {
      title:{text: "Temperature [ºC]"},
      row:1,
      col:1,
    },
    yaxis2: {
      title:{text: "Relative humidity [%]"},
      row:2,
      col:1,
    },
    hovermode:'x unified',
  };
  Plotly.newPlot(divName, data, layout);
}

function plotStats(divName, days, t_avg, t_q25, t_q75, h_avg, h_q25, h_q75){
  var d3colors = Plotly.d3.scale.category10();

  var trace1 ={
    //x:days2.concat(days,days.reverse()),
    x:days,
    y:t_q25,
    //fill: "toself",
    line: {color: "transparent"},
    fillcolor: "rgba(31, 119, 180,0.2)",
    name: "q25",
    showlegend: false,
    type: "scatter",
    mode: 'lines',
  };
  var trace2 ={
      x:days,
      y:t_avg,
      mode:'lines+markers',
      name:'Temperature',
      line: {color:d3colors(0)},
      marker: {color:d3colors(0)},
      fillcolor: "rgba(31, 119, 180,0.2)",
      type: "scatter",
      fill: "tonexty",
  };
  var trace3 ={
    //x:days2.concat(days,days.reverse()),
    x:days,
    y:t_q75,
    fill: "tonexty",
    fillcolor: "rgba(31, 119, 180,0.2)",
    line: {color: "transparent"},
    name: "q75",
    showlegend: false,
    type: "scatter",
    mode: 'lines',
  };

  var trace4 ={
    //x:days2.concat(days,days.reverse()),
    x:days,
    y:h_q25,
    //fill: "toself",
    line: {color: "transparent"},
    fillcolor: "rgba(255, 127, 14,0.2)",
    name: "q25",
    showlegend: false,
    type: "scatter",
    mode: 'lines',
    yaxis: 'y2',
  };
  var trace5 ={
      x:days,
      y:h_avg,
      mode:'lines+markers',
      name:'Relative humidity',
      yaxis: 'y2',
      line: {color:d3colors(1)},
      marker: {color:d3colors(1)},
      fillcolor: "rgba(255, 127, 14,0.2)",
      type: "scatter",
      fill: "tonexty",
  };
  var trace6 ={
    x:days,
    y:h_q75,
    fill: "tonexty",
    fillcolor: "rgba(255, 127, 14,0.2)",
    line: {color: "transparent"},
    name: "q75",
    showlegend: false,
    type: "scatter",
    mode: 'lines',
    yaxis: 'y2',
  };

  var data = [trace1, trace2, trace3, trace4, trace5, trace6];

  var layout = {
    grid: {rows: 2, columns: 1},
    shared_xaxes: true,
    margin: { t: 5, b:20 },
    yaxis: {
      title:{text: "Temperature [ºC]"},
      row:1,
      col:1,
    },
    yaxis2: {
      title:{text: "Relative humidity [%]"},
      row:2,
      col:1,
    },
    hovermode:'closest',
  };
  Plotly.newPlot(divName, data, layout);
}

const range = (start, stop, step = 1) =>
  Array(Math.ceil((stop - start) / step)).fill(start).map((x, y) => x + y * step)

function plotDensity(divName, date, bins, histParams){
  for (var i=0; i < bins[date].length; i++) {
    bins[date][i].map(x=>+x/100);
  }
  var tempBins=[];
  var humiBins=[];
  for(var i = 0; i < bins[date].length; i++) {
    for(var j = 0; j < bins[date][0].length; j++) {
      //console.log(bins2d[i][j]);
      tempBins[i]=(tempBins[i] || 0) + bins[date][i][j];
      humiBins[j]=(humiBins[j] || 0) + bins[date][i][j];
    }
  }
  //transpose
  bins2dT = bins[date][0].map((_, colIndex) => bins[date].map(row => row[colIndex]));
  console.log(humiBins);
  var t = range(histParams[date]["x_min"]/100,histParams[date]["x_max"]/100,histParams[date]["dx"]/100);
  var h = range(histParams[date]["y_min"]/100,histParams[date]["y_max"]/100,histParams[date]["dy"]/100);
  var tlims=[histParams[date]["x_min"]/100,histParams[date]["x_max"]/100];
  var hlims=[histParams[date]["y_min"]/100,histParams[date]["y_max"]/100];
  var clims=[0,60*24/2];
  console.log(hlims);
  var contour1 ={
    z: bins2dT,
    x: t,
    y: h,
    xaxis: 'x1',
    yaxis: 'y1',
    ncontours: 12,
    name:"",
    colorscale: 'Greys',
    reversescale: true,
    showscale: false,
    type: 'contour'
  };
  var histTemp ={
    y: tempBins,
    x: t,
    xaxis: 'x2',
    yaxis: 'y2',
    type: 'bar',
    name:"",
    marker: {
      color: 'rgb(0,0,0)'
      },
    };
  var histHumi ={
    y: h,
    x: humiBins,
    type: 'bar',
    xaxis: 'x3',
    yaxis: 'y3',
    name:"",
    orientation: 'h',
    marker: {
      color: 'rgb(0,0,0)'
      },
  };
  var hl1 ={
    y: [40,40],
    x: tlims,
    xaxis: 'x1',
    yaxis: 'y1',
    type: 'scatter',
    fill : 'tozeroy',
    mode: 'markers',
    name:"",
    fillcolor: 'rgba(255, 0, 0, 0.05)',
    marker: {
      size: 0,
      color:'rgba(0, 0, 0, 0)',
    },
  };
  var hl2 ={
    y: [60,60],
    x: tlims,
    xaxis: 'x1',
    yaxis: 'y1',
    type: 'scatter',
    fill : 'tonexty',
    mode: 'markers',
    name:"",
    fillcolor: 'rgba(0, 207, 55, 0.05)',
    marker: {
      size: 0,
      color:'rgba(0, 0, 0, 0)',
    },
  };
  var hl3 ={
    y: [hlims[1],hlims[1]],
    x: tlims,
    xaxis: 'x1',
    yaxis: 'y1',
    type: 'scatter',
    fill : 'tonexty',
    mode: 'markers',
    name:"",
    fillcolor: 'rgba(255, 0, 0, 0.05)',
    marker: {
      size: 0,
      color:'rgba(0, 0, 0, 0)',
    },
  };
  var vl1 ={
    y: hlims,
    x: [20,20],
    xaxis: 'x1',
    yaxis: 'y1',
    type: 'scatter',
    fill : 'tozerox',
    mode: 'markers',
    name:"",
    fillcolor: 'rgba(255, 0, 0, 0.05)',
    marker: {
      size: 0,
      color:'rgba(0, 0, 0, 0)',
    },
  };
  var vl2 ={
    y: hlims,
    x: [25,25],
    xaxis: 'x1',
    yaxis: 'y1',
    type: 'scatter',
    fill : 'tonextx',
    mode: 'markers',
    name:"",
    fillcolor: 'rgba(0, 207, 55, 0.05)',
    marker: {
      size: 0,
      color:'rgba(0, 0, 0, 0)',
    },
  };
  var vl3 ={
    y: hlims,
    x: [tlims[1],tlims[1]],
    xaxis: 'x1',
    yaxis: 'y1',
    type: 'scatter',
    fill : 'tonextx',
    mode: 'markers',
    name:"",
    fillcolor: 'rgba(255, 0, 0, 0.05)',
    marker: {
      size: 0,
      color:'rgba(0, 0, 0, 0)',
    },
  };
  var data = [histTemp, contour1, histHumi, hl1, hl2, hl3, vl1, vl2, vl3];

  var layout = {
    grid: {rows: 2, columns: 2},
    showlegend: false,
    bargap :0.0,
    //margin: { t: 5, b:5 },

    xaxis: {
      title:{text: "Temperature [ºC]"},
      domain: [0, 0.8],
      anchor: 'x1',
      showgrid: true,
      gridwidth: 2,
      gridcolor: 'rgb(255,255,255)',
      range: tlims,
    },
    yaxis: {
      title:{text: "Relative humidity [%]"},
      domain: [0, 0.8],
      anchor: 'y1',
      showgrid: true,
      gridwidth: 2,
      range: hlims,
    },
    xaxis2: {
      domain: [0, 0.8],
      anchor: 'x2',
      range: tlims,
      showticklabels: false,
    },
    yaxis2: {
      domain: [0.8, 1],
      anchor: 'y2',
      range: clims,
      showticklabels: false,
      gridcolor: "rgba(0,0,0,0)",
    },
    xaxis3: {
      domain: [0.8, 1],
      anchor: 'x3',
      range: clims,
      showticklabels: false,
      gridcolor: "rgba(0,0,0,0)",
    },
    yaxis3: {
      domain: [0, 0.8],
      anchor: 'y3',
      range: hlims,
      showticklabels: false,
      gridwidth: 0,
    },
  };
  Plotly.newPlot(divName, data, layout);
}

function plotDensities(divName, bins, params, labels, plots){
  var data = [];
  for (let k = 0; k < bins.length; k++) {
    for (var i=0; i < bins[k].length; i++) {
      bins[k][i].map(x=>+x/100);
    }
    var tempBins=[];
    var humiBins=[];
    for(var i = 0; i < bins[k].length; i++) {
      for(var j = 0; j < bins[k][0].length; j++) {
        //console.log(bins2d[i][j]);
        tempBins[i]=(tempBins[i] || 0) + bins[k][i][j];
        humiBins[j]=(humiBins[j] || 0) + bins[k][i][j];
      }
    }
    //transpose
    bins2dT = bins[k][0].map((_, colIndex) => bins[k].map(row => row[colIndex]));
    var t = range(params[k]["x_min"]/100,params[k]["x_max"]/100,params[k]["dx"]/100);
    var h = range(params[k]["y_min"]/100,params[k]["y_max"]/100,params[k]["dy"]/100);
    var tlims=[params[k]["x_min"]/100,params[k]["x_max"]/100];
    var hlims=[params[k]["y_min"]/100,params[k]["y_max"]/100];
    var clims=[0,60*24/2];

    var contour1 ={
      z: bins2dT,
      x: t,
      y: h,
      xaxis: 'x1',
      yaxis: 'y1',
      ncontours: 8,
      name:"",
      line:{
        smoothing: 0.85,
        color: colors[k],
      },
      colorscale: colorscales[k],
      reversescale: true,
      showscale: false,
      type: 'contour'
    };
    var histTemp ={
      y: tempBins,
      x: t,
      xaxis: 'x2',
      yaxis: 'y2',
      type: 'bar',
      name:"",
      marker: {
        color: colors[k]
        },
      };
    var histHumi ={
      y: h,
      x: humiBins,
      type: 'bar',
      xaxis: 'x3',
      yaxis: 'y3',
      name:"",
      orientation: 'h',
      marker: {
        color: colors[k]
        },
    };
    var hl1 ={
      y: [40,40],
      x: tlims,
      xaxis: 'x1',
      yaxis: 'y1',
      type: 'scatter',
      fill : 'tozeroy',
      mode: 'markers',
      name:"",
      fillcolor: 'rgba(255, 0, 0, 0.02)',
      marker: {
        size: 0,
        color:colorsTransp[k],
      },
    };
    var hl2 ={
      y: [60,60],
      x: tlims,
      xaxis: 'x1',
      yaxis: 'y1',
      type: 'scatter',
      fill : 'tonexty',
      mode: 'markers',
      name:"",
      fillcolor: 'rgba(0, 207, 55, 0.02)',
      marker: {
        size: 0,
        color:'rgba(0, 0, 0, 0)',
      },
    };
    var hl3 ={
      y: [hlims[1],hlims[1]],
      x: tlims,
      xaxis: 'x1',
      yaxis: 'y1',
      type: 'scatter',
      fill : 'tonexty',
      mode: 'markers',
      name:"",
      fillcolor: 'rgba(255, 0, 0, 0.02)',
      marker: {
        size: 0,
        color:'rgba(0, 0, 0, 0)',
      },
    };
    var vl1 ={
      y: hlims,
      x: [20,20],
      xaxis: 'x1',
      yaxis: 'y1',
      type: 'scatter',
      fill : 'tozerox',
      mode: 'markers',
      name:"",
      fillcolor: 'rgba(255, 0, 0, 0.02)',
      marker: {
        size: 0,
        color:'rgba(0, 0, 0, 0)',
      },
    };
    var vl2 ={
      y: hlims,
      x: [25,25],
      xaxis: 'x1',
      yaxis: 'y1',
      type: 'scatter',
      fill : 'tonextx',
      mode: 'markers',
      name:"",
      fillcolor: 'rgba(0, 207, 55, 0.02)',
      marker: {
        size: 0,
        color:'rgba(0, 0, 0, 0)',
      },
    };
    var vl3 ={
      y: hlims,
      x: [tlims[1],tlims[1]],
      xaxis: 'x1',
      yaxis: 'y1',
      type: 'scatter',
      fill : 'tonextx',
      mode: 'markers',
      name:"",
      fillcolor:'rgba(255, 0, 0, 0.02)',
      marker: {
        size: 0,
        color:'rgba(0, 0, 0, 0)',
      },
    };
    console.log(plots[k]);
    if (plots[k]){
      data.push(histTemp, contour1, histHumi, hl1, hl2, hl3, vl1, vl2, vl3);
    }
    var layout = {
      grid: {rows: 2, columns: 2},
      showlegend: false,
      bargap :0.0,
      //margin: { t: 5, b:20 },

      xaxis: {
        title:{text: "Temperature [ºC]"},
        domain: [0, 0.8],
        anchor: 'x1',
        showgrid: true,
        gridwidth: 2,
        gridcolor: 'rgb(255,255,255)',
        range: tlims,
      },
      yaxis: {
        title:{text: "Relative humidity [%]"},
        domain: [0, 0.8],
        anchor: 'y1',
        showgrid: true,
        gridwidth: 2,
        range: hlims,
      },
      xaxis2: {
        domain: [0, 0.8],
        anchor: 'x2',
        range: tlims,
        showticklabels: false,
      },
      yaxis2: {
        domain: [0.8, 1],
        anchor: 'y2',
        range: clims,
        showticklabels: false,
        gridcolor: "rgba(0,0,0,0)",
      },
      xaxis3: {
        domain: [0.8, 1],
        anchor: 'x3',
        range: clims,
        showticklabels: false,
        gridcolor: "rgba(0,0,0,0)",
      },
      yaxis3: {
        domain: [0, 0.8],
        anchor: 'y3',
        range: hlims,
        showticklabels: false,
        gridwidth: 0,
      },
    };

  }
  Plotly.newPlot(divName, data, layout);
}
