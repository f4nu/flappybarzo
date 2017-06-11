<html>
    <head>
        <meta charset="utf-8" />
        <title>FlappyBarzo</title>
		<style>
			body {
				margin: 0;
				padding: 0;
				background-color: #1D1D1D;
			}
			#game {
				overflow: hidden;
			}
			#sprite {
				display: none;
			}
		</style>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script>
				var debug = false;
			
				var points = 0;
				var collisionThreshold = 5;
				var barzoWC = undefined;
				var barzoHC = undefined;
				var barzoXC = undefined;
				var barzoYC = undefined;
				
				var gravity = 4.2;
				var deathGravity = gravity * 1.5;
				var gameTime = 0;
				var groundVelocity = 3;
				var barzoY = 0;
				var flapping = false;
				var flapTime = 0;
				var flapVelocity = 3;
				var airTime = 45;
				var canvas = undefined;
				var context = undefined;
				var title = true;
				var falling = false;
				var gameOver = false;
				var deathFlash = 4;
				var deathTime = 0;
				var pause = true;
				
				var groundOffset = 0;
				var groundY = 450;
				var grass = new Image();
				grass.src = "sprite.php?width=16&height=16&x=444&y=202";
				
				var cloudA = new Image();
				cloudA.src = "sprite.php?width=16&height=16&x=172&y=113";
				var cloudB = new Image();
				cloudB.src = "sprite.php?width=16&height=16&x=189&y=113";
				var cloudC = new Image();
				cloudC.src = "sprite.php?width=16&height=16&x=206&y=113";
							
				var clouds = new Array();
				initClouds(6);
				var pipes = new Array();
				var maxPipes = 70;
				var pipeSpread = 70;
				var pipeSpawnTime = 100;
				var lastPipeTime = 0;
				
				var pipeBottomL = new Image();
				pipeBottomL.src = "sprite.php?width=16&height=16&x=1&y=195";
				var pipeBottomR = new Image();
				pipeBottomR.src = "sprite.php?width=16&height=16&x=18&y=195";
				var pipeTopL = new Image();
				pipeTopL.src = "sprite.php?width=16&height=16&x=1&y=178";
				var pipeTopR = new Image();
				pipeTopR.src = "sprite.php?width=16&height=16&x=18&y=178";
				
				var pipeRTopL = new Image();
				pipeRTopL.src = "sprite.php?width=16&height=16&x=1&y=263";
				var pipeRTopR = new Image();
				pipeRTopR.src = "sprite.php?width=16&height=16&x=18&y=263";
				
				var barzo = new Image();
				barzo.src = "sprite.php?width=13&height=18&x=378&y=210";
				barzoScale = 3;
				barzoY = 450 / 2 - (barzo.height * barzoScale) / 2;
				
				var titleBg = new Image();
				titleBg.src = "images/flappyBarzo.png";
				
				var tap = new Image();
				tap.src = "images/tap.png";
				
				var timerMove = undefined;
				$(document).ready(function() {
					resize(700, 500);
					
					canvas = document.getElementById("game");
					context = canvas.getContext("2d");
					context.webkitImageSmoothingEnabled = false;
					context.mozImageSmoothingEnabled = false;
					context.imageSmoothingEnabled = false;
					timerMove = setInterval(function() { gameLoop(); }, 15);
					$("#game").click(function() {
						barzoFlap();
					});
				});
				function gameLoop() {
					update();
					paint();
				}
				function paint() {
					context.beginPath();
					// sky
					context.clearRect(0, 0, $("#game").attr("width"), $("#game").attr("height"));
					context.rect(0, 0, $("#game").attr("width"), $("#game").attr("height"));
					context.fillStyle = "cyan";
					context.fill();
					
					for (var i = 0; i < clouds.length; i++) {
						drawCloud(clouds[i]);
					}
					
					for (var i = 0; i < pipes.length; i++) {
						drawPipePair(pipes[i], pipeSpread);
					}
					
					// ground
					for (var i = 0; i < 14; i++) {
						context.drawImage(grass, (grass.width * -1) + (grass.width * 4) * i - groundOffset, groundY, grass.width * 4, grass.height * 4);
					}
					
					context.drawImage(barzo, 700 / 2 - ((barzo.width) * barzoScale) / 2, barzoY, barzo.width * barzoScale, barzo.height * barzoScale);
					
					if (title) {
						context.drawImage(titleBg, 700 / 2 - titleBg.width / 2, 120, titleBg.width, titleBg.height);
						if (!gameOver) {
							context.drawImage(tap, 700 / 2 - tap.width / 4, barzoY + 70, tap.width / 2, tap.height / 2);
						}
					}
					
					if (debug) {
						context.beginPath();
						context.moveTo(0, groundY);
						context.lineTo(700, groundY);
						context.lineWidth = 2;
						context.strokeStyle = '#ff0000';
						context.stroke();
						
						
						for (var i = 0; i < pipes.length; i++) {
							var p = pipes[i];
							context.beginPath();
							context.rect(p.x, p.y + pipeSpread, pipeTopL.width * 4 * 2, 150);
							context.rect(p.x, 0, pipeTopL.width * 4 * 2, p.y - pipeSpread);
							context.lineWidth = 2;
							context.strokeStyle = '#ff0000';
							context.stroke();
						}
						
						context.beginPath();
						context.rect((700 / 2 - ((barzo.width) * barzoScale) / 2), barzoY, barzo.width * barzoScale, barzo.height * barzoScale);
						context.lineWidth = 2;
						context.strokeStyle = '#00ff00';
						context.stroke();
						
						context.beginPath();
						context.rect(barzoXC, barzoYC, barzoWC, barzoHC);
						context.lineWidth = 2;
						context.strokeStyle = '#ff0000';
						context.stroke();
						
						context.beginPath();
						context.moveTo(700 / 2, 0);
						context.lineTo(700 / 2, 500);
						context.moveTo(0, 500/2);
						context.lineTo(700, 500 / 2);
						context.lineWidth = 2;
						context.strokeStyle = '#ff0000';
						context.stroke();
					}
					
					// death flash
					if (gameOver) {
						if (deathTime + deathFlash >= gameTime) {
							var deathFrame = gameTime - deathTime;
							var alpha = 1;
							if (deathFrame == 1) alpha = 0.5;
							if (deathFrame == 2 || deathFrame == 3) alpha = 1;
							if (deathFrame == 4) alpha = 0.5;
							context.beginPath();
							context.rect(0, 0, 700, 500);
							context.fillStyle = "rgba(255, 255, 255, " + alpha + ")";
							context.fill();
						}
					}
				}
				function update() {
					// clouds
					for (var i = 0; i < clouds.length; i++) {
						var c = clouds[i];
						c.x = c.x - c.speed;
						if (c.x < 0 - (c.size + 2) * cloudA.width * 3) {
							randomizeCloud(c);
							c.x = 700;
						}
					}
					
					// pipes
					if (!gameOver && !title) {
						if (pipes.length < maxPipes && gameTime - lastPipeTime >= pipeSpawnTime) {
							var newPipe = new Object();
							newPipe.size = 4;
							newPipe.x = 700;
							newPipe.y = 125 + Math.random() * 175;
							newPipe.id = (Math.random() + 1).toString(36).substring(2);
							newPipe.inside = false;
							pipes.push(newPipe);
							lastPipeTime = gameTime;
						}
						for (var i = 0; i < pipes.length; i++) {
							p = pipes[i];
							p.x = p.x - groundVelocity;
						}
						for (var i = 0; i < pipes.length; i++) {
							p = pipes[i];
							if (p.x <= 0 - 16*4*2) {
								pipes.splice(pipes.indexOf(p), 1);
							}
						}
					}
					
					if (!title || gameOver) {
						//barzorati
						if (flapping) {
							if (gameTime - flapTime >= airTime) {
								flapping = false;
							} else {
								var velocity = 1;
								if (gameTime - flapTime <= airTime / 2) {
									var x = (gameTime - flapTime);
									var r = airTime / 2;
									velocity = Math.sqrt(r * r - x * x);
									velocity = velocity / (airTime / 2);
								} else {
									var x = airTime - (gameTime - flapTime);
									var r = airTime / 2;
									velocity = Math.sqrt(Math.abs(r * r - x * x));
									velocity = -1 * velocity / (airTime / 2);
								}
								barzoY = barzoY - gravity * velocity;
							}
						} else {
							barzoY = barzoY + gravity;
						}
						if (barzoY + barzo.height * barzoScale >= groundY) barzoY = groundY - barzo.height * barzoScale;
					}
					// ground
					if (!gameOver) {
						groundOffset += groundVelocity;
					}
					if (groundOffset >= grass.width * 4) groundOffset = 0;

					if (isColliding()) {
						gameover();
					}
					
					gameTime++;
				}
				function resize(w, h) {
					$("#game").attr("height", h).attr("width", w);
					$("#game").css("width", w).css("height", h);
					canvas = document.getElementById("game");
					context = canvas.getContext("2d");
				}
				function initClouds(maxClouds) {
					for (var i = 0; i < maxClouds; i++) {
						var c = new Object();
						c.x = Math.random() * 700;
						randomizeCloud(c);
						clouds[i] = c;
					}
				}
				function randomizeCloud(c) {
					c.size = Math.floor(Math.random() * 2) + 1;
					c.y = Math.random() * 150;
					c.speed = Math.random() * 0.5 + 0.3;
				}
				function drawCloud(c) {
					context.drawImage(cloudA, c.x, c.y, cloudA.width * 3,  cloudA.height * 3);
					for (var j = 1; j <= c.size; j++) {
						context.drawImage(cloudB, c.x + (cloudB.width * 3) * j, c.y, cloudB.width * 3,  cloudB.height * 3);
					}
					context.drawImage(cloudC, c.x + (cloudC.width * 3) * (c.size + 1), c.y, cloudC.width * 3,  cloudC.height * 3);
				}
				function drawPipePair(p, offset) {
					drawPipe(p, offset, true);
					drawPipe(p, offset, false);
				}
				function drawPipe(p, offset, reverse) {
					if (!reverse) {
						context.drawImage(pipeTopL, p.x, offset + p.y, pipeTopL.width * 4, pipeTopL.height * 4);
						context.drawImage(pipeTopR, p.x + pipeTopR.width * 4, offset + p.y, pipeTopR.width * 4, pipeTopR.height * 4);
						for (var i = 1; i <= p.size + 1; i++) {
							context.drawImage(pipeBottomL, p.x, offset + p.y + pipeBottomL.height * 4 * i, pipeBottomL.width * 4, pipeBottomL.height * 4);
							context.drawImage(pipeBottomR, p.x + pipeBottomR.width * 4, offset + p.y + pipeBottomR.height * 4 * i, pipeBottomR.width * 4, pipeBottomR.height * 4);
						}
					} else {
						context.drawImage(pipeRTopL, p.x, p.y - pipeRTopL.height * 4 - offset, pipeRTopL.width * 4, pipeRTopL.height * 4);
						context.drawImage(pipeRTopR, p.x + pipeRTopR.width * 4, p.y - pipeRTopL.height * 4 - offset, pipeRTopR.width * 4, pipeRTopR.height * 4);
						for (var i = 1; i <= p.size + 1; i++) {
							context.drawImage(pipeBottomL, p.x, p.y - pipeBottomL.height * 4 * (i + 1) - offset, pipeBottomL.width * 4, pipeBottomL.height * 4);
							context.drawImage(pipeBottomR, p.x + pipeBottomR.width * 4, p.y - pipeBottomR.height * 4 * (i + 1) - offset, pipeBottomR.width * 4, pipeBottomR.height * 4);
						}
					}
				}
				function barzoFlap() {
					if (gameOver) {
						location.reload();
						return;
					}
					if (title) {
						lastPipeTime = gameTime;
						pause = false;
						title = false;
						gameOver = false;
						falling = false;
					}
					flapping = true;
					flapTime = gameTime;
				}
				function gameover() {
					if (deathTime == 0) deathTime = gameTime;
					gravity = deathGravity;
					flapping = false;
					gameOver = true;
					title = true;
				}
				function pause() {
					title = true;
				}
				function isColliding() {
					barzoWC = barzo.width * barzoScale - collisionThreshold * 2;
					barzoHC = barzo.height * barzoScale - collisionThreshold * 2;
					barzoXC = (700 / 2 - (barzoWC) / 2);
					barzoYC = barzoY + collisionThreshold;
					if (barzoY + barzo.height * barzoScale >= groundY) return true;
					for (var i = 0; i < pipes.length; i++) {
						var insidePipe = pipes[i].x <= barzoXC + barzoWC && pipes[i].x + (pipeTopL.width * 4 * 2) >= barzoXC;
						if (insidePipe) {
							pipes[i].inside = true;
							var insidePipeV = barzoYC + barzoHC >= pipes[i].y + pipeSpread || pipes[i].y - pipeSpread >= barzoYC;
							if (insidePipeV)
								return true;
						}
						if (pipes[i].inside && !insidePipe) {
							addPoint();
							pipes[i].inside = false;
						}
					}
					return false;
				}
				function addPoint() {
					points = points + 1;
				}
		</script>
    </head>
    <body>
		<canvas id="game"></canvas>
    </body>
</html>
