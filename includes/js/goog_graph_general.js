var $ = jQuery.noConflict();
google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
          
         
          
          graphdata1 = [];
          $i = 0;
        $.each(goog_graph_user_data, function(key, value){
            
           graphdata1[$i] = ([parseInt(value.persentage), parseInt(value.year)]);    
            $i++;
                
            
        });
      
         //  data.addRows([99,1990],[90,1991]);
        var data = new google.visualization.DataTable();
      data.addColumn('number', 'Percentage');
      data.addColumn('number', 'Year');
     
     
     data.addRows(graphdata1);
   
        var options = {
          title: 'Company Performance',
          curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
      }