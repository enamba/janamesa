/**
 * --------------------------------------------------------------------
 * jQuery-Plugin "visualize"
 * by Scott Jehl, scott@filamentgroup.com
 * http://www.filamentgroup.com
 * Copyright (c) 2009 Filament Group 
 * Dual licensed under the MIT (filamentgroup.com/examples/mit-license.txt) and GPL (filamentgroup.com/examples/gpl-license.txt) licenses.
 * 	
 * --------------------------------------------------------------------
 */
(function($) { 
$.fn.visualize = function(options, container){
	return $(this).each(function(){
		//configuration
		var o = $.extend({
			type: 'bar', //also available: area, pie, line
			width: 700, //height of canvas - defaults to table height
			height: 300, //height of canvas - defaults to table height
			appendTitle: true, //table caption text is added to chart
			title: null, //grabs from table caption if null
			appendKey: true, //color key is added to chart
			rowFilter: ' ',
			colFilter: ' ',
			colors: ['#be1e2d','#666699','#92d5ea','#ee8310','#8d10ee','#5a3b16','#26a4ed','#f45a90','#e9e744', '#f9a744'],
			textColors: [], //corresponds with colors array. null/undefined items will fall back to CSS
			parseDirection: 'x', //which direction to parse the table data
			pieMargin: 20, //pie charts only - spacing around pie
			pieLabelsAsPercent: true,
			pieLabelPos: 'inside',
			lineWeight: 4, //for line and area - stroke weight
			lineDots: ( options && options.type == 'line' ) ? 'double' : false, //also available: 'single', false
			dotInnerColor: "#ffffff", // only used for lineDots:'double'
			barGroupMargin: 10,
			barMargin: 1, //space around bars in bar chart (added to both sides of bar)
			yLabelInterval: 30, //distance between y labels,
			labelFilter: function(label) { return label; },
                                                bottomValue: false //value to start at
		},options);
		
		//reset width, height to numbers
		o.width = parseFloat(o.width);
		o.height = parseFloat(o.height);
		
		
		var self = $(this);
		
		//function to scrape data from html table
		function scrapeTable(){
			var colors = o.colors;
			var textColors = o.textColors;
			var tableData = {
				dataGroups: function(){
					var dataGroups = [];
					if(o.parseDirection == 'x'){
						self.find('tr:gt(0)').filter(o.rowFilter).each(function(i){
							dataGroups[i] = {};
							dataGroups[i].points = [];
							dataGroups[i].color = colors[i];
							if(textColors[i]){ dataGroups[i].textColor = textColors[i]; }
							$(this).find('td').filter(o.colFilter).each(function(){
								dataGroups[i].points.push( parseFloat($(this).text()) );
							});
						});
					}
					else {
						var cols = self.find('tr:eq(1) td').filter(o.colFilter).size();
						for(var i=0; i<cols; i++){
							dataGroups[i] = {};
							dataGroups[i].points = [];
							dataGroups[i].color = colors[i];
							if(textColors[i]){ dataGroups[i].textColor = textColors[i]; }
							self.find('tr:gt(0)').filter(o.rowFilter).each(function(){
								dataGroups[i].points.push( $(this).find('td').filter(o.colFilter).eq(i).text()*1 );
							});
						};
					}
					return dataGroups;
				},
				allData: function(){
					var allData = [];
					$(this.dataGroups()).each(function(){
						allData.push(this.points);
					});
					return allData;
				},
				dataSum: function(){
					var dataSum = 0;
					var allData = this.allData().join(',').split(',');
					$(allData).each(function(){
						dataSum += parseFloat(this);
					});
					return dataSum
				},	
				topValue: function(){
						var topValue = 0;
						var allData = this.allData().join(',').split(',');
						$(allData).each(function(){
							if(parseFloat(this,10)>topValue) topValue = parseFloat(this);
						});
                                                                                                                                           
                                                                                                if(topValue < 10 ) {
                                                                                                    topValue = 10;
                                                                                                }
						return  topValue;
				},
				bottomValue: function(){
                                                                                                if(o.bottomValue === false) {
                                                                                                    var bottomValue = this.topValue();
                                                                                                    var allData = this.allData().join(',').split(',');
                                                                                                    $(allData).each(function(){
							if(this<bottomValue) bottomValue = parseFloat(this);
                                                                                                    });
                                                                                                    return bottomValue;                                                                                                    
                                                                                                }else {
                                                                                                    return o.bottomValue;
                                                                                                }
						
				},
				memberTotals: function(){
					var memberTotals = [];
					var dataGroups = this.dataGroups();
					$(dataGroups).each(function(l){
						var count = 0;
						$(dataGroups[l].points).each(function(m){
							count +=dataGroups[l].points[m];
						});
						memberTotals.push(count);
					});
					return memberTotals;
				},
				yTotals: function(){
					var yTotals = [];
					var dataGroups = this.dataGroups();
					var loopLength = this.xLabels().length;
					for(var i = 0; i<loopLength; i++){
						yTotals[i] =[];
						var thisTotal = 0;
						$(dataGroups).each(function(l){
							yTotals[i].push(this.points[i]);
						});
						yTotals[i].join(',').split(',');
						$(yTotals[i]).each(function(){
							thisTotal += parseFloat(this);
						});
						yTotals[i] = thisTotal;
						
					}
					return yTotals;
				},
				topYtotal: function(){
					var topYtotal = 0;
						var yTotals = this.yTotals().join(',').split(',');
						$(yTotals).each(function(){
							if(parseFloat(this,10)>topYtotal) topYtotal = parseFloat(this);
						});
						return topYtotal;
				},
				totalYRange: function(){
					return this.topValue() - this.bottomValue();
				},
				xLabels: function(){
					var xLabels = [];
					if(o.parseDirection == 'x'){
						self.find('tr:eq(0) th').filter(o.colFilter).each(function(){
							xLabels.push($(this).html());
						});
					}
					else {
						self.find('tr:gt(0) th').filter(o.rowFilter).each(function(){
							xLabels.push($(this).html());
						});
					}
					return xLabels;
				},
				yLabels: function(){
					var yLabels = [];
					var chartHeight = ('bar' == o.type && 'horizontal' == o.barDirection) ? o.width : o.height;
					var numLabels = Math.round(chartHeight / 30);
					//var totalRange = this.topValue() + Math.abs(this.bottomValue());
					var loopInterval = Math.round(this.totalYRange() / Math.floor(numLabels)); //fix provided from lab
					loopInterval = Math.max(loopInterval, 1);
					for(var j=this.bottomValue(); j<=topValue; j+=loopInterval){
						yLabels.push(j); 
					}
					if(yLabels[yLabels.length-1] != this.topValue()) {
						yLabels.pop();
						yLabels.push(this.topValue());
					}
					return yLabels;
				}			
			};
			
			return tableData;
		};
		
		
		//function to create a chart
		var createChart = {
			pie: function(){	
				
				canvasContain.addClass('visualize-pie');
				
				if(o.pieLabelPos == 'outside'){ canvasContain.addClass('visualize-pie-outside'); }	
						
				var centerx = Math.round(canvas.width()/2);
				var centery = Math.round(canvas.height()/2);
				var radius = centery - o.pieMargin;				
				var counter = 0.0;
				var toRad = function(integer){ return (Math.PI/180)*integer; };
				var labels = $('<ul class="visualize-labels"></ul>')
					.insertAfter(canvas);

				//draw the pie pieces
				$.each(memberTotals, function(i){
					var fraction = this / dataSum;
                    if (fraction <= 0 || isNaN(fraction))
                        return;
					ctx.beginPath();
					ctx.moveTo(centerx, centery);
					ctx.arc(centerx, centery, radius, 
						counter * Math.PI * 2 - Math.PI * 0.5,
						(counter + fraction) * Math.PI * 2 - Math.PI * 0.5,
		                false);
			        ctx.lineTo(centerx, centery);
			        ctx.closePath();
			        ctx.fillStyle = dataGroups[i].color;
			        ctx.fill();
			        // draw labels
			       	var sliceMiddle = (counter + fraction/2);
			       	var distance = o.pieLabelPos == 'inside' ? radius/1.5 : radius +  radius / 5;
			        var labelx = Math.round(centerx + Math.sin(sliceMiddle * Math.PI * 2) * (distance));
			        var labely = Math.round(centery - Math.cos(sliceMiddle * Math.PI * 2) * (distance));
			        var leftRight = (labelx > centerx) ? 'right' : 'left';
			        var topBottom = (labely > centery) ? 'bottom' : 'top';
			        var percentage = parseFloat((fraction*100).toFixed(2));

			        if(percentage){
			        	var labelval = (o.pieLabelsAsPercent) ? percentage + '%' : o.labelFilter(this);
				        var labeltext = $('<span class="visualize-label">' + labelval +'</span>')
				        	.css(leftRight, 0)
				        	.css(topBottom, 0);
				        	if(labeltext)
				        var label = $('<li class="visualize-label-pos"></li>')
				       			.appendTo(labels)
				        		.css({left: labelx, top: labely})
				        		.append(labeltext);	
				        labeltext
				        	.css('font-size', radius / 8)		
				        	.css('margin-'+leftRight, -labeltext.width()/2)
				        	.css('margin-'+topBottom, -labeltext.outerHeight()/2);
				        	
				        if(dataGroups[i].textColor){ labeltext.css('color', dataGroups[i].textColor); }	
			        }
			      	counter+=fraction;
				});
			},
			
			line: function(area){
			
				if(area){ canvasContain.addClass('visualize-area'); }
				else{ canvasContain.addClass('visualize-line'); }
			
				//write X labels
				var xInterval = canvas.width() / (xLabels.length -1);
				var xlabelsUL = $('<ul class="visualize-labels-x"></ul>')
					.width(canvas.width())
					.height(canvas.height())
					.insertBefore(canvas);
				$.each(xLabels, function(i){ 
					var thisLi = $('<li><span>'+o.labelFilter(this)+'</span></li>')
						.prepend('<span class="line" />')
						.css('left', xInterval * i)
						.appendTo(xlabelsUL);						
					var label = thisLi.find('span:not(.line)');
					var leftOffset = label.width()/-2;
					if(i == 0){ leftOffset = 0; }
					else if(i== xLabels.length-1){ leftOffset = -label.width(); }
					label
						.css('margin-left', leftOffset)
						.addClass('label');
				});

				//write Y labels
				var yScale = canvas.height() / totalYRange;
				var liBottom = canvas.height() / (yLabels.length-1);
				var ylabelsUL = $('<ul class="visualize-labels-y"></ul>')
					.width(canvas.width())
					.height(canvas.height())
					.insertBefore(canvas);
					
				$.each(yLabels, function(i){  
					var thisLi = $('<li><span>'+o.labelFilter(this)+'</span></li>')
						.prepend('<span class="line"  />')
						.css('bottom',liBottom*i)
						.prependTo(ylabelsUL);
					var label = thisLi.find('span:not(.line)');
					var topOffset = label.height()/-2;
					if(i == 0){ topOffset = -label.height(); }
					else if(i== yLabels.length-1){ topOffset = 0; }
					label
						.css('margin-top', topOffset)
						.addClass('label');
				});

				var drawPoint = function (x,y,color,size) {
					ctx.moveTo(x,y);
					ctx.beginPath();
					ctx.arc(x,y,size/2,0,2*Math.PI,false);
					ctx.closePath();
					ctx.fillStyle = color;
					ctx.fill();
				}
				var pointQueue = [];
				var keyPoint = function(x,y,color) {
					var size = o.lineWeight*Math.PI;
					pointQueue.push(function() {
						drawPoint(x,y,color,size);
						if(o.lineDots === 'double') {
							drawPoint(x,y,o.dotInnerColor,size-o.lineWeight*Math.PI/2);
						}
					});
				};

				//start from the bottom left
				ctx.translate(0,zeroLoc);
				//iterate and draw
				$.each(dataGroups,function(h){
					ctx.beginPath();
					ctx.lineWidth = o.lineWeight;
					ctx.lineJoin = 'round';
					var points = this.points;
					var integer = 0; // the current offset
					var color = this.color;
					ctx.moveTo(0,-(points[0]*yScale));
					keyPoint(0,-(points[0]*yScale),color);
					$.each(points, function(){
						if(o.lineDots) {
							keyPoint(integer,-(this*yScale),color);
						}
						ctx.lineTo(integer,-(this*yScale));
						integer+=xInterval;
					});
					ctx.strokeStyle = color;
					ctx.stroke();
					if(area){
						// integer can be infinite if the xInterval is infinite (i.e. there's only one entry)
						if (isFinite(integer))
							ctx.lineTo(integer,0);
						ctx.lineTo(0,0);
						ctx.closePath();
						ctx.fillStyle = color;
						ctx.globalAlpha = .3;
						ctx.fill();
						ctx.globalAlpha = 1.0;
					}
					else {ctx.closePath();}
					$.each(pointQueue,function(){
						pointQueue.shift().call();
					});
				});
			},
			
			area: function(){
				createChart.line(true);
			},
			
			bar: function(){
				/**
				 * We can draw horizontal or vertical bars depending on the
				 * value of the 'barDirection' option (which may be 'vertical' or
				 * 'horizontal').
				 */

				var horizontal = (o.barDirection == 'horizontal');

				canvasContain.addClass('visualize-bar');

				/**
				 * Write labels along the bottom of the chart.	If we're drawing
				 * horizontal bars, these will be the yLabels, otherwise they
				 * will be the xLabels.	The positioning also varies slightly:
				 * yLabels are values, hence they will span the whole width of
				 * the canvas, whereas xLabels are supposed to line up with the
				 * bars.
				 */
				var bottomLabels = horizontal ? yLabels : xLabels;

				var xInterval = canvas.width() / (bottomLabels.length - (horizontal ? 1 : 0));

				var xlabelsUL = $('<ul class="visualize-labels-x"></ul>')
					.width(canvas.width())
					.height(canvas.height())
					.insertBefore(canvas);

				$.each(bottomLabels, function(i){
					var thisLi = $('<li><span class="label">'+o.labelFilter(this)+'</span></li>')
						.prepend('<span class="line" />')
						.css('left', xInterval * i)
						.width(xInterval)
						.appendTo(xlabelsUL);

					if (horizontal)	{
						var label = thisLi.find('span.label');
						label.css("margin-left", -label.width() / 2);
					}
				});

				/**
				 * Write labels along the left of the chart.	Follows the same idea
				 * as the bottom labels.
				 */
				leftLabels = horizontal ? xLabels : yLabels;
				var liBottom = canvas.height() / (leftLabels.length - (horizontal ? 0 : 1));

				var ylabelsUL = $('<ul class="visualize-labels-y"></ul>')
					.width(canvas.width())
					.height(canvas.height())
					.insertBefore(canvas);

				$.each(leftLabels, function(i){
					var thisLi = $('<li><span>'+o.labelFilter(this)+'</span></li>').prependTo(ylabelsUL);

					var label = thisLi.find('span:not(.line)').addClass('label');

					if (horizontal) {
						/**
						 * For left labels, we want to vertically align the text
						 * to the middle of its container, but we don't know how
						 * many lines of text we will have, since the labels could
						 * be very long.
						 *
						 * So we set a min-height of liBottom, and a max-height
						 * of liBottom + 1, so we can then check the label's actual
						 * height to determine if it spans one line or more lines.
						 */
						label.css({
							'min-height': liBottom,
							'max-height': liBottom + 1,
							'vertical-align': 'middle'
						});
						thisLi.css({'top': liBottom * i, 'min-height': liBottom});

						r = label[0].getClientRects()[0];
						if (r.bottom - r.top == liBottom) {
							/* This means we have only one line of text; hence
							 * we can centre the text vertically by setting the line-height,
							 * as described at:
							 *   http://www.ampsoft.net/webdesign-l/vertical-aligned-nav-list.html
							 *
							 * (Although firefox has .height on the rectangle, IE doesn't,
							 * so we use r.bottom - r.top rather than r.height.)
							 */
							label.css('line-height', parseInt(liBottom) + 'px');
						}
						else {
							/*
							 * If there is more than one line of text, then we shouldn't
							 * touch the line height, but we should make sure the text
							 * doesn't overflow the container.
							 */
							label.css("overflow", "hidden");
						}
					}
					else {
						thisLi.css('bottom', liBottom * i).prepend('<span class="line" />');
						label.css('margin-top', -label.height() / 2)
					}
				});

				// Draw bars

				if (horizontal) {
					// for horizontal, keep the same code, but rotate everything 90 degrees
					// clockwise.
					ctx.rotate(Math.PI / 2);
				}
				else {
					// for vertical, translate to the top left corner.
					ctx.translate(0, zeroLoc);
				}

				// Don't attempt to draw anything if all the values are zero,
				// otherwise we will get weird exceptions from the canvas methods.
				if (totalYRange <= 0)
					return;

				var yScale = (horizontal ? canvas.width() : canvas.height()) / totalYRange;
				var barWidth = horizontal ? (canvas.height() / xLabels.length) : (canvas.width() / (bottomLabels.length));
				var linewidth = (barWidth - o.barGroupMargin*2) / dataGroups.length;

				for(var h=0; h<dataGroups.length; h++){
					ctx.beginPath();

					var strokeWidth = linewidth - (o.barMargin*2);
					ctx.lineWidth = strokeWidth;
					var points = dataGroups[h].points;
					var integer = 0;
					for(var i=0; i<points.length; i++){
						// If the last value is zero, IE will go nuts and not draw anything,
						// so don't try to draw zero values at all.
						if (points[i] != 0) {
							var xVal = (integer-o.barGroupMargin)+(h*linewidth)+linewidth/2;
							xVal += o.barGroupMargin*2;

							ctx.moveTo(xVal, 0);
							ctx.lineTo(xVal, Math.round(-points[i]*yScale));
                        }
						integer+=barWidth;
					}
					ctx.strokeStyle = dataGroups[h].color;
					ctx.stroke();
					ctx.closePath();
				}
			}
		};
	
		//create new canvas, set w&h attrs (not inline styles)
		var canvasNode = document.createElement("canvas"); 
		var canvas = $(canvasNode)
			.attr({
				'height': o.height,
				'width': o.width
			});
			
		//get title for chart
		var title = o.title || self.find('caption').text();
		
		//create canvas wrapper div, set inline w&h, append
		var canvasContain = (container || $('<div class="visualize" role="img" aria-label="Chart representing data from the table: '+ title +'" />'))
			.height(o.height)
			.width(o.width)
			.append(canvas);

		//scrape table (this should be cleaned up into an obj)
		var tableData = scrapeTable();
		var dataGroups = tableData.dataGroups();
		var allData = tableData.allData();
		var dataSum = tableData.dataSum();
		var topValue = tableData.topValue();
		var bottomValue = tableData.bottomValue();
		var memberTotals = tableData.memberTotals();
		var totalYRange = tableData.totalYRange();
		var zeroLoc = o.height * (topValue/totalYRange);
		var xLabels = tableData.xLabels();
		var yLabels = tableData.yLabels();
								
		//title/key container
		if(o.appendTitle || o.appendKey){
			var infoContain = $('<div class="visualize-info"></div>')
				.appendTo(canvasContain);
		}
		
		//append title
		if(o.appendTitle){
			$('<div class="visualize-title">'+ title +'</div>').appendTo(infoContain);
		}
		
		
		//append key
		if(o.appendKey){
			var newKey = $('<ul class="visualize-key"></ul>');
			var selector;
			if(o.parseDirection == 'x'){
				selector = self.find('tr:gt(0) th').filter(o.rowFilter);
			}
			else{
				selector = self.find('tr:eq(0) th').filter(o.colFilter);
			}
			
			selector.each(function(i){
				$('<li><span class="visualize-key-color" style="background: '+dataGroups[i].color+'"></span><span class="visualize-key-label">'+ $(this).text() +'</span></li>')
					.appendTo(newKey);
			});
			newKey.appendTo(infoContain);
		};		
		
		//append new canvas to page
		
		if(!container){canvasContain.insertAfter(this); }
		if( typeof(G_vmlCanvasManager) != 'undefined' ){ G_vmlCanvasManager.init(); G_vmlCanvasManager.initElement(canvas[0]); }	
		
		//set up the drawing board	
		var ctx = canvas[0].getContext('2d');
		
		//create chart
		createChart[o.type]();
		
		//clean up some doubled lines that sit on top of canvas borders (done via JS due to IE)
		$('.visualize-line li:first-child span.line, .visualize-line li:last-child span.line, .visualize-area li:first-child span.line, .visualize-area li:last-child span.line, .visualize-bar li:first-child span.line,.visualize-bar .visualize-labels-y li:last-child span.line').css('border','none');
		if(!container){
		//add event for updating
		canvasContain.bind('visualizeRefresh', function(){
			self.visualize(o, $(this).empty()); 
		});
		}
	}).next(); //returns canvas(es)
};
})(jQuery);


