<!DOCTYPE html>

	<head>
		<title> Image Processing Practice </title>
	</head>
	<body>
		<center>
			<h1> training_image </h1>
			<canvas id = "canvas"  width = 700  height = 400  onmousemove = "getRGBA(event)"  onmouseout = "clearCoordinate()"><\canvas>
		</center>
		<p id = "myCoordinate"></p>
		<p id = "myColorCoordinate"></p>
		</br>
		<p id = "regularShapes"></p>
		<p id = "irregularShapes"></p>

	</body>
</html>

<script type = "text/javascript">

	//These next few lines have to be global =\
	var image = new Image();
	var canvas = document.getElementById('canvas');
	var ctx = canvas.getContext('2d');
	image.onload = function() {ctx.drawImage(image,0,0);};
	image.src = "training0.png";
	var imageData = ctx.getImageData(10,10,40,40);
	canvas.addEventListener('mousemove', getRGBA);
	var segmentImageBool = "true";

	var numberOfRegularShapes = 0;
	var numberOfIrregularShapes = 0;


	var entity =
	{
		anchorPoint:0,
		outlineArray:0
	};
	var enityArray =
	{

	};

	function getRGBA(event)
	{
		var x = event.x;
		var y = event.y;
		var xOrigin = document.getElementById("canvas").offsetLeft;
		var yOrigin = document.getElementById("canvas").offsetTop;
		x = x - xOrigin;
		y = y - yOrigin;
		var RGBA = ctx.getImageData(x,y,1,1);
		var coordinate = "Coordinate: ("+x+","+y+")";
		var colorCoordinate = "RGBA: ("+RGBA.data[0]+","+RGBA.data[1]+","+RGBA.data[2]+","+RGBA.data[3]+")";

		colorCoordinate = colorCoordinate.fontcolor('rgb('+RGBA.data[0]+','+RGBA.data[1]+','+RGBA.data[2]+')');

		document.getElementById("myCoordinate").innerHTML = coordinate;
		document.getElementById("myColorCoordinate").innerHTML = colorCoordinate;

		segmentImage();

		if(segmentImageBool == "true")
			segmentImage();
	}
	function clearCoordinate()
	{
		document.getElementById("myCoordinate").innerHTML = "";
		document.getElementById("myColorCoordinate").innerHTML = "";
	}
	function segmentImage()
	{

		//Algorithm: See a possible entity.
		//Check pixel to the right and immediately to the bottom.
		//If colors are analogous, then it is part of that entity.
		//Do this until outline is finished
		//Check middlesome points to see if it's part of same entity.
		//If the color is black assume it's not an entity

		var canvasWidth = document.getElementById("canvas").width;
		var canvasHeight = document.getElementById("canvas").height;
		var imageInformation = ctx.getImageData(0,0,canvasWidth,canvasHeight);
		var previousColor = {r:0,g:0,b:0,a:0};
		var currentColor = {r:0,g:0,b:0,a:0};
		var anchorPoint = 0;


		//Assume alpha doesn't need to be read, and that the anchor point is always the topmost pixel (you don't have to check for a uppermost pixel)
		for (var i = 0; i < canvasWidth*canvasHeight; i=i+4)
		{
			currentColor.r = imageInformation[i];
			currentColor.g = imageInformation[i+1];
			currentColor.b = imageInformation[i+2];

			if(i != 0)
			{
				if(areColorsAnalogous(currentColor,previousColor) == "true")
				{
					produceEntity(imageInformation,i);
					anchorPoint = i - 4;
				}
			}

			previousColor.r = currentColor.r;
			previousColor.g = currentColor.g;
			previousColor.b = currentColor.b;
		}

	}
	function topPixel(i, desiredArray)
	{
		//Returns rgba information
		var desiredIndex = i - document.getElementById("canvas").width;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function bottomPixel(i, desiredArray)
	{
		//Returns rgba information
		var desiredIndex = i + document.getElementById("canvas").width;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function leftPixel(i, desiredArray)
	{
		var desiredIndex = i - 1;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function rightPixel(i, desiredArray)
	{
		var desiredIndex = i + 1;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function topLeftDiagonalPixel(i,desiredArray)
	{
		//Returns rgba information
 		var desiredIndex = i - document.getElementById("canvas").width - 1;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function topRightDiagonalPixel(i,desiredArray)
	{
		//Returns rgba information
		var desiredIndex = i - document.getElementById("canvas").width - 1;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function bottomLeftDiagonalPixel(i,desiredArray)
	{
		//Returns rgba information
		var desiredIndex = i + document.getElementById("canvas").width - 1;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function bottomRightDiagonalPixel(i,desiredArray)
	{
		//Returns rgba information
		var desiredIndex = i + document.getElementById("canvas").width + 1;
		var desiredColor = {r:desiredArray[desiredIndex],g:desiredArray[desiredIndex+1],b:desiredArray[desiredIndex+2]};

		return desiredColor;
	}
	function areColorsAnalogous(colorOne,colorTwo)
	{
		//Precondition: parameters are RGB(A) values

		var colorThreshold = 40;

		if(Math.abs(colorOne.r - colorTwo.r) < colorThreshold &&
		   Math.abs(colorOne.g - colorTwo.g) < colorThreshold &&
		   Math.abs(colorOne.b - colorTwo.b) < colorThreshould )
		{
			return "true";
		}

		return "false";
	}
	function entityInstance(desiredOutlineArray, desiredAnchor)
	{

	}
	function produceEntity(i,desiredArray)
	{
		//Precondtion: this function is called when anchor point is found. The anchor point is the topmost entity point
		//Postcondition: i in the callee function will remain the same
		//Return value: outline array of shape and if it's irregular or not

		var anchorPoint = i;


	}
	function outermostPixels(i,desiredArray)
	{
		//Precondition: i is an edge pixel
		/*
		   1 2 3
		   4 i 6
		   7 8 9
			*/

		var mainColor = {r:desiredArray[i],g:desiredArray[i+1],b:desiredArray[i+2]};
		var one = topLeftDiagonalPixel(i,desiredArray);
		var two = topPixel(i,desiredArray);
		var three = topRightDiagonalPixel(i,desiredArray);
		var four = leftPixel(i,desiredArray);
		var six = rightPixel(i,desiredArray);
		var seven = bottomLeftDiagonalPixel(i,desiredArray);
		var eight = bottomPixel(i,desiredArray);
		var nine = bottomRightPixel(i,desiredArray);

		
	}

</script>

