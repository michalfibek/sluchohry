
var labelStarted = "zahájené hry";
var labelSolved = "vyřešené hry";

var ctxWeekCurrentPlays = $('#chart-week-current-plays').get(0).getContext('2d');
var ctxWeekPreviousPlays = $('#chart-week-previous-plays').get(0).getContext('2d');
var ctxFavGameStats = $('#chart-fav-games').get(0).getContext('2d');

var dataCurrent = {
    labels: ["Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"],
    datasets: [
        {
            label: labelStarted,
            fillColor: "rgba(220,220,220,0.5)",
            strokeColor: "rgba(220,220,220,0.8)",
            highlightFill: "rgba(220,220,220,0.75)",
            highlightStroke: "rgba(220,220,220,1)",
            data: startedByWeekCurrent
        },
        {
            label: labelSolved,
            fillColor: "rgba(151,187,205,0.5)",
            strokeColor: "rgba(151,187,205,0.8)",
            highlightFill: "rgba(151,187,205,0.75)",
            highlightStroke: "rgba(151,187,205,1)",
            data: solvedByWeekCurrent
        }
    ]
};
var dataPrevious = {
    labels: ["Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"],
    datasets: [
        {
            label: labelStarted,
            fillColor: "rgba(220,220,220,0.5)",
            strokeColor: "rgba(220,220,220,0.8)",
            highlightFill: "rgba(220,220,220,0.75)",
            highlightStroke: "rgba(220,220,220,1)",
            data: startedByWeekPrevious
        },
        {
            label: labelSolved,
            fillColor: "rgba(151,187,205,0.5)",
            strokeColor: "rgba(151,187,205,0.8)",
            highlightFill: "rgba(151,187,205,0.75)",
            highlightStroke: "rgba(151,187,205,1)",
            data: solvedByWeekPrevious
        }
    ]
};

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
        color: "#44cc71",
        highlight: "#50e07f",
        label: "Posuvníky"
    }
]

var options = {

}

var chartWeekCurrentPlays = new Chart(ctxWeekCurrentPlays).Bar(dataCurrent, options);
var chartWeekPreviousPlays = new Chart(ctxWeekPreviousPlays).Bar(dataPrevious, options);
var chartFavGameStats = new Chart(ctxFavGameStats).DoughnutAlt(dataFavGames,options);

var legendCurrentPlays = chartWeekCurrentPlays.generateLegend();
var legendFavGames = chartFavGameStats.generateLegend();

$('#legend-week-plays').append(legendCurrentPlays);
$('#legend-fav-games').append(legendFavGames);


var currentPlaysBox = $('#week-current-plays');
var previousPlaysBox = $('#week-previous-plays');

previousPlaysBox.css('opacity', '0');
previousPlaysBox.toggleClass('hidden');

currentPlaysBox.find('a').on('click', function() {
    currentPlaysBox.css('opacity', '0');
    currentPlaysBox.toggleClass('hidden');
    previousPlaysBox.toggleClass('hidden').transition({ opacity: 1 }, 300, function() {
        this.show();
    });
})

previousPlaysBox.find('a').on('click', function() {
    previousPlaysBox.css('opacity', '0');
    previousPlaysBox.toggleClass('hidden');
    currentPlaysBox.toggleClass('hidden').transition({ opacity: 1 }, 300, function() {
        this.show();
    });
})

