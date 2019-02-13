<!DOCTYPE html>

	<head>
		<title> Breakout Clone </title>

	</head>

	<body onload = "printCanvas();">
		<h1 align = "center" > Breakout </h1>

		<center>
			<canvas id = "canvas" width = "700" height = "700">Your browser doesn't seem to support HTML 5 =/ </canvas>
		</center>

	</body>

</html>


<script type = "text/javascript">

			//List of necessary constants
			const leftIndentation = 28;
			const rightIndentation = 660;
			const topIndentation = 60;
			const brickXSpacing = 10;
			const brickYSpacing = 10;

			var canvas = document.getElementById('canvas');
			var brickArray = [];
			var programStartTime = Date.now();
			var FPSCallTime = 0;
			var frameCallBack = setInterval(updateFrame, 16.6);
			var gameOverScreenCallBack = null;

			var brick =
			{
				width: 50,
				height: 20
			};

			var paddle =
			{
				width: 100,
				height: 20,
				x: 0,
				y: 0
			};

			var ball =
			{
				width: 10,
				height: 10,
				x: 0,
				y: 0,
				v_x: 0,
				v_y: 0
			};
			var scoreBoardGeneralInfo =
			{
				lives: 3,
				score: 0,
				frames: 0,
				fps:0,
				affectedBrick:0,
				firstCall: "true",
				bricksDestroyed: 0
			};
			var timer =
			{
				minutes: 0,
				seconds: 0,
				centiseconds: 0,
				milliseconds: 0,
				lastCall: programStartTime
			};

			function printCanvas()
			{
				var programTimer = Date.now();

				if(canvas.getContext)
				{
					var ctx = canvas.getContext('2d');

					ctx.fillStyle = 'black';
					ctx.fillRect(0,0,700,700);
				}

				initializeBallVelocity();
				initializeBreakoutLayout();
				document.addEventListener("keydown", gameControls);
				printBall(canvas.width/2,440);
				printPaddle(canvas.width/2-45,600);
				printBreakoutLayout();
				printCurrentLives();

				//promptUser();
				printReady();
				wait(1000);
				eraseText(canvas.width/2,canvas.height/2);
				printGo();
				wait(1000);
				eraseText(canvas.width/2,canvas.height/2);

				//setInterval(updateFrame,16.6);
				timer.milliseconds = Date.now() - programStartTime;
				updateTimer();
				displayScore();
				frames++;
				//requestAnimationFrame(updateFrame);

				if(Date.now()-programTimer > 1000) wait(100);
				else wait(Date.now()-programTimer);

			}
			function updateFrame()
			{
				var utilityString = null;
				var programTimer = Date.now();

				printBreakoutLayout();
				printBall(ball.x+ball.v_x,ball.y+ball.v_y);
				printPaddle(paddle.x,paddle.y);
				printCurrentLives();

				utilityString = collisionOccured();
				if(utilityString != null)
				{
					if(utilityString == "BoP")
						determineBoPDirection();
					if(utilityString == "BoB")
						determineBoBDirection();
					if(utilityString == "BoW")
						determineBoWDirection();
				}

				checkIfBallDrowned();
				if(scoreBoardGeneralInfo.timer >= 60)
				{
					scoreBoardGeneralInfo.fps = frames / (scoreBoardGeneralInfo.timer);
				}

				eraseText(20,20);
				eraseText(40,20);
				eraseText(60,20);
				eraseText(80,20);
				eraseText(100,20);
				eraseText(120,20);
				eraseText(140,20);
				displayScore();

				scoreBoardGeneralInfo.frames++;
				updateTimer();
				if((Date.now()-FPSCallTime)/1000 > 1)
				{
					eraseText(canvas.widht/2,20);
					displayFPS();
					FPSCallTime = Date.now();
				}

				//requestAnimationFrame(updateFrame);

				if(Date.now()-programTimer > 1000) wait(100);
				else wait(Date.now()-programTimer);
			}
			function printBrick(x,y,r,g,b)
			{
				var brickInstance = canvas.getContext('2d');
				brickInstance.fillStyle = 'rgb( ' + r +',' + g + ','+b +')';
				brickInstance.fillRect(x,y,50,20);
			}
			function printPaddle(x,y)
			{
				//Note: this function will work with or without being called from updateFrame()
				var paddleInstance = canvas.getContext('2d');

				//Erase current paddle rect
				paddleInstance.fillStyle = "#000000";
				paddleInstance.fillRect(paddle.x,paddle.y,paddle.width,paddle.height);

				paddleInstance.fillStyle = "#fbfbfb";
				paddleInstance.fillRect(x,y,paddle.width,paddle.height);

				//Update paddle coordinates
				paddle.x = x;
				paddle.y = y;
			}
			function printBall(x,y)
			{
				var ballInstance = canvas.getContext('2d');

				//Erase current ball rect
				ballInstance.fillStyle = "#000000";
				ballInstance.fillRect(ball.x,ball.y,ball.width,ball.height);

				ballInstance.fillStyle = "#fbfbfb";
				ballInstance.fillRect(x,y,ball.width,ball.height);

				//Update ball coordinates
				ball.x = x;
				ball.y = y;

			}
			function brickInstance(x,y,width,height,destroyed,r,g,b)
			{
				this.x = x;
				this.y = y;
				this.width = width;
				this.height = height;
				this.destroyed = destroyed;
				this.r = r;
				this.g = g;
				this.b = b;
			}
			function printBreakoutLayout()
			{
				for(var i = 0; i < brickArray.length; i++)
					printBrick(brickArray[i].x,brickArray[i].y,brickArray[i].r,brickArray[i].g,brickArray[i].b);

			}
			function initializeBreakoutLayout()
			{
				/*Color Codes
				rgb(255,0,0) == red
				rgb(0,255,0) == green
				rgb(0,0,255) == blue
				rgb(187,255,255) == cyan
				rgb(255,0,255) == magenta
				rgb(250,240,0) == yellow
				*/

				if(scoreBoardGeneralInfo.firstCall == "true")
				{
					scoreBoardGeneralInfo.firstCall = "false";

					var arrayIndex = 0;
					var colorArray = [{r:255,g:0,b:0},
							  {r:0,g:255,b:0},
							  {r:0,g:0,b:255},
						 	 {r:187,g:255,b:255},
						 	 {r:255,g:0,b:255},
						  	{r:256,g:256,b:0}];


					for(var row = topIndentation; row <= 210; row += brick.height+brickYSpacing)
					{
						for(var column = leftIndentation; column < rightIndentation; column += brick.width+brickXSpacing)
						{
							brickArray.push(new brickInstance(column,row,brick.width,brick.height,"false",colorArray[arrayIndex].r,colorArray[arrayIndex].g,colorArray[arrayIndex].b));
						}
						arrayIndex++;
					}
				}


			}
			function movePaddle(desiredDirection)
			{
				//Precondition: desiredDirection is the direction in the x direction
				//one wants to move the paddle.

				if (desiredDirection == "left")
				{
					printPaddle(paddle.x-10,paddle.y);
				}
				if (desiredDirection == "right")
				{
					printPaddle(paddle.x+10,paddle.y);
				}

			}
			function initializeBallVelocity()
			{
				var myRandomNumber = Math.random();
				var possibleX = 2;
				var possibleY = 4;

				if(0 <  myRandomNumber && myRandomNumber < .2)
				{
					ball.v_x = -possibleX;
					ball.v_y = possibleX;
				}
				if(.2 < myRandomNumber && myRandomNumber < .4)
				{
					ball.v_x = -possibleX;
					ball.v_y = possibleY;
				}
				if(.4 < myRandomNumber && myRandomNumber < .6)
				{
					ball.v_x = 0;
					ball.v_y = possibleY;
				}
				if(.6 < myRandomNumber && myRandomNumber < .8)
				{
					ball.v_x = possibleX;
					ball.v_y = possibleY;
				}
				if(.8 < myRandomNumber && myRandomNumber < 1)
				{
					ball.v_x = possibleX;
					ball.v_y = possibleX;
				}
			}
			function gameControls(desiredEvent, exitFlagCalled)
			{
				/*a == 65
				  w == 87
				  d == 68
				  s == 83
				  escp == 27
				  SPACE == 32
				*/

				var ctx = canvas.getContext('2d');
				ctx.fillStyle = "black";

				switch(desiredEvent.keyCode)
				{
					case 65:
						if(paddle.x > 0) movePaddle("left");
						//alert("Move left called");
					break;
					case 87:
						//Do nothing
					break;
					case 68:
						if(paddle.x+paddle.width < 700) movePaddle("right");
						//alert("Move right called");
					break;
					case 83:
						//Do nothing
					break;
					case 27:
						exitFlagCalled = true;
						alert("Exit flag called");
					break;
					case 32:
						ctx.fillRect(0,0,canvas.width,canvas.height);
						clearInterval(gameOverScreenCallBack);
						printCanvas();
					break;
				}
			}
			function collisionOccured()
			{
				/*Note: One of two collisions are possible ball-on-paddle (BoP)
				  and ball-on-brick (BoB). Obviously the both those instances is
				  not possible thus is a pre condition. Also, in canvas element
				  the top-left is the origin bottom-right is (canvas.width, can-
				  vas.height).

				  Precondition: None. This function will called after every render

				  Postcondtion: Either return "BoP" or "BoB or BoW"*/

				var brickInstance = null;
				var bottomLine = 220;

				//Check for BoP using axis aligned bounding box
				if(ball.x < paddle.x + paddle.width &&
				   paddle.x < ball.x + ball.width &&
				   ball.y < paddle.y + paddle.height &&
				   paddle.y < ball.y + ball.height)
				{
					return "BoP";
				}

				//Check for BoB
				for(var i = 0; i < brickArray.length; i += 11)
				{
					brickInstance = brickArray[i];

					if(ball.y <= brickInstance.y+brickInstance.height)
					{
						for(var j = i; j < i+11; j++)
						{
							brickInstance = brickArray[j];
							if(ball.x <= brickInstance.x+brickInstance.width && ball.x+ball.width >=brickInstance.x && brickInstance.destroyed == "false")
							{
								scoreBoardGeneralInfo.affectedBrick = j;
								scoreBoardGeneralInfo.score += 200;
								scoreBoardGeneralInfo.bricksDestroyed++;

								brickArray[j].destroyed = "true";
								brickArray[j].r = 0;
								brickArray[j].g = 0;
								brickArray[j].b = 0;

								printBrick(brickArray[j].x,brickArray[j].y,0,0,0);

								return "BoB";
							}
						}
					}
				}

				//Check for BoW
				if(ball.x<=0 || ball.x+ball.width>=700 || ball.y<=0)
					return "BoW"

				return null;
			}
			function determineBoBDirection()
			{
				//The center of the paddle will make the ball move straight up.
				//The extreme ends will make the ball go almost completely left
				//or almost completely right

				//Precondition: This function will only be called if the ball and paddle collided.

				var collidedBrick = brickArray[scoreBoardGeneralInfo.affectedBrick];

				//Right Impact
				if(ball.x<=collidedBrick.x+collidedBrick.width && ball.x+ball.width>collidedBrick.x+collidedBrick.width);
					ball.v_x *= -1;
				//Left Impact
				if(ball.x+ball.width>=collidedBrick.x && ball.x<collidedBrick.x)
					ball.v_x *= -1;
				//Top Impact
				if(ball.y+ball.height>=collidedBrick.y && ball.y < collidedBrick.y)
					ball.v_y *= -1;
				//Bottom Impact
				if(ball.y<=collidedBrick.y+collidedBrick.height && ball.y+ball.height > collidedBrick.y+collidedBrick.height)
					ball.v_y *= -1;
			}
			function determineBoPDirection()
			{
				//x^2-rx-lx+rl-1
				//y=x_velocity
				//x=ballMidpoint

				var maxXVelocity = 3;

				var x = (2*ball.x+ball.width)/2 - paddle.x;
				var v_x = Math.pow(x-50,3);
				v_x = maxXVelocity*v_x;
				v_x = Math.ceil(v_x/125000);

				ball.v_x = v_x;
				ball.v_y = -ball.v_y;
			}
			function determineBoWDirection()
			{
				if(ball.x <= 0 || ball.x+ball.width >= 700)
				{
					ball.v_x *= -1;
					return;
				}
				if(ball.y <= 0)
				{
					ball.v_y *= -1;
					return;
				}
			}
			function checkIfBallDrowned()
			{
				if(ball.y+ball.height > 700)
				{
					scoreBoardGeneralInfo.lives--;
					if(scoreBoardGeneralInfo.lives == 0)
					{
						printCurrentLives();
						displayGameOverScreen();
					}
					else printCanvas();
				}
			}
			function printGameOver()
			{
				alert("Game Over!");
			}
			function printReady()
			{
				var ctx = canvas.getContext('2d');
				ctx.font = "30px Arial";
				ctx.fillStyle = "white";
				ctx.fillText("Ready", canvas.width/2,canvas.height/2);
				wait(1000);
			}
			function printGo()
			{
				var ctx = canvas.getContext('2d');
				ctx.font = "30px Arial";
				ctx.fillStyle = "white";
				ctx.fillText("Go!", canvas.width/2,canvas.height/2);
				wait(1000);
			}
			function eraseText(x,y)
			{
				var ctx = canvas.getContext('2d');

				ctx.fillStyle = "black";
				ctx.fillRect(x,y-30,90,60);

			}
			function wait(milliseconds)
			{
				if(milliseconds <= 0)
					return;

				var currentTime = new Date().getTime();
				while(currentTime+milliseconds >= new Date().getTime()){}
			}
			function displayFPS()
			{
				var seconds = timer.milliseconds/1000;

				scoreBoardGeneralInfo.fps = scoreBoardGeneralInfo.frames/seconds;
				scoreBoardGeneralInfo.fps = toFixedFloat(scoreBoardGeneralInfo.fps,2);

				eraseText(canvas.width/2,10);

				//This will always print at the top center corner
				var ctx = canvas.getContext('2d');
				ctx.fillStyle = "white";
				ctx.fillText(Math.trunc(scoreBoardGeneralInfo.fps,4),canvas.width/2,40);

			}
			function displayScore()
			{
				var ctx = canvas.getContext('2d');

				ctx.fillStyle = "black";
				eraseText(20,20);

				ctx.fillStyle = "white";
				ctx.fillText("Score:   " +  scoreBoardGeneralInfo.score,30,40);

			}
			function updateTimer()
			{
				//Date.now() returns the number of milliseconds since 1/1/1970
				var elapsedTime = Date.now()-timer.lastCall;

				timer.minutes = Math.floor(elapsedTime/60000);
				elapsedTime = elapsedTime - timer.minutes*60000;

				timer.seconds = Math.floor(elapsedTime/1000);
				elapsedTime = elapsedTime - timer.seconds*1000;

				timer.centiseconds = Math.floor(elapsedTime/100);

				timer.lastCall = Date.now();
			}
			function printCurrentLives()
			{
				var customSpacing = 15;
				var life = canvas.getContext('2d');
				life.fillStyle = "white";

				eraseText(canvas.width-6*customSpacing,20);
				life.fillStyle = "white";

				for(var i = 1; i <= scoreBoardGeneralInfo.lives; i++)
				{
					switch(i)
					{
						case 1:
							life.fillRect(canvas.width-2*customSpacing,20,ball.width,ball.height);
						break;
						case 2:
							life.fillRect(canvas.width-4*customSpacing,20,ball.width,ball.height);
						break;
						case 3:
							life.fillRect(canvas.width-6*customSpacing,20,ball.width,ball.height);
						break;
					}
				}

			}
			function displayGameOverScreen()
			{
				var menuWidth = 300;
				var menuHeight = 300;
				var menuXCoordinate = canvas.width/2-130;
				var menuYCoordinate = 200;

				var ctx = canvas.getContext('2d');
				ctx.fillStyle = 'rgb(0,120,170)';
				ctx.fillRect(menuXCoordinate,menuYCoordinate,menuWidth,menuHeight);
				ctx.fillStyle = 'rgb(130,130,130)';
				ctx.fillRect(menuXCoordinate+5,menuYCoordinate+5,menuWidth-10,menuHeight-10);

				ctx.fillStyle = "#ffffff";
				ctx.fillText("Press [SPACE] to", menuXCoordinate+40, menuYCoordinate+90);
				ctx.fillText("to play again!", menuXCoordinate+70, menuYCoordinate+140);

				clearInterval(frameCallBack);
				gameOverScreenCallBack = setInterval(function(){},1);
				wait(1);

			}
			function toFixedFloat(desiredNumber,numberOfDecimalPoints)
			{
				//This function only truncates floats not concanated more zeroes like toFixed()
				var tenToThe = Math.pow(10, numberOfDecimalPoints);
				var utilityNumber = desiredNumber*tenToThe;
				utilityNumber = Math.trunc(utilityNumber);
				desiredNumber = utilityNumber*Math.pow(10,-numberOfDecimalPoints);

				return desiredNumber;
			}

							/*CheckList

							 1. Fix the prompting animation
							 2. Fix the HUD (lives and FPS)
							 3. Add a nueral network
							 4. Fix collision detection
							 5. Fix framerate issues
							 6. Add a menu to reduce user confusion.
							 8. Add sound
												*/
</script>

