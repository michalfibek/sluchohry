
var ctxFavGameStats = $('#chart-fav-games').get(0).getContext('2d');

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

var options = {

}

var chartFavGameStats = new Chart(ctxFavGameStats).Doughnut(dataFavGames,options);

var legendFavGames = chartFavGameStats.generateLegend();

$('#legend-fav-games').append(legendFavGames);


