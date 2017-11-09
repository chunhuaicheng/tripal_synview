
    /**
	var data = {
		A:{
			min:1,
			max:1000,
			rect:{
				ND00001:{min:110,max:170},
				ND00002:{min:200,max:253},
				ND00003:{min:450,max:500},
				ND00004:{min:550,max:610},
				ND00005:{min:810,max:870}
			}

		},
		B:{
			min:150,
			max:1150,
			rect:{
				ID00001:{min:170,max:270},
				ID00002:{min:420,max:480},
				ID00003:{min:550,max:620},
				ID00004:{min:840,max:880},
				ID00005:{min:1010,max:1070}
			}
		}
	};

	var relate = [
		{A:"ND00001", B:"ID00001"},
		{A:"ND00004", B:"ID00005"},
		{A:"ND00002", B:"ID00002"}
	];
    **/

	// set the svg and other parameters for drawing synteny genes
	var svg = d3.select("#svg");

	var myZoom = d3.zoom();
	myZoom(svg);

	var margin = {
		left:100,
		right:100,
		top:100,
		bottom:100
	};

	var chrWidth = 20;
	var chrHeight = 600;
	var spaceBetweenChr = 250;
	var txtToChr = 5;

	// the related include the path for synteny links
	let relateD = relate.map(d=>{
        let _d = [];
        _d.push([margin.left + chrWidth,data.A.rect[d.A].min]);
        _d.push([margin.left + spaceBetweenChr,data.B.rect[d.B].min]);
        _d.push([margin.left + spaceBetweenChr,data.B.rect[d.B].max]);
        _d.push([margin.left + chrWidth,data.A.rect[d.A].max]);
        return _d;
    });

	var width  = +svg.attr("width") - margin.left - margin.right;
	var height = +svg.attr("height") - margin.top - margin.bottom;

	let scale_A = d3.scaleLinear().domain([data.A.min, data.A.max]).range([margin.top, margin.top + chrHeight]); // 100,700
	let scale_B = d3.scaleLinear().domain([data.B.min, data.B.max]).range([margin.top, margin.top + chrHeight]); // 100,700

    var yrange = scale_A.range();
	myZoom.on("zoom", function(){
    	var fullsize = yrange[1]-yrange[0];
    	var newsize = fullsize*d3.event.transform.k;
    	var offset = newsize/2;
    	var newrange = [yrange[0]-offset,yrange[1]+offset];
    	scale_A.range(newrange);
		scale_B.range(newrange);
        drawFunction();
	});

	// draw chr A and B

	svg.append('rect')
		.attr('x',margin.left)		// 100
		.attr('y',margin.top)		// 100
		.attr('width', chrWidth)	// 30
		.attr('height',chrHeight)	// 600
		.style('stroke','#D7702A')
		.style('fill','#ED7C30')
		
	svg.append('text')
        .classed('textA',true)
        .attr('x',margin.left + 40)       // 90
        .attr('y',margin.top - 40)
        .attr('text-anchor','end')
        .html(data.A.name);
	svg.append('text')
		.classed('textA',true)
		.attr('x',margin.left + 40)
		.attr('y',margin.top - 20)
		.attr('text-anchor','end')
		.html(data.A.min);
	svg.append('text')
        .classed('textA',true)
        .attr('x',margin.left + 40)
        .attr('y',margin.top + chrHeight + 20)
        .attr('text-anchor','end')
        .html(data.A.max);

	svg.append('rect')
		.attr('x',margin.left + spaceBetweenChr)	// 500
		.attr('y',margin.top)						// 100
		.attr('width',chrWidth)						// 30
		.attr('height',chrHeight)					// 600
		.style('stroke','#4C84B7')
		.style('fill','#5A9BD5');

    svg.append('text')
        .classed('textB',true)
        .attr('x',margin.left + 40 + spaceBetweenChr)       // 90
        .attr('y',margin.top - 40)
        .attr('text-anchor','end')
        .html(data.B.name);
    svg.append('text')
        .classed('textB',true)
        .attr('x',margin.left + 40 + spaceBetweenChr)
        .attr('y',margin.top - 20)
        .attr('text-anchor','end')
        .html(data.B.min);
    svg.append('text')
        .classed('textB',true)
        .attr('x',margin.left + 40 + spaceBetweenChr)
        .attr('y',margin.top + chrHeight + 20)
        .attr('text-anchor','end')
        .html(data.B.max);

    let subRectG = svg.append('g');

  var drawFunction = function(){
	let data_A = data.A.rect;
	let rect_A = subRectG.selectAll(".bar_a")
		.data(Object.keys(data_A));
	rect_A.enter().append('rect')
		.classed('bar_a',true)
		.merge(rect_A)							// position for genes in chrA
		.attr('x',margin.left) 					// 100 
		.attr('y',d=>scale_A(data_A[d].min))	// scale  
		.attr('width',chrWidth)
		.attr('height',d=>scale_A(data_A[d].max)-scale_A(data_A[d].min));
	rect_A.exit().remove();

	let textA = subRectG.selectAll('.textA')
		.data(Object.keys(data_A));
	textA.enter().append('text')
		.classed('textA',true)
		.merge(textA)							// position for txt of all genes in chrA
		.attr('x',margin.left - txtToChr)		// 90
		.attr('y',d=>(scale_A(data_A[d].max)+scale_A(data_A[d].min))/2)  
		.attr('text-anchor','end')
		.html(d=>"<a href=/feature/gene/" + d + " target=_blank>" + d + "</a>");
	textA.exit().remove();

	let data_B = data.B.rect;
	let rect_B = subRectG.selectAll(".bar_b")
		.data(Object.keys(data_B));
	rect_B.enter().append('rect')
		.classed('bar_b',true)
		.merge(rect_B)								// position for genes in chrB
		.attr('x',margin.left + spaceBetweenChr)	// 500
		.attr('y',d=>scale_B(data_B[d].min))		// scale
		.attr('width',chrWidth)
		.attr('height',d=>scale_B(data_B[d].max)-scale_B(data_B[d].min));
	rect_B.exit().remove();

	let textB = subRectG.selectAll('.textB')
		.data(Object.keys(data_B));
	textB.enter().append('text')
		.classed('textB',true)
		.merge(textB)													// position for txt of all genes in chrB
		.attr('x',margin.left + spaceBetweenChr + chrWidth + txtToChr)	// 550 , should be 540
		.attr('y',d=>(scale_B(data_B[d].max)+scale_B(data_B[d].min))/2)
		.attr('text-anchor','start')
		.html(d=>"<a href=/feature/gene/" + d + " target=_blank>" + d + "</a>");
	textB.exit().remove();

	// draw path for synteny 
	let path = d3.path();
	for (var i=0; i< relateD.length; i++) {
		let r = relateD[i];
		path.moveTo(r[0][0],scale_A(r[0][1]));
	    path.lineTo(r[1][0],scale_B(r[1][1]));
	    path.lineTo(r[2][0],scale_B(r[2][1]));
	    path.lineTo(r[3][0],scale_A(r[3][1]));
	}

	path.closePath();
	//subRectG.append('path')
	//	.attr('d',path.toString());

	var pathelement = subRectG.select('.synPath');
	if (pathelement.empty()) pathelement = subRectG.append('path').classed('synPath', true);
	pathelement.attr('d', path.toString());
  }

  drawFunction();


