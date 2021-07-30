$(function () {
	var user = [];
	var game = [];
	var activedigit = 0;
	var level = '';
	var theme = 'light';
	var number = '';
	var time = 0;
	timeInterval = '';
	var serverURL = 'server.php';

	var themes = [];
	themes['light'] = 'Light';
	themes['dark'] = 'Dark';
	themes['wood'] = 'Wood';
	themes['tiles'] = 'Tiles';
	for (var t in themes) {
		$("#themes").append('<div class="settheme" data-theme="'+t+'"><img src="theme-'+t+'.jpg" alt="'+themes[t]+'"><div>'+themes[t]+'</div></div>');
	}


	if (getCookie('theme')) {
		theme = getCookie('theme');
	}
	$('head').append('<link href="style_'+theme+'.css?'+Date.now()+'" rel="stylesheet" type="text/css" id="theme" />');
	$(".settheme").removeClass('active');
	$(".settheme[data-theme="+theme+"]").addClass('active');


	if (number = getCookie('number')) {
		$.getJSON( serverURL, { action: "resume", number: number } )
		.done(function( json ) {
			if (json['error']) {
				if (json['error']=='usernotfound') {
					gameOver();
					$(".window").hide();
					$("#gamestart").show();
				}
			} else {
				level = json['user']['level'];
				user = json['user'];
				game = json['game'];
				setCookie('number', user['number'], { expires: 2592000, sameSite: 'strict' });
				$(".window").hide();
				$('#gamepad').show();
				drawArea();
				insertCells(game);
				gameSizes();
				time = game['time'];
				timeInterval = setInterval(function () {
					time++;
					$("#time span").text(sec2time(time));
				}, 1000);
			}
		})
		.fail(function( jqxhr, textStatus, textError ) {
			var error = ['<p>' + textStatus + '</p>', '<p>' + textError + '</p>'];
			showerror(error);
		});
	} else {
		$(".window").hide();
		$("#gamestart").show();
	}



	$(".levelButton").click(function (e) {
		e.preventDefault();
		level = $(this).data('level');
		$(".window").hide();
		$("#connecting").show();
		$.getJSON( serverURL, { action: "start", level: level, theme: theme } )
		.done(function( json ) {
			if (json['error']) {
				showerror(json['error']);
			} else {
				user = json['user'];
				game = json['game'];
				setCookie('number', user['number'], { expires: 2592000, sameSite: 'strict' });
				$(".window").hide();
				$('#gamepad').show();
				drawArea();
				insertCells(game);
				gameSizes();
				time = 0;
				timeInterval = setInterval(function () {
					time++;
					$("#time span").text(sec2time(time));
				}, 1000);
			}
		})
		.fail(function( jqxhr, textStatus, textError ) {
			var error = ['<p>' + textStatus + '</p>', '<p>' + textError + '</p>'];
			showerror(error);
		});
	});


	$(".nextgame").click(function (e) {
		e.preventDefault();
		$(".window").hide();
		$("#connecting").show();
		$.getJSON( serverURL, { action: "start", level: level, theme: theme } )
		.done(function( json ) {
			if (json['error']) {
				showerror(json['error']);
			} else {
				user = json['user'];
				game = json['game'];
				setCookie('number', user['number'], { expires: 2592000, sameSite: 'strict' });
				$(".window").hide();
				$('#gamepad').show();
				drawArea();
				insertCells(game);
				gameSizes();
				time = 0;
				timeInterval = setInterval(function () {
					time++;
					$("#time span").text(sec2time(time));
				}, 1000);
			}
		})
		.fail(function( jqxhr, textStatus, textError ) {
			var error = ['<p>' + textStatus + '</p>', '<p>' + textError + '</p>'];
			showerror(error);
		});
	});


	$(".newstart").click(function (e) {
		gameOver();
		$(".window").hide();
		$("#gamestart").show();
	});


	$(".settheme").click(function (e) {
		theme = $(this).data('theme');
		setCookie('theme', theme, { expires: 2592000, sameSite: 'strict' });
		$('#theme').remove();
		$('head').append('<link href="style_'+theme+'.css?'+Date.now()+'" rel="stylesheet" type="text/css" id="theme" />');
		$(".settheme").removeClass('active');
		$(".settheme[data-theme="+theme+"]").addClass('active');
	});


	function showerror(error) {
		$(".window").hide();
		$("#error").show();
		$("#error div").empty();
		$(error).each(function(index, item){
			$("#error div").append('<p>' + item + '</p>');
		});
	}


	function sec2time(timeInSeconds) {
		var pad = function(num, size) {
			return ('000' + num).slice(size * -1);
		},
		time = parseFloat(timeInSeconds).toFixed(3),
		hours = Math.floor(time / 60 / 60),
		minutes = Math.floor(time / 60) % 60,
		seconds = Math.floor(time - minutes * 60),
		milliseconds = time.slice(-3);
		if (hours > 0) return hours + ':' + pad(minutes, 2) + ':' + pad(seconds, 2);
		else return minutes + ':' + pad(seconds, 2);
	}


	function drawArea() {
		for (let sq = 0; sq < 9; sq++) {
			$('#gamearea').append('<div class="squarepre"><div class="square" id="s' + sq + '"></div></div>');
		}
		for (let n = 0; n < 81; n++) {
			sq = Math.floor(n % 9 / 3) + Math.floor(n / 27) * 3; // N -> SQ
			$('#s' + sq).append('<div class="precell"><div class="cell empty" data-n="' + n + '"></div></div>');
		}

	}


	function insertCells(e) {

		for (let n = 0; n < 81; n++) {
			if (e.ustr[n] != '0') {
				$('div[data-n="' + n + '"]').removeClass('empty').addClass('filled').attr('data-di', e.ustr[n]).text(e.ustr[n]);
				// numqty[e.ustr[n]]--;
			}
		}

		$('div#emptycells span').text(e.emptyqty);
		$('div#mistakes span').text(e.mistakes);
		$('div#level').text(e.levelname);

		for (let u = 1; u <= 9; u++) {
			$('#helper').append('<div class="cellcheckpre"><div class="cellcheck' + (e.numqty[u]>0?' filled':'') + '" data-u="' + u + '">' + u + (e.numqty[u]>0?'<span>'+e.numqty[u]+'</span>':'') + '</div></div>');
			if (activedigit == 0 && e.numqty[u] > 0) activedigit = u;
		}

		$('div.cell[data-di="' + activedigit + '"]').addClass('active');
		$('div.cellcheck[data-u="' + activedigit + '"]').addClass('active');

		$('.cellcheck.filled').on('click', function () {
			activedigit = $(this).data('u');
			$('.cellcheck').removeClass('active');
			$('div.cellcheck[data-u="' + activedigit + '"]').addClass('active');
			$('div.cell').removeClass('active');
			$('div.cell[data-di="' + activedigit + '"]').addClass('active');
		});


		$(document.body).on('mouseover', '.empty', function () {
			$(this).text(activedigit);
		}).on('mouseout', '.empty', function () {
			$(this).text('');
		});


		$(document.body).on('click', '.empty', function () {
			let n = $(this).data('n');
			$.getJSON( serverURL, { action: "step", number: user['number'], n: n, digit: activedigit } )
			.done(function( json ) {
				if (json['error']) {
					if (json['error']=='usernotfound') {
						gameOver();
						$(".window").hide();
						$("#gamestart").show();
					}
				} else {
					game = json['game'];
					setCookie('number', user['number'], { expires: 2592000, sameSite: 'strict' });

					if (json['alert']=='stepright') {
						stepRight(game);
					} else if (json['alert']=='stepwrong') {
						stepWrong(game);
					}

					if (game['emptyqty']==0 || json['alert']=='win') {
						$("#gwtime").text(sec2time(game['time']));
						$("#gwmistakes").text(game['mistakes']);
						gameOver();
						$(".window").hide();
						$("#gamewin").fadeIn();
					}
					if (game['mistakes']>3 || json['alert']=='fail') {
						$("#gftime").text(sec2time(game['time']));
						$("#gfmistakes").text(game['mistakes']);
						gameOver();
						$(".window").hide();
						$("#gamefail").fadeIn();
					}

				}
			})
			.fail(function( jqxhr, textStatus, textError ) {
				var error = ['<p>' + textStatus + '</p>', '<p>' + textError + '</p>'];
				showerror(error);
			});
		});

	}


	function stepRight(e) {
		$('.empty[data-n=' + e.n + ']').removeClass('empty').addClass('filled').addClass('active').attr('data-di', e.digit).text(e.digit);
		if (e.numqty[e.digit] == 0) {
			$('div.cell[data-di="' + e.digit + '"]').fadeOut(200).fadeIn(200);
			$('.cellcheck[data-u="' + e.digit + '"] span').remove();
			$('.cellcheck[data-u="' + e.digit + '"]').removeClass('filled');
		} else {
			$('.cellcheck[data-u="' + e.digit + '"] span').text(e.numqty[e.digit]);
		}
		$('div#emptycells span').text(e.emptyqty);
	}


	function stepWrong(e) {
		// $('.empty[data-n=' + e.n + ']').addClass('wrong').delay(800).removeClass('wrong');
		$('.empty[data-n=' + e.n + ']').fadeOut(200).fadeIn(200);
		$('div#mistakes span').text(e.mistakes);
	}


	function gameOver() {
		clearInterval(timeInterval);
		deleteCookie('number');
		user = [];
		game = [];
		activedigit = 0;
		$(document.body).off('click');
		$(document.body).off('mouseover');
		$(document.body).off('mouseout');
		$('#gamearea').empty();
		$('#helper').empty();
		$("#emptycells span").text('0');
		$("#mistakes span").text('0');
		$("#time span").text('0:00');
		$("#level").text('');
	}


	function gameSizes() {
		var windowWidth = $(window).width();
		var windowHeight = $(window).height();

		// var areaWidth = $('#gamepad').outerWidth();
		var areaWidth = $('#gamearea').outerWidth();
		var squarepreSize = Math.floor(areaWidth / 3);
		$('.squarepre').outerWidth(squarepreSize).outerHeight(squarepreSize);

		var squareWidth = $('.square').width();
		var precellSize = Math.floor(squareWidth / 3);
		$('.precell').outerWidth(precellSize).outerHeight(precellSize);

		var cellWidth = $('.cell').width();
		$('.cell').css("font-size", Math.ceil(cellWidth / 1.2) + "px");

		var helperWidth = $('#helper').width();
		var cellcheckSize = Math.floor(helperWidth / 9);
		$('.cellcheckpre').outerWidth(cellcheckSize).outerHeight(cellcheckSize);

		var cellcheckWidth = $('.cellcheck').width();
		$('.cellcheck').css("font-size", Math.ceil(cellcheckWidth / 1.2) + "px");
	}


	window.addEventListener("resize", gameSizes);
});






// document.cookie = "userName=Name";
function setCookie(name, value, options) {
	options = options || {};
	var expires = options.expires;
	if (typeof expires == "number" && expires) {
		var d = new Date();
		d.setTime(d.getTime() + expires * 1000);
		expires = options.expires = d;
	}
	if (expires && expires.toUTCString) {
		options.expires = expires.toUTCString();
	}
	value = encodeURIComponent(value);
	var updatedCookie = name + "=" + value;
	for (var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}
	document.cookie = updatedCookie;
}


function getCookie(name) {
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}


function deleteCookie(name) {
	setCookie(name, "", { expires: -1 });
}
