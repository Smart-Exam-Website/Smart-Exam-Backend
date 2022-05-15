<html>

<head>
    <title>Exam Statistics</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
    <h2 class="title">Exam Statistics</h2>
    <div id="barchart"></div>
    <?php for ($i = 0; $i < count($questionsData); $i++) {
        $chartId = "piechart " . $i;
        $questionData = $questionsData[$i];

        if ($i % 2 == 0) {
            if ($i == 0) {


    ?>
    <table class="columns">
        <tr>
            <?php } else { ?>
        </tr>
    </table>
    <table class="columns">
        <tr>
            <?php }
            } ?>

            <td>
                <div id="<?php echo $chartId; ?>" style="width: 150px; height: 150px; margin-left: 235px">
                </div>
            </td>
            <script type="text/javascript">
                var good_count = <?php echo $questionData['good']; ?>;
                var bad_count = <?php echo $questionData['bad']; ?>;
                var very_bad_count = <?php echo $questionData['very bad']; ?>;
                google.charts.load('current', {
                    'packages': ['corechart']
                });
                google.charts.setOnLoadCallback(drawChart);


                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ['User Type', 'Count'],
                        ['Good', good_count],
                        ['Bad', bad_count],
                        ['Very bad', very_bad_count]
                    ]);
                    var options = {
                        curveType: 'function',
                        legend: {
                            position: 'bottom'
                        }
                    };
                    var chart = new google.visualization.PieChart(document.getElementById("<?php echo $chartId; ?>"));
                    chart.draw(data, options);
                }
            </script>
            <script type="text/javascript">
                //bar chart

                google.charts.load('current', {
                    'packages': ['bar']
                });
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {

                    var data = google.visualization.arrayToDataTable([
                        ['Mark Percent', 'Student Count'],
                        ['0%', <?php echo $mark0; ?>],
                        ['20%', <?php echo $mark20; ?>],
                        ['40%', <?php echo $mark40; ?>],
                        ['60%', <?php echo $mark60; ?>],
                        ['80%', <?php echo $mark80; ?>],
                        ['100%', <?php echo $mark100; ?>]

                    ]);

                    var options = {
                        chart: {
                            title: 'Bar Graph | Histogram for Exam Marks',
                            subtitle: '@php echo date('Y-m-d H:i:s') @endphp',
                        },
                        bars: 'vertical'
                    };
                    var chart = new google.charts.Bar(document.getElementById('barchart'));
                    chart.draw(data, google.charts.Bar.convertOptions(options));
                }
            </script>
            <?php } ?>
        </tr>
    </table>


</body>

</html>
