<html>

<head>
    <title>Exam Statistics</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
    <h2 class="title">Exam Statistics</h2>
    <div class="details-box">
        <h2 class="examName"><?php echo $exam->name; ?></h2>
        <hr>
        <br>
        <h3 class="details">Instructor Name: <?php echo $instructor->firstName . ' ' . $instructor->lastName; ?></h3>
        <h3 class="details">Number Of Students: <span><?php echo $st_num; ?></span></h3>
        <h3 class="details">Exam Total Mark: <span><?php echo $exam->totalMark; ?></span></h3>
        <h3 class="details">Number Of Trials: <span><?php echo $exam->numberOfTrials; ?></span></h3>
    </div>

    <div class="bar">
        <div id="barchart"></div>
    </div>
    <div class="pie">
        @for ($i = 0; $i < count($questionsData); $i++)
        @php
        $chartId = "piechart " . $i;
        $questionData = $questionsData[$i]; 
        @endphp

        @if ($i % 2 == 0) 
            @if ($i == 0) 
                <table class="columns">
                    <tr>
            @else
                    </tr>
                </table>
                <table class="columns">
                    <tr>
            @endif
        @endif

                <td>
                    <div id="<?php echo $chartId; ?>" style="width: 220px; height: 220px; margin-left: 235px">
                    </div>
                </td>
                <script type="text/javascript">
                    var good_count = <?php echo $questionData['Good']; ?>;
                    var bad_count = <?php echo $questionData['Bad']; ?>;
                    var fair_count = <?php echo $questionData['Fair']; ?>;
                    google.charts.load('current', {
                        'packages': ['corechart']
                    });
                    google.charts.setOnLoadCallback(drawChart);


                    function drawChart() {
                        var data = google.visualization.arrayToDataTable([
                            ['Type', 'Count'],
                            ['Good', good_count],
                            ['Bad', bad_count],
                            ['Fair', fair_count]
                        ]);
                        console.log(good_count, bad_count, fair_count);
                        var options = {
                            curveType: 'function',
                            legend: {
                                position: 'bottom'
                            }
                        };
                        var chart = new google.visualization.PieChart(document.getElementById("<?php echo $chartId ?>"));
                        chart.draw(data, options);
                    }
                </script>
            @endfor
        <script type="text/javascript">

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
                    subtitle: '<?php echo date('Y-m-d H:i:s') ?>',
                },
                colors: ['#2f4f4f'],
                bars: 'vertical'
            };
            var chart = new google.charts.Bar(document.getElementById('barchart'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
    </tr>
    </table>


</body>

</html>
