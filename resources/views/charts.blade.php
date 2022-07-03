<html>

<head>
    <title>Exam Statistics</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
    <h2 class="title">Exam Statistics</h2>
    @if ($error)
        <div class="error">
            <h3>
                <?php echo $error; ?>
            </h3>
        </div>
    @else
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
            <h2 class="title">Students Score</h2>
            <div id="barchart"></div>
        </div>
        <div class="pie">
            <h2 class="title">Exam Questions</h2>

            @for ($i = 0; $i < count($questionsData); $i++)
                @php
                    $chartId = 'piechart ' . $i;
                    $questionData = $questionsData[$i];
                @endphp

                @if ($i % 2 == 0)
                    @if ($i == 0)
                        <table class="columns">
                            <div class="charts">
                                <tr>
                                @else
                                </tr>
                        </table>
                        <table class="columns">
                            <tr>
                    @endif
                @endif

                <td>
                    <div class="question">
                        <h3 class="box">Question <?php echo $i + 1; ?></h3>
                        <div id="<?php echo $chartId; ?>" class="pie_circle">
                        </div>
                    </div>
                </td>
                <script type="text/javascript">
                    google.charts.load('current', {
                        'packages': ['corechart']
                    });


                    google.charts.setOnLoadCallback(drawChart);

                    function drawChart() {
                        var good_count = <?php echo $questionData['Good']; ?>;
                        var bad_count = <?php echo $questionData['Bad']; ?>;
                        var fair_count = <?php echo $questionData['Fair']; ?>;
                        var chartId = "<?php echo $chartId; ?>";
                        var none_count = 0;
                        if ((good_count == 0 && bad_count == 0 && fair_count == 0)) {
                            none_count = 1;
                        }

                        var data = google.visualization.arrayToDataTable([
                            ['Type', 'Count'],
                            ['Good', good_count],
                            ['Bad', bad_count],
                            ['Fair', fair_count],
                            ['No Results', none_count]
                        ]);


                        var options = {
                            curveType: 'function',
                            legend: {
                                position: 'bottom'
                            },
                            colors: ['#2f4f4f', '#f90', '#54aa7a', '#4f2f42'],
                        };
                        var chart = new google.visualization.PieChart(document.getElementById(chartId));
                        chart.draw(data, options);
                        window.addEventListener('resize', drawChart, false);
                    }
                </script>
            @endfor
        </div>
        </div>
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
                        subtitle: '<?php echo date('Y-m-d H:i:s'); ?>',
                    },
                    colors: ['#2f4f4f'],
                    bars: 'vertical'
                };
                var chart = new google.charts.Bar(document.getElementById('barchart'));
                chart.draw(data, google.charts.Bar.convertOptions(options));
                window.addEventListener('resize', drawChart, false);
            }
        </script>
    @endif
</body>

</html>
