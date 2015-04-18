<?php $this->view('partials/head', array(
  "scripts" => array(
	"clients/client_list.js"
  )
)); ?>

<div class="container">

  <div class="row">

	<div class="col-xs-12">

	  <svg id="chart1" style="height: 400px; width: 100%"></svg>
	  <svg id="chart2" style="height: 400px; width: 100%"></svg>
	  <svg id="chart3" style="height: 400px; width: 100%"></svg>
	  <svg id="chart4" style="height: 400px; width: 100%"></svg>
	  <svg id="chart5" style="height: 400px; width: 100%"></svg>
	  <svg id="chart6" style="height: 400px; width: 100%"></svg>


	<script>

function sortByDateAscending(a, b) {
	// Dates will be cast to numbers automagically:
	return a.x - b.x;
}


$( document ).ready(function() {

	var colors = d3.scale.category20(),
		serialNumber = '<?php echo $serial_number?>',
		keyColor = function(d, i) {return colors(d.key)},
		dateformat = "L LT", // Moment.js dateformat
		xTickCount = 4, // Amount of ticks on x axis
		chart;

	$.when( 
		$.ajax( baseUrl + 'index.php?/module/machine/report/' + serialNumber ), 
		$.ajax( baseUrl + 'index.php?/module/servermetrics/get_data/' + serialNumber + '/24' ) )
	.done(function( a1, a2 )
	{
		// a1 and a2 are arguments resolved for the page1 and page2 ajax requests, respectively.
		// Each argument is an array with the following structure: [ data, statusText, jqXHR ]
		var maxMemory = Math.min(parseInt(a1[ 0 ]['physical_memory']), 48);
		var data = a2[ 0 ];
		var networkTraffic = [
			  {
				key: 'Inbound traffic',
				values:[]
			  },
			  {
				key: 'Outbound traffic',
				color: "#ff7f0e",
				values:[]
			  }
			],
			cpuUsage = [
			  {
				key: 'User',
				values:[]
			  },
			  {
				key: 'System',
				color: "#ff7f0e",
				values:[]
			  }
			],
			memoryUsage = [
			  {
				key: 'Memory Usage',
				values:[]
			  }
			],
			memoryPressure = [
			  {
				key: 'Memory Pressure',
				values: []
			  }
			],
			cachingServer = [
			  {
				key: 'From Origin',
				values:[]
			  },
			  {
				key: 'From Peers',
				values:[]
			  },
			  {
				key: 'From Cache',
				values:[]
			  }
			],
			connectedUsers = [
			  {
				key: 'AFP Users',
				values:[]
			  },
			  {
				key: 'SMB Users',
				values:[]
			  }
			]

		for (var obj in data)
		{
			var date = new Date (obj.replace(' ', 'T'))
			
			cpuUsage[0].values.push({x: date, y: data[obj][5]}) // User
			cpuUsage[1].values.push({x: date, y: data[obj][12]}) // System
			networkTraffic[0].values.push({x: date, y: data[obj][10]}) // Inbound
			networkTraffic[1].values.push({x: date, y: data[obj][13]}) // Outbound
			memoryPressure[0].values.push({x: date, y: data[obj][11]})
			memoryUsage[0].values.push({x: date, y: data[obj][6] + data[obj][7]}) // Wired + Active
			cachingServer[0].values.push({x: date, y: data[obj][3]}) // From Origin
			cachingServer[1].values.push({x: date, y: data[obj][4]}) // From Peers
			cachingServer[2].values.push({x: date, y: data[obj][2]}) // From Cache
			connectedUsers[0].values.push({x: date, y: data[obj][0]}) // AFP
			connectedUsers[1].values.push({x: date, y: data[obj][1]}) // SMB
		}

		// Memory Usage
		nv.addGraph(function() {
			chart = nv.models.lineChart()
				.y(function(d) { return d.y ? d3.round(d.y / Math.pow(1024, 3), 1): null })
				.yDomain([0, maxMemory])
				.duration(300);

			chart.xAxis
			  .ticks(xTickCount)
			  .tickFormat(function(d) { return moment(d).format(dateformat) })
			  .showMaxMin(false);

			chart.yAxis
			.ticks(6)
			  .tickFormat(function(d){return d + ' GB'})
			  .showMaxMin(false)
			  

			d3.select('#chart1')
				.datum(memoryUsage)
				.transition().duration(500)
				.call(chart)

			nv.utils.windowResize(chart.update);
			return chart;
		});        

		// CPU Usage
		nv.addGraph(function() {
			chart = nv.models.lineChart()
				.y(function(d) { return d.y ? d.y : null })
				.duration(300);
			chart.xAxis
			  .ticks(xTickCount)
			  .tickFormat(function(d) { return moment(d).format(dateformat) })
			  .showMaxMin(false);

			chart.yDomain([0,1])
			  .yAxis
				  .ticks(4)
				  .tickFormat(d3.format('%'));

			d3.select('#chart2')
				.datum(cpuUsage)
				.transition().duration(500)
				.call(chart)

			nv.utils.windowResize(chart.update);
			return chart;
		});

		// Network traffic
		nv.addGraph(function() {
			chart = nv.models.lineChart()
				.y(function(d) { return d.y ? d.y : null })
				.duration(300);
			chart.xAxis
			  .ticks(xTickCount)
			  .tickFormat(function(d) { return moment(d).format(dateformat) })
			  .showMaxMin(false);

			chart.yAxis.tickFormat(d3.format('s'))
			  .showMaxMin(false);

			d3.select('#chart3')
				.datum(networkTraffic)
				.transition().duration(500)
				.call(chart)

			nv.utils.windowResize(chart.update);
			return chart;
		});

		// Memory Pressure
		nv.addGraph(function() {
			chart = nv.models.lineChart()
				.y(function(d) { return d.y ? d.y : null })
				.duration(300);
			chart.xAxis
			  .ticks(xTickCount)
			  .tickFormat(function(d) { return moment(d).format(dateformat) })
			  .showMaxMin(false);

			chart.yDomain([0,1])
			  .yAxis
			  	.ticks(4)
			  	.tickFormat(d3.format('%'));

			d3.select('#chart4')
				.datum(memoryPressure)
				.transition().duration(500)
				.call(chart)

			nv.utils.windowResize(chart.update);
			return chart;
		});

		// Caching server
		nv.addGraph(function() {
			chart = nv.models.lineChart()
				.y(function(d) { return d.y ? d.y : null })
				.duration(300);
			chart.xAxis
			  .ticks(xTickCount)
			  .tickFormat(function(d) { return moment(d).format(dateformat) })
			  .showMaxMin(false);

			chart.yAxis.tickFormat(d3.format('s'))
			  .showMaxMin(false);

			d3.select('#chart5')
				.datum(cachingServer)
				.transition().duration(500)
				.call(chart)

			nv.utils.windowResize(chart.update);
			return chart;
		});

		// File Sharing Users
		nv.addGraph(function() {
			chart = nv.models.lineChart()
				.y(function(d) { return d.y ? d.y : null })
				.duration(300);
			chart.xAxis
			  .ticks(xTickCount)
			  .tickFormat(function(d) { return moment(d).format(dateformat) })
			  .showMaxMin(false);

			chart.yDomain([0,1])
			  .yAxis.tickFormat(d3.format('.0f'));

			d3.select('#chart6')
				.datum(connectedUsers)
				.transition().duration(500)
				.call(chart)

			nv.utils.windowResize(chart.update);
			return chart;
		}); 

	});


});
</script>

	</div> <!-- /span 12 -->

  </div> <!-- /row -->

</div>  <!-- /container -->


<?php $this->view('partials/foot'); ?>
