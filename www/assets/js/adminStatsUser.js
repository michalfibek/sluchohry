var options = {
    responsive: true,
    scaleShowLabels: true
}

var dataFavGames = [];

if (favGameStats['melodicCubes'])
    dataFavGames.push({
        value: favGameStats['melodicCubes'],
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "Melodické kostky"
    });

if (favGameStats['pexeso'])
    dataFavGames.push({
        value: favGameStats['pexeso'],
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Pexeso"
    });

if (favGameStats['noteSteps'])
    dataFavGames.push({
        value: favGameStats['noteSteps'],
        color: "#FDB45C",
        highlight: "#FFC870",
        label: "Krokování not"
    });

if (favGameStats['faders'])
    dataFavGames.push({
        value: favGameStats['faders'],
        color: "#44cc71",
        highlight: "#50e07f",
        label: "Posuvníky"
    });


var ctxFavGameStats = $('#chart-fav-games').get(0).getContext('2d');
var chartFavGameStats = new Chart(ctxFavGameStats).DoughnutAlt(dataFavGames,{responsive: false});
var legendFavGames = chartFavGameStats.generateLegend();
$('#legend-fav-games').append(legendFavGames);

if (gameRatioMelodicCubes[0] || gameRatioMelodicCubes[1]) {
    var dataRatioMelodicCubes = [
        {
            value: gameRatioMelodicCubes[0],
            color: "#F7464A",
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
    var chartRatioMelodicCubes = new Chart(ctxRatioMelodicCubes).DoughnutAlt(dataRatioMelodicCubes, options);
    var legendFavGames = chartRatioMelodicCubes.generateLegend();
    $('#legend-ratio-melodicCubes').append(legendFavGames);
}

if (gameRatioPexeso[0] || gameRatioPexeso[1]) {
    var dataRatioPexeso = [
        {
            value: gameRatioPexeso[0],
            color: "#F7464A",
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
    var chartRatioPexeso = new Chart(ctxRatioPexeso).DoughnutAlt(dataRatioPexeso, options);
    var legendFavGames = chartRatioPexeso.generateLegend();
    $('#legend-ratio-pexeso').append(legendFavGames);
}

if (gameRatioNoteSteps[0] || gameRatioNoteSteps[1]) {
    var dataRatioNoteSteps = [
        {
            value: gameRatioNoteSteps[0],
            color: "#F7464A",
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
    var chartRatioNoteSteps = new Chart(ctxRatioNoteSteps).DoughnutAlt(dataRatioNoteSteps, options);
    var legendFavGames = chartRatioNoteSteps.generateLegend();
    $('#legend-ratio-noteSteps').append(legendFavGames);
}

if (gameRatioFaders[0] || gameRatioFaders[1]) {
    var dataRatioFaders = [
        {
            value: gameRatioFaders[0],
            color: "#F7464A",
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
    var chartRatioFaders = new Chart(ctxRatioFaders).DoughnutAlt(dataRatioFaders, options);
    var legendFavGames = chartRatioFaders.generateLegend();
    $('#legend-ratio-faders').append(legendFavGames);
}

