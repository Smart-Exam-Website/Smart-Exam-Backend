<html>

<head>
    <title>Exam Statistics</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
    <h2 style="margin:50px 0px 0px 0px;text-align: center;">Exam Statistics</h2>

    <?php for ($i = 0; $i < count($questionsData); $i++) {
        $chartId = "piechart " . $i;
        $questionData = $questionsData[i];

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
                    <div id="<?php echo $chartId ?>" style="width: 150px; height: 150px; margin-left: 235px">
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
                        var chart = new google.visualization.PieChart(document.getElementById("<?php echo $chartId ?>"));
                        chart.draw(data, options);
                    }
                </script>
            <?php } ?>



</body>

</html>