/* widget specific footer js here */
document.addEventListener("DOMContentLoaded", function(event) {
    var browserstatsgraph = c3.generate({
        bindto: '#browserstats-graph',
        data: {
            mimeType: 'json',
            url: '../newrelic/getbrowsers.php',
            type: 'pie',
        },
        legend: {
            item: {
                onclick: function (id) { return false; }
            }
        },
        size: {
            height: 400
        },
        color: {
            pattern: ['#535990', '#a3629c', '#d9ab03', '#40c297', '#b5491d', '#717491', '#c6a3c3', '#db9578', '#39564c']
        },
    });
});