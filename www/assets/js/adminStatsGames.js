var options = {

}

var dataFavGames = [
    {
        value: favGameStats['melodicCubes'],
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "Melodické kostky"
    },
    {
        value: favGameStats['pexeso'],
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Pexeso"
    },
    {
        value: favGameStats['noteSteps'],
        color: "#FDB45C",
        highlight: "#FFC870",
        label: "Krokování not"
    },
    {
        value: favGameStats['faders'],
        color: "#5cfd91",
        highlight: "#9bffbc",
        label: "Posuvníky"
    }
]

var ctxFavGameStats = $('#chart-fav-games').get(0).getContext('2d');
var chartFavGameStats = new Chart(ctxFavGameStats).Doughnut(dataFavGames,options);
var legendFavGames = chartFavGameStats.generateLegend();
$('#legend-fav-games').append(legendFavGames);


var dataRatioMelodicCubes = [
    {
        value: gameRatioMelodicCubes[0],
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "Splněné hry"
    },
    {
        value: gameRatioMelodicCubes[1],
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Nesplněné hry"
    }
]

var ctxRatioMelodicCubes = $('#chart-ratio-melodicCubes').get(0).getContext('2d');
var chartRatioMelodicCubes = new Chart(ctxRatioMelodicCubes).Doughnut(dataRatioMelodicCubes,options);
var legendFavGames = chartRatioMelodicCubes.generateLegend();
$('#legend-ratio-melodicCubes').append(legendFavGames);



var dataRatioPexeso = [
    {
        value: gameRatioPexeso[0],
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "Splněné hry"
    },
    {
        value: gameRatioPexeso[1],
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Nesplněné hry"
    }
]

var ctxRatioPexeso = $('#chart-ratio-pexeso').get(0).getContext('2d');
var chartRatioPexeso = new Chart(ctxRatioPexeso).Doughnut(dataRatioPexeso,options);
var legendFavGames = chartRatioPexeso.generateLegend();
$('#legend-ratio-pexeso').append(legendFavGames);



var dataRatioNoteSteps = [
    {
        value: gameRatioNoteSteps[0],
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "Splněné hry"
    },
    {
        value: gameRatioNoteSteps[1],
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Nesplněné hry"
    }
]

var ctxRatioNoteSteps = $('#chart-ratio-noteSteps').get(0).getContext('2d');
var chartRatioNoteSteps = new Chart(ctxRatioNoteSteps).Doughnut(dataRatioNoteSteps,options);
var legendFavGames = chartRatioNoteSteps.generateLegend();
$('#legend-ratio-noteSteps').append(legendFavGames);



var dataRatioFaders = [
    {
        value: gameRatioFaders[0],
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "Splněné hry"
    },
    {
        value: gameRatioFaders[1],
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Nesplněné hry"
    }
]

var ctxRatioFaders = $('#chart-ratio-faders').get(0).getContext('2d');
var chartRatioFaders = new Chart(ctxRatioFaders).Doughnut(dataRatioFaders,options);
var legendFavGames = chartRatioFaders.generateLegend();
$('#legend-ratio-faders').append(legendFavGames);


