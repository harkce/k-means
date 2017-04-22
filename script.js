var x = [];
var y = [];
var c = [];
var raw;
var datalen;
var classcount;

$(document).ready(function() {
	init()
})

function init() {
	$('#1e').hide()
	$('#1gh').hide()
	$('#1c').attr('disabled','disabled')
	$('#1d').attr('disabled','disabled')
	$('#1f').attr('disabled','disabled')
	$('#plot1').empty()
	$('#plot2').empty()
	$('#plot3').empty()
	$('#plot4').empty()
	$('#sse').hide()
	$('#sse2').hide()
}

$('#1a').click(function() {
	$('#1a').attr('disabled','disabled')
	$('#1a').text('Loading...')
	$.ajax({
		type: "POST",
		data: {type: 'countclass', filename: $('#dataset').val()},
		url: "kmeans.php",
		success: function(res) {
			classcount = res;
		}
	})
	$.ajax({
		type: "POST",
		data: {type: 'loadData',filename: $('#dataset').val()},
		url: "kmeans.php",
		success: function(res) {
			res = JSON.parse(res);
			raw = res;
			var len = res.length;
			datalen = len;
			x = [];
			y = [];
			c = [];
			for (var i = 0; i < len; i++) {
				x.push(res[i].x);
				y.push(res[i].y);
				c.push(res[i].c);
			}
			var mark = {
				x: x,
				y: y,
				mode: 'markers',
				type: 'scatter'
			};

			var data = [mark];

			Plotly.newPlot('plot1', data);

			$('#1a').text('Load Dataset (1a)')
			$('#1c').removeAttr("disabled")
			$('#1d').removeAttr("disabled")
			$('#1f').removeAttr("disabled")
			$('#1a').removeAttr("disabled")
			$('html, body').animate({
		        scrollTop: $("#firstrow").offset().top
		    }, 2000);
		},
	});
});

$('#1c').click(function() {
	$('#1c').attr('disabled','disabled')
	$('#1c').text('Loading...')
	$.ajax({
		type: "POST",
		url: "kmeans.php",
		dataType: 'json',
		data: {
			dataset: JSON.stringify(raw),
			problem: '1c'
		},
		success: function(res) {
			var data = [];
			for (var i = 0; i < classcount; i++) {
				var mark = {
					x: [],
					y: [],
					mode: 'markers',
					type: 'scatter',
					name: 'Cluster ' + (i+1)
				}
				data.push(mark);
			}
			$('#sse').show()
			$('#sse').html('<h4><strong>SSE</strong> : <span style="color: blue">' + res.sse + '</span></h4>')
			for (var i = 0; i < datalen; i++) {
				data[res[i].c - 1].x.push(res[i].x);
				data[res[i].c - 1].y.push(res[i].y);
			}
			Plotly.newPlot('plot2', data);

			$('#1c').text('Run k-Means (1c)')
			$('#1e').show()
			$('#1c').removeAttr('disabled')
			$('html, body').animate({
		        scrollTop: $("#firstrow").offset().top
		    }, 2000);
		}
	})
})

$('#1d').click(function() {
	$('#1d').attr('disabled', 'disabled')
	$('#1d').text('Loading...')
	var data = [];
	for (var i = 0; i < classcount; i++) {
		var mark = {
			x: [],
			y: [],
			mode: 'markers',
			type: 'scatter',
			name: 'Cluster ' + (i+1)
		}
		data.push(mark);
	}
	for (var i = 0; i < datalen; i++) {
		data[c[i] - 1].x.push(x[i])
		data[c[i] - 1].y.push(y[i])
	}
	Plotly.newPlot('plot3', data);

	$('#1d').text('Load Dataset (with actual cluster) (1d)')
	$('#1d').removeAttr('disabled')
	$('html, body').animate({
        scrollTop: $("#secondrow").offset().top
    }, 2000);
})

$('#1f').click(function() {
	$('#1f').attr('disabled', 'disabled')
	$('#1f').text('Loading...')
	$.ajax({
		type: "POST",
		url: "kmeans.php",
		dataType: 'json',
		data: {
			dataset: JSON.stringify(raw),
			problem: '1f'
		},
		success: function(res) {
			var data = [];
			for (var i = 0; i < classcount; i++) {
				var mark = {
					x: [],
					y: [],
					mode: 'markers',
					type: 'scatter',
					name: 'Cluster ' + (i+1)
				}
				data.push(mark);
			}
			$('#sse2').show()
			$('#sse2').html('<h4><strong>SSE</strong> : <span style="color: blue">' + res.sse + '</span></h4>')
			for (var i = 0; i < datalen; i++) {
				data[res[i].c - 1].x.push(res[i].x);
				data[res[i].c - 1].y.push(res[i].y);
			}
			Plotly.newPlot('plot4', data);

			$('#1gh').show()
			$('#1f').removeAttr('disabled')
			$('#1f').text('Run k-Means (1f)')
			$('html, body').animate({
		        scrollTop: $("#thirdrow").offset().top
		    }, 2000);
		}
	})
})