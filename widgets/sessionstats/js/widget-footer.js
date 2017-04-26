/* widget specific footer js here */
document.addEventListener("DOMContentLoaded", function(event) {
    var xlabel = M.util.get_string('dashboard_sessionstats_xlabel', 'local_rlsiteadmin');
    var ylabel = M.util.get_string('dashboard_sessionstats_ylabel', 'local_rlsiteadmin');
    var sessionstatsgraph = c3.generate({
        bindto: '#sessionstats-graph',
        data: {
            url: '../newrelic/getsessions.php',
            mimeType: 'json',
            type: 'area',
            keys: {
                x: 'time',
                value: ['sessions']
            },
        },
        legend: {
            show: false
        },
        size: {
            height: 400
        },
        color: {
            pattern: ['#535990', '#a3629c', '#d9ab03', '#40c297', '#b5491d']
        },
        zoom: {
            enabled: true,
        },
        axis: {
            x: {
                label: {
                    text: xlabel,
                    position: 'outer-center'
                },
                type: 'timeseries',
                localtime: true,
                tick: {
                    count: 5,
                    format: "%m/%d/%Y @ %H:%M"
                }
            },
            y: {
                label: {
                    text: ylabel,
                    position: 'outer-middle'
                },
            },
        },
        subchart: {
            show: true
        }
    });
});