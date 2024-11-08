(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["dateFns"] = factory();
	else
		root["dateFns"] = factory();
})(this, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports, __webpack_require__) {

	module.exports = {
	    addDays: __webpack_require__(1),
	    addHours: __webpack_require__(4),
	    addISOYears: __webpack_require__(6),
	    addMilliseconds: __webpack_require__(5),
	    addMinutes: __webpack_require__(14),
	    addMonths: __webpack_require__(15),
	    addQuarters: __webpack_require__(17),
	    addSeconds: __webpack_require__(18),
	    addWeeks: __webpack_require__(19),
	    addYears: __webpack_require__(20),
	    areRangesOverlapping: __webpack_require__(21),
	    closestIndexTo: __webpack_require__(22),
	    closestTo: __webpack_require__(23),
	    compareAsc: __webpack_require__(24),
	    compareDesc: __webpack_require__(25),
	    differenceInCalendarDays: __webpack_require__(12),
	    differenceInCalendarISOWeeks: __webpack_require__(26),
	    differenceInCalendarISOYears: __webpack_require__(27),
	    differenceInCalendarMonths: __webpack_require__(28),
	    differenceInCalendarQuarters: __webpack_require__(29),
	    differenceInCalendarWeeks: __webpack_require__(31),
	    differenceInCalendarYears: __webpack_require__(32),
	    differenceInDays: __webpack_require__(33),
	    differenceInHours: __webpack_require__(34),
	    differenceInISOYears: __webpack_require__(36),
	    differenceInMilliseconds: __webpack_require__(35),
	    differenceInMinutes: __webpack_require__(38),
	    differenceInMonths: __webpack_require__(39),
	    differenceInQuarters: __webpack_require__(40),
	    differenceInSeconds: __webpack_require__(41),
	    differenceInWeeks: __webpack_require__(42),
	    differenceInYears: __webpack_require__(43),
	    distanceInWords: __webpack_require__(44),
	    distanceInWordsStrict: __webpack_require__(49),
	    distanceInWordsToNow: __webpack_require__(50),
	    eachDay: __webpack_require__(51),
	    endOfDay: __webpack_require__(52),
	    endOfHour: __webpack_require__(53),
	    endOfISOWeek: __webpack_require__(54),
	    endOfISOYear: __webpack_require__(56),
	    endOfMinute: __webpack_require__(57),
	    endOfMonth: __webpack_require__(58),
	    endOfQuarter: __webpack_require__(59),
	    endOfSecond: __webpack_require__(60),
	    endOfToday: __webpack_require__(61),
	    endOfTomorrow: __webpack_require__(62),
	    endOfWeek: __webpack_require__(55),
	    endOfYear: __webpack_require__(63),
	    endOfYesterday: __webpack_require__(64),
	    format: __webpack_require__(65),
	    getDate: __webpack_require__(70),
	    getDay: __webpack_require__(71),
	    getDayOfYear: __webpack_require__(66),
	    getDaysInMonth: __webpack_require__(16),
	    getDaysInYear: __webpack_require__(72),
	    getHours: __webpack_require__(74),
	    getISODay: __webpack_require__(75),
	    getISOWeek: __webpack_require__(68),
	    getISOWeeksInYear: __webpack_require__(76),
	    getISOYear: __webpack_require__(7),
	    getMilliseconds: __webpack_require__(77),
	    getMinutes: __webpack_require__(78),
	    getMonth: __webpack_require__(79),
	    getOverlappingDaysInRanges: __webpack_require__(80),
	    getQuarter: __webpack_require__(30),
	    getSeconds: __webpack_require__(81),
	    getTime: __webpack_require__(82),
	    getYear: __webpack_require__(83),
	    isAfter: __webpack_require__(84),
	    isBefore: __webpack_require__(85),
	    isDate: __webpack_require__(3),
	    isEqual: __webpack_require__(86),
	    isFirstDayOfMonth: __webpack_require__(87),
	    isFriday: __webpack_require__(88),
	    isFuture: __webpack_require__(89),
	    isLastDayOfMonth: __webpack_require__(90),
	    isLeapYear: __webpack_require__(73),
	    isMonday: __webpack_require__(91),
	    isPast: __webpack_require__(92),
	    isSameDay: __webpack_require__(93),
	    isSameHour: __webpack_require__(94),
	    isSameISOWeek: __webpack_require__(96),
	    isSameISOYear: __webpack_require__(98),
	    isSameMinute: __webpack_require__(99),
	    isSameMonth: __webpack_require__(101),
	    isSameQuarter: __webpack_require__(102),
	    isSameSecond: __webpack_require__(104),
	    isSameWeek: __webpack_require__(97),
	    isSameYear: __webpack_require__(106),
	    isSaturday: __webpack_require__(107),
	    isSunday: __webpack_require__(108),
	    isThisHour: __webpack_require__(109),
	    isThisISOWeek: __webpack_require__(110),
	    isThisISOYear: __webpack_require__(111),
	    isThisMinute: __webpack_require__(112),
	    isThisMonth: __webpack_require__(113),
	    isThisQuarter: __webpack_require__(114),
	    isThisSecond: __webpack_require__(115),
	    isThisWeek: __webpack_require__(116),
	    isThisYear: __webpack_require__(117),
	    isThursday: __webpack_require__(118),
	    isToday: __webpack_require__(119),
	    isTomorrow: __webpack_require__(120),
	    isTuesday: __webpack_require__(121),
	    isValid: __webpack_require__(69),
	    isWednesday: __webpack_require__(122),
	    isWeekend: __webpack_require__(123),
	    isWithinRange: __webpack_require__(124),
	    isYesterday: __webpack_require__(125),
	    lastDayOfISOWeek: __webpack_require__(126),
	    lastDayOfISOYear: __webpack_require__(128),
	    lastDayOfMonth: __webpack_require__(129),
	    lastDayOfQuarter: __webpack_require__(130),
	    lastDayOfWeek: __webpack_require__(127),
	    lastDayOfYear: __webpack_require__(131),
	    max: __webpack_require__(132),
	    min: __webpack_require__(133),
	    parse: __webpack_require__(2),
	    setDate: __webpack_require__(134),
	    setDay: __webpack_require__(135),
	    setDayOfYear: __webpack_require__(136),
	    setHours: __webpack_require__(137),
	    setISODay: __webpack_require__(138),
	    setISOWeek: __webpack_require__(139),
	    setISOYear: __webpack_require__(10),
	    setMilliseconds: __webpack_require__(140),
	    setMinutes: __webpack_require__(141),
	    setMonth: __webpack_require__(142),
	    setQuarter: __webpack_require__(143),
	    setSeconds: __webpack_require__(144),
	    setYear: __webpack_require__(145),
	    startOfDay: __webpack_require__(13),
	    startOfHour: __webpack_require__(95),
	    startOfISOWeek: __webpack_require__(8),
	    startOfISOYear: __webpack_require__(11),
	    startOfMinute: __webpack_require__(100),
	    startOfMonth: __webpack_require__(146),
	    startOfQuarter: __webpack_require__(103),
	    startOfSecond: __webpack_require__(105),
	    startOfToday: __webpack_require__(147),
	    startOfTomorrow: __webpack_require__(148),
	    startOfWeek: __webpack_require__(9),
	    startOfYear: __webpack_require__(67),
	    startOfYesterday: __webpack_require__(149),
	    subDays: __webpack_require__(150),
	    subHours: __webpack_require__(151),
	    subISOYears: __webpack_require__(37),
	    subMilliseconds: __webpack_require__(152),
	    subMinutes: __webpack_require__(153),
	    subMonths: __webpack_require__(154),
	    subQuarters: __webpack_require__(155),
	    subSeconds: __webpack_require__(156),
	    subWeeks: __webpack_require__(157),
	    subYears: __webpack_require__(158)
	};
	


/***/ },
/* 1 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function addDays(dirtyDate, dirtyAmount) {
	    var date = parse(dirtyDate);
	    var amount = Number(dirtyAmount);
	    date.setDate(date.getDate() + amount);
	    return date;
	}
	module.exports = addDays;
	


/***/ },
/* 2 */
/***/ function(module, exports, __webpack_require__) {

	var isDate = __webpack_require__(3);
	var MILLISECONDS_IN_HOUR = 3600000;
	var MILLISECONDS_IN_MINUTE = 60000;
	var DEFAULT_ADDITIONAL_DIGITS = 2;
	var parseTokenDateTimeDelimeter = /[T ]/;
	var parseTokenPlainTime = /:/;
	var parseTokenYY = /^(\d{2})$/;
	var parseTokensYYY = [
	    /^([+-]\d{2})$/,
	    /^([+-]\d{3})$/,
	    /^([+-]\d{4})$/
	];
	var parseTokenYYYY = /^(\d{4})/;
	var parseTokensYYYYY = [
	    /^([+-]\d{4})/,
	    /^([+-]\d{5})/,
	    /^([+-]\d{6})/
	];
	var parseTokenMM = /^-(\d{2})$/;
	var parseTokenDDD = /^-?(\d{3})$/;
	var parseTokenMMDD = /^-?(\d{2})-?(\d{2})$/;
	var parseTokenWww = /^-?W(\d{2})$/;
	var parseTokenWwwD = /^-?W(\d{2})-?(\d{1})$/;
	var parseTokenHH = /^(\d{2}([.,]\d*)?)$/;
	var parseTokenHHMM = /^(\d{2}):?(\d{2}([.,]\d*)?)$/;
	var parseTokenHHMMSS = /^(\d{2}):?(\d{2}):?(\d{2}([.,]\d*)?)$/;
	var parseTokenTimezone = /([Z+-].*)$/;
	var parseTokenTimezoneZ = /^(Z)$/;
	var parseTokenTimezoneHH = /^([+-])(\d{2})$/;
	var parseTokenTimezoneHHMM = /^([+-])(\d{2}):?(\d{2})$/;
	function parse(argument, dirtyOptions) {
	    if (isDate(argument)) {
	        return new Date(argument.getTime());
	    } else if (typeof argument !== 'string') {
	        return new Date(argument);
	    }
	    var options = dirtyOptions || {};
	    var additionalDigits = options.additionalDigits;
	    if (additionalDigits == null) {
	        additionalDigits = DEFAULT_ADDITIONAL_DIGITS;
	    } else {
	        additionalDigits = Number(additionalDigits);
	    }
	    var dateStrings = splitDateString(argument);
	    var parseYearResult = parseYear(dateStrings.date, additionalDigits);
	    var year = parseYearResult.year;
	    var restDateString = parseYearResult.restDateString;
	    var date = parseDate(restDateString, year);
	    if (date) {
	        var timestamp = date.getTime();
	        var time = 0;
	        var offset;
	        if (dateStrings.time) {
	            time = parseTime(dateStrings.time);
	        }
	        if (dateStrings.timezone) {
	            offset = parseTimezone(dateStrings.timezone);
	        } else {
	            offset = new Date(timestamp + time).getTimezoneOffset();
	            offset = new Date(timestamp + time + offset * MILLISECONDS_IN_MINUTE).getTimezoneOffset();
	        }
	        return new Date(timestamp + time + offset * MILLISECONDS_IN_MINUTE);
	    } else {
	        return new Date(argument);
	    }
	}
	function splitDateString(dateString) {
	    var dateStrings = {};
	    var array = dateString.split(parseTokenDateTimeDelimeter);
	    var timeString;
	    if (parseTokenPlainTime.test(array[0])) {
	        dateStrings.date = null;
	        timeString = array[0];
	    } else {
	        dateStrings.date = array[0];
	        timeString = array[1];
	    }
	    if (timeString) {
	        var token = parseTokenTimezone.exec(timeString);
	        if (token) {
	            dateStrings.time = timeString.replace(token[1], '');
	            dateStrings.timezone = token[1];
	        } else {
	            dateStrings.time = timeString;
	        }
	    }
	    return dateStrings;
	}
	function parseYear(dateString, additionalDigits) {
	    var parseTokenYYY = parseTokensYYY[additionalDigits];
	    var parseTokenYYYYY = parseTokensYYYYY[additionalDigits];
	    var token;
	    token = parseTokenYYYY.exec(dateString) || parseTokenYYYYY.exec(dateString);
	    if (token) {
	        var yearString = token[1];
	        return {
	            year: parseInt(yearString, 10),
	            restDateString: dateString.slice(yearString.length)
	        };
	    }
	    token = parseTokenYY.exec(dateString) || parseTokenYYY.exec(dateString);
	    if (token) {
	        var centuryString = token[1];
	        return {
	            year: parseInt(centuryString, 10) * 100,
	            restDateString: dateString.slice(centuryString.length)
	        };
	    }
	    return { year: null };
	}
	function parseDate(dateString, year) {
	    if (year === null) {
	        return null;
	    }
	    var token;
	    var date;
	    var month;
	    var week;
	    if (dateString.length === 0) {
	        date = new Date(0);
	        date.setUTCFullYear(year);
	        return date;
	    }
	    token = parseTokenMM.exec(dateString);
	    if (token) {
	        date = new Date(0);
	        month = parseInt(token[1], 10) - 1;
	        date.setUTCFullYear(year, month);
	        return date;
	    }
	    token = parseTokenDDD.exec(dateString);
	    if (token) {
	        date = new Date(0);
	        var dayOfYear = parseInt(token[1], 10);
	        date.setUTCFullYear(year, 0, dayOfYear);
	        return date;
	    }
	    token = parseTokenMMDD.exec(dateString);
	    if (token) {
	        date = new Date(0);
	        month = parseInt(token[1], 10) - 1;
	        var day = parseInt(token[2], 10);
	        date.setUTCFullYear(year, month, day);
	        return date;
	    }
	    token = parseTokenWww.exec(dateString);
	    if (token) {
	        week = parseInt(token[1], 10) - 1;
	        return dayOfISOYear(year, week);
	    }
	    token = parseTokenWwwD.exec(dateString);
	    if (token) {
	        week = parseInt(token[1], 10) - 1;
	        var dayOfWeek = parseInt(token[2], 10) - 1;
	        return dayOfISOYear(year, week, dayOfWeek);
	    }
	    return null;
	}
	function parseTime(timeString) {
	    var token;
	    var hours;
	    var minutes;
	    token = parseTokenHH.exec(timeString);
	    if (token) {
	        hours = parseFloat(token[1].replace(',', '.'));
	        return hours % 24 * MILLISECONDS_IN_HOUR;
	    }
	    token = parseTokenHHMM.exec(timeString);
	    if (token) {
	        hours = parseInt(token[1], 10);
	        minutes = parseFloat(token[2].replace(',', '.'));
	        return hours % 24 * MILLISECONDS_IN_HOUR + minutes * MILLISECONDS_IN_MINUTE;
	    }
	    token = parseTokenHHMMSS.exec(timeString);
	    if (token) {
	        hours = parseInt(token[1], 10);
	        minutes = parseInt(token[2], 10);
	        var seconds = parseFloat(token[3].replace(',', '.'));
	        return hours % 24 * MILLISECONDS_IN_HOUR + minutes * MILLISECONDS_IN_MINUTE + seconds * 1000;
	    }
	    return null;
	}
	function parseTimezone(timezoneString) {
	    var token;
	    var absoluteOffset;
	    token = parseTokenTimezoneZ.exec(timezoneString);
	    if (token) {
	        return 0;
	    }
	    token = parseTokenTimezoneHH.exec(timezoneString);
	    if (token) {
	        absoluteOffset = parseInt(token[2], 10) * 60;
	        return token[1] === '+' ? -absoluteOffset : absoluteOffset;
	    }
	    token = parseTokenTimezoneHHMM.exec(timezoneString);
	    if (token) {
	        absoluteOffset = parseInt(token[2], 10) * 60 + parseInt(token[3], 10);
	        return token[1] === '+' ? -absoluteOffset : absoluteOffset;
	    }
	    return 0;
	}
	function dayOfISOYear(isoYear, week, day) {
	    week = week || 0;
	    day = day || 0;
	    var date = new Date(0);
	    date.setUTCFullYear(isoYear, 0, 4);
	    var fourthOfJanuaryDay = date.getUTCDay() || 7;
	    var diff = week * 7 + day + 1 - fourthOfJanuaryDay;
	    date.setUTCDate(date.getUTCDate() + diff);
	    return date;
	}
	module.exports = parse;
	


/***/ },
/* 3 */
/***/ function(module, exports) {

	function isDate(argument) {
	    return argument instanceof Date;
	}
	module.exports = isDate;
	


/***/ },
/* 4 */
/***/ function(module, exports, __webpack_require__) {

	var addMilliseconds = __webpack_require__(5);
	var MILLISECONDS_IN_HOUR = 3600000;
	function addHours(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addMilliseconds(dirtyDate, amount * MILLISECONDS_IN_HOUR);
	}
	module.exports = addHours;
	


/***/ },
/* 5 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function addMilliseconds(dirtyDate, dirtyAmount) {
	    var timestamp = parse(dirtyDate).getTime();
	    var amount = Number(dirtyAmount);
	    return new Date(timestamp + amount);
	}
	module.exports = addMilliseconds;
	


/***/ },
/* 6 */
/***/ function(module, exports, __webpack_require__) {

	var getISOYear = __webpack_require__(7);
	var setISOYear = __webpack_require__(10);
	function addISOYears(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return setISOYear(dirtyDate, getISOYear(dirtyDate) + amount);
	}
	module.exports = addISOYears;
	


/***/ },
/* 7 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var startOfISOWeek = __webpack_require__(8);
	function getISOYear(dirtyDate) {
	    var date = parse(dirtyDate);
	    var year = date.getFullYear();
	    var fourthOfJanuaryOfNextYear = new Date(0);
	    fourthOfJanuaryOfNextYear.setFullYear(year + 1, 0, 4);
	    fourthOfJanuaryOfNextYear.setHours(0, 0, 0, 0);
	    var startOfNextYear = startOfISOWeek(fourthOfJanuaryOfNextYear);
	    var fourthOfJanuaryOfThisYear = new Date(0);
	    fourthOfJanuaryOfThisYear.setFullYear(year, 0, 4);
	    fourthOfJanuaryOfThisYear.setHours(0, 0, 0, 0);
	    var startOfThisYear = startOfISOWeek(fourthOfJanuaryOfThisYear);
	    if (date.getTime() >= startOfNextYear.getTime()) {
	        return year + 1;
	    } else if (date.getTime() >= startOfThisYear.getTime()) {
	        return year;
	    } else {
	        return year - 1;
	    }
	}
	module.exports = getISOYear;
	


/***/ },
/* 8 */
/***/ function(module, exports, __webpack_require__) {

	var startOfWeek = __webpack_require__(9);
	function startOfISOWeek(dirtyDate) {
	    return startOfWeek(dirtyDate, { weekStartsOn: 1 });
	}
	module.exports = startOfISOWeek;
	


/***/ },
/* 9 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfWeek(dirtyDate, dirtyOptions) {
	    var weekStartsOn = dirtyOptions ? Number(dirtyOptions.weekStartsOn) || 0 : 0;
	    var date = parse(dirtyDate);
	    var day = date.getDay();
	    var diff = (day < weekStartsOn ? 7 : 0) + day - weekStartsOn;
	    date.setDate(date.getDate() - diff);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = startOfWeek;
	


/***/ },
/* 10 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var startOfISOYear = __webpack_require__(11);
	var differenceInCalendarDays = __webpack_require__(12);
	function setISOYear(dirtyDate, dirtyISOYear) {
	    var date = parse(dirtyDate);
	    var isoYear = Number(dirtyISOYear);
	    var diff = differenceInCalendarDays(date, startOfISOYear(date));
	    var fourthOfJanuary = new Date(0);
	    fourthOfJanuary.setFullYear(isoYear, 0, 4);
	    fourthOfJanuary.setHours(0, 0, 0, 0);
	    date = startOfISOYear(fourthOfJanuary);
	    date.setDate(date.getDate() + diff);
	    return date;
	}
	module.exports = setISOYear;
	


/***/ },
/* 11 */
/***/ function(module, exports, __webpack_require__) {

	var getISOYear = __webpack_require__(7);
	var startOfISOWeek = __webpack_require__(8);
	function startOfISOYear(dirtyDate) {
	    var year = getISOYear(dirtyDate);
	    var fourthOfJanuary = new Date(0);
	    fourthOfJanuary.setFullYear(year, 0, 4);
	    fourthOfJanuary.setHours(0, 0, 0, 0);
	    var date = startOfISOWeek(fourthOfJanuary);
	    return date;
	}
	module.exports = startOfISOYear;
	


/***/ },
/* 12 */
/***/ function(module, exports, __webpack_require__) {

	var startOfDay = __webpack_require__(13);
	var MILLISECONDS_IN_MINUTE = 60000;
	var MILLISECONDS_IN_DAY = 86400000;
	function differenceInCalendarDays(dirtyDateLeft, dirtyDateRight) {
	    var startOfDayLeft = startOfDay(dirtyDateLeft);
	    var startOfDayRight = startOfDay(dirtyDateRight);
	    var timestampLeft = startOfDayLeft.getTime() - startOfDayLeft.getTimezoneOffset() * MILLISECONDS_IN_MINUTE;
	    var timestampRight = startOfDayRight.getTime() - startOfDayRight.getTimezoneOffset() * MILLISECONDS_IN_MINUTE;
	    return Math.round((timestampLeft - timestampRight) / MILLISECONDS_IN_DAY);
	}
	module.exports = differenceInCalendarDays;
	


/***/ },
/* 13 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfDay(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = startOfDay;
	


/***/ },
/* 14 */
/***/ function(module, exports, __webpack_require__) {

	var addMilliseconds = __webpack_require__(5);
	var MILLISECONDS_IN_MINUTE = 60000;
	function addMinutes(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addMilliseconds(dirtyDate, amount * MILLISECONDS_IN_MINUTE);
	}
	module.exports = addMinutes;
	


/***/ },
/* 15 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var getDaysInMonth = __webpack_require__(16);
	function addMonths(dirtyDate, dirtyAmount) {
	    var date = parse(dirtyDate);
	    var amount = Number(dirtyAmount);
	    var desiredMonth = date.getMonth() + amount;
	    var dateWithDesiredMonth = new Date(0);
	    dateWithDesiredMonth.setFullYear(date.getFullYear(), desiredMonth, 1);
	    dateWithDesiredMonth.setHours(0, 0, 0, 0);
	    var daysInMonth = getDaysInMonth(dateWithDesiredMonth);
	    date.setMonth(desiredMonth, Math.min(daysInMonth, date.getDate()));
	    return date;
	}
	module.exports = addMonths;
	


/***/ },
/* 16 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getDaysInMonth(dirtyDate) {
	    var date = parse(dirtyDate);
	    var year = date.getFullYear();
	    var monthIndex = date.getMonth();
	    var lastDayOfMonth = new Date(0);
	    lastDayOfMonth.setFullYear(year, monthIndex + 1, 0);
	    lastDayOfMonth.setHours(0, 0, 0, 0);
	    return lastDayOfMonth.getDate();
	}
	module.exports = getDaysInMonth;
	


/***/ },
/* 17 */
/***/ function(module, exports, __webpack_require__) {

	var addMonths = __webpack_require__(15);
	function addQuarters(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    var months = amount * 3;
	    return addMonths(dirtyDate, months);
	}
	module.exports = addQuarters;
	


/***/ },
/* 18 */
/***/ function(module, exports, __webpack_require__) {

	var addMilliseconds = __webpack_require__(5);
	function addSeconds(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addMilliseconds(dirtyDate, amount * 1000);
	}
	module.exports = addSeconds;
	


/***/ },
/* 19 */
/***/ function(module, exports, __webpack_require__) {

	var addDays = __webpack_require__(1);
	function addWeeks(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    var days = amount * 7;
	    return addDays(dirtyDate, days);
	}
	module.exports = addWeeks;
	


/***/ },
/* 20 */
/***/ function(module, exports, __webpack_require__) {

	var addMonths = __webpack_require__(15);
	function addYears(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addMonths(dirtyDate, amount * 12);
	}
	module.exports = addYears;
	


/***/ },
/* 21 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function areRangesOverlapping(dirtyInitialRangeStartDate, dirtyInitialRangeEndDate, dirtyComparedRangeStartDate, dirtyComparedRangeEndDate) {
	    var initialStartTime = parse(dirtyInitialRangeStartDate).getTime();
	    var initialEndTime = parse(dirtyInitialRangeEndDate).getTime();
	    var comparedStartTime = parse(dirtyComparedRangeStartDate).getTime();
	    var comparedEndTime = parse(dirtyComparedRangeEndDate).getTime();
	    if (initialStartTime > initialEndTime || comparedStartTime > comparedEndTime) {
	        throw new Error('The start of the range cannot be after the end of the range');
	    }
	    return initialStartTime < comparedEndTime && comparedStartTime < initialEndTime;
	}
	module.exports = areRangesOverlapping;
	


/***/ },
/* 22 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function closestIndexTo(dirtyDateToCompare, dirtyDatesArray) {
	    if (!(dirtyDatesArray instanceof Array)) {
	        throw new TypeError(toString.call(dirtyDatesArray) + ' is not an instance of Array');
	    }
	    var dateToCompare = parse(dirtyDateToCompare);
	    var timeToCompare = dateToCompare.getTime();
	    var result;
	    var minDistance;
	    dirtyDatesArray.forEach(function (dirtyDate, index) {
	        var currentDate = parse(dirtyDate);
	        var distance = Math.abs(timeToCompare - currentDate.getTime());
	        if (result === undefined || distance < minDistance) {
	            result = index;
	            minDistance = distance;
	        }
	    });
	    return result;
	}
	module.exports = closestIndexTo;
	


/***/ },
/* 23 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function closestTo(dirtyDateToCompare, dirtyDatesArray) {
	    if (!(dirtyDatesArray instanceof Array)) {
	        throw new TypeError(toString.call(dirtyDatesArray) + ' is not an instance of Array');
	    }
	    var dateToCompare = parse(dirtyDateToCompare);
	    var timeToCompare = dateToCompare.getTime();
	    var result;
	    var minDistance;
	    dirtyDatesArray.forEach(function (dirtyDate) {
	        var currentDate = parse(dirtyDate);
	        var distance = Math.abs(timeToCompare - currentDate.getTime());
	        if (result === undefined || distance < minDistance) {
	            result = currentDate;
	            minDistance = distance;
	        }
	    });
	    return result;
	}
	module.exports = closestTo;
	


/***/ },
/* 24 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function compareAsc(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var timeLeft = dateLeft.getTime();
	    var dateRight = parse(dirtyDateRight);
	    var timeRight = dateRight.getTime();
	    if (timeLeft < timeRight) {
	        return -1;
	    } else if (timeLeft > timeRight) {
	        return 1;
	    } else {
	        return 0;
	    }
	}
	module.exports = compareAsc;
	


/***/ },
/* 25 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function compareDesc(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var timeLeft = dateLeft.getTime();
	    var dateRight = parse(dirtyDateRight);
	    var timeRight = dateRight.getTime();
	    if (timeLeft > timeRight) {
	        return -1;
	    } else if (timeLeft < timeRight) {
	        return 1;
	    } else {
	        return 0;
	    }
	}
	module.exports = compareDesc;
	


/***/ },
/* 26 */
/***/ function(module, exports, __webpack_require__) {

	var startOfISOWeek = __webpack_require__(8);
	var MILLISECONDS_IN_MINUTE = 60000;
	var MILLISECONDS_IN_WEEK = 604800000;
	function differenceInCalendarISOWeeks(dirtyDateLeft, dirtyDateRight) {
	    var startOfISOWeekLeft = startOfISOWeek(dirtyDateLeft);
	    var startOfISOWeekRight = startOfISOWeek(dirtyDateRight);
	    var timestampLeft = startOfISOWeekLeft.getTime() - startOfISOWeekLeft.getTimezoneOffset() * MILLISECONDS_IN_MINUTE;
	    var timestampRight = startOfISOWeekRight.getTime() - startOfISOWeekRight.getTimezoneOffset() * MILLISECONDS_IN_MINUTE;
	    return Math.round((timestampLeft - timestampRight) / MILLISECONDS_IN_WEEK);
	}
	module.exports = differenceInCalendarISOWeeks;
	


/***/ },
/* 27 */
/***/ function(module, exports, __webpack_require__) {

	var getISOYear = __webpack_require__(7);
	function differenceInCalendarISOYears(dirtyDateLeft, dirtyDateRight) {
	    return getISOYear(dirtyDateLeft) - getISOYear(dirtyDateRight);
	}
	module.exports = differenceInCalendarISOYears;
	


/***/ },
/* 28 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function differenceInCalendarMonths(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    var yearDiff = dateLeft.getFullYear() - dateRight.getFullYear();
	    var monthDiff = dateLeft.getMonth() - dateRight.getMonth();
	    return yearDiff * 12 + monthDiff;
	}
	module.exports = differenceInCalendarMonths;
	


/***/ },
/* 29 */
/***/ function(module, exports, __webpack_require__) {

	var getQuarter = __webpack_require__(30);
	var parse = __webpack_require__(2);
	function differenceInCalendarQuarters(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    var yearDiff = dateLeft.getFullYear() - dateRight.getFullYear();
	    var quarterDiff = getQuarter(dateLeft) - getQuarter(dateRight);
	    return yearDiff * 4 + quarterDiff;
	}
	module.exports = differenceInCalendarQuarters;
	


/***/ },
/* 30 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getQuarter(dirtyDate) {
	    var date = parse(dirtyDate);
	    var quarter = Math.floor(date.getMonth() / 3) + 1;
	    return quarter;
	}
	module.exports = getQuarter;
	


/***/ },
/* 31 */
/***/ function(module, exports, __webpack_require__) {

	var startOfWeek = __webpack_require__(9);
	var MILLISECONDS_IN_MINUTE = 60000;
	var MILLISECONDS_IN_WEEK = 604800000;
	function differenceInCalendarWeeks(dirtyDateLeft, dirtyDateRight, dirtyOptions) {
	    var startOfWeekLeft = startOfWeek(dirtyDateLeft, dirtyOptions);
	    var startOfWeekRight = startOfWeek(dirtyDateRight, dirtyOptions);
	    var timestampLeft = startOfWeekLeft.getTime() - startOfWeekLeft.getTimezoneOffset() * MILLISECONDS_IN_MINUTE;
	    var timestampRight = startOfWeekRight.getTime() - startOfWeekRight.getTimezoneOffset() * MILLISECONDS_IN_MINUTE;
	    return Math.round((timestampLeft - timestampRight) / MILLISECONDS_IN_WEEK);
	}
	module.exports = differenceInCalendarWeeks;
	


/***/ },
/* 32 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function differenceInCalendarYears(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    return dateLeft.getFullYear() - dateRight.getFullYear();
	}
	module.exports = differenceInCalendarYears;
	


/***/ },
/* 33 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var differenceInCalendarDays = __webpack_require__(12);
	var compareAsc = __webpack_require__(24);
	function differenceInDays(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    var sign = compareAsc(dateLeft, dateRight);
	    var difference = Math.abs(differenceInCalendarDays(dateLeft, dateRight));
	    dateLeft.setDate(dateLeft.getDate() - sign * difference);
	    var isLastDayNotFull = compareAsc(dateLeft, dateRight) === -sign;
	    return sign * (difference - isLastDayNotFull);
	}
	module.exports = differenceInDays;
	


/***/ },
/* 34 */
/***/ function(module, exports, __webpack_require__) {

	var differenceInMilliseconds = __webpack_require__(35);
	var MILLISECONDS_IN_HOUR = 3600000;
	function differenceInHours(dirtyDateLeft, dirtyDateRight) {
	    var diff = differenceInMilliseconds(dirtyDateLeft, dirtyDateRight) / MILLISECONDS_IN_HOUR;
	    return diff > 0 ? Math.floor(diff) : Math.ceil(diff);
	}
	module.exports = differenceInHours;
	


/***/ },
/* 35 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function differenceInMilliseconds(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    return dateLeft.getTime() - dateRight.getTime();
	}
	module.exports = differenceInMilliseconds;
	


/***/ },
/* 36 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var differenceInCalendarISOYears = __webpack_require__(27);
	var compareAsc = __webpack_require__(24);
	var subISOYears = __webpack_require__(37);
	function differenceInISOYears(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    var sign = compareAsc(dateLeft, dateRight);
	    var difference = Math.abs(differenceInCalendarISOYears(dateLeft, dateRight));
	    dateLeft = subISOYears(dateLeft, sign * difference);
	    var isLastISOYearNotFull = compareAsc(dateLeft, dateRight) === -sign;
	    return sign * (difference - isLastISOYearNotFull);
	}
	module.exports = differenceInISOYears;
	


/***/ },
/* 37 */
/***/ function(module, exports, __webpack_require__) {

	var addISOYears = __webpack_require__(6);
	function subISOYears(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addISOYears(dirtyDate, -amount);
	}
	module.exports = subISOYears;
	


/***/ },
/* 38 */
/***/ function(module, exports, __webpack_require__) {

	var differenceInMilliseconds = __webpack_require__(35);
	var MILLISECONDS_IN_MINUTE = 60000;
	function differenceInMinutes(dirtyDateLeft, dirtyDateRight) {
	    var diff = differenceInMilliseconds(dirtyDateLeft, dirtyDateRight) / MILLISECONDS_IN_MINUTE;
	    return diff > 0 ? Math.floor(diff) : Math.ceil(diff);
	}
	module.exports = differenceInMinutes;
	


/***/ },
/* 39 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var differenceInCalendarMonths = __webpack_require__(28);
	var compareAsc = __webpack_require__(24);
	function differenceInMonths(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    var sign = compareAsc(dateLeft, dateRight);
	    var difference = Math.abs(differenceInCalendarMonths(dateLeft, dateRight));
	    dateLeft.setMonth(dateLeft.getMonth() - sign * difference);
	    var isLastMonthNotFull = compareAsc(dateLeft, dateRight) === -sign;
	    return sign * (difference - isLastMonthNotFull);
	}
	module.exports = differenceInMonths;
	


/***/ },
/* 40 */
/***/ function(module, exports, __webpack_require__) {

	var differenceInMonths = __webpack_require__(39);
	function differenceInQuarters(dirtyDateLeft, dirtyDateRight) {
	    var diff = differenceInMonths(dirtyDateLeft, dirtyDateRight) / 3;
	    return diff > 0 ? Math.floor(diff) : Math.ceil(diff);
	}
	module.exports = differenceInQuarters;
	


/***/ },
/* 41 */
/***/ function(module, exports, __webpack_require__) {

	var differenceInMilliseconds = __webpack_require__(35);
	function differenceInSeconds(dirtyDateLeft, dirtyDateRight) {
	    var diff = differenceInMilliseconds(dirtyDateLeft, dirtyDateRight) / 1000;
	    return diff > 0 ? Math.floor(diff) : Math.ceil(diff);
	}
	module.exports = differenceInSeconds;
	


/***/ },
/* 42 */
/***/ function(module, exports, __webpack_require__) {

	var differenceInDays = __webpack_require__(33);
	function differenceInWeeks(dirtyDateLeft, dirtyDateRight) {
	    var diff = differenceInDays(dirtyDateLeft, dirtyDateRight) / 7;
	    return diff > 0 ? Math.floor(diff) : Math.ceil(diff);
	}
	module.exports = differenceInWeeks;
	


/***/ },
/* 43 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var differenceInCalendarYears = __webpack_require__(32);
	var compareAsc = __webpack_require__(24);
	function differenceInYears(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    var sign = compareAsc(dateLeft, dateRight);
	    var difference = Math.abs(differenceInCalendarYears(dateLeft, dateRight));
	    dateLeft.setFullYear(dateLeft.getFullYear() - sign * difference);
	    var isLastYearNotFull = compareAsc(dateLeft, dateRight) === -sign;
	    return sign * (difference - isLastYearNotFull);
	}
	module.exports = differenceInYears;
	


/***/ },
/* 44 */
/***/ function(module, exports, __webpack_require__) {

	var compareDesc = __webpack_require__(25);
	var parse = __webpack_require__(2);
	var differenceInSeconds = __webpack_require__(41);
	var differenceInMonths = __webpack_require__(39);
	var enLocale = __webpack_require__(45);
	var MINUTES_IN_DAY = 1440;
	var MINUTES_IN_ALMOST_TWO_DAYS = 2520;
	var MINUTES_IN_MONTH = 43200;
	var MINUTES_IN_TWO_MONTHS = 86400;
	function distanceInWords(dirtyDateToCompare, dirtyDate, dirtyOptions) {
	    var options = dirtyOptions || {};
	    var comparison = compareDesc(dirtyDateToCompare, dirtyDate);
	    var locale = options.locale;
	    var localize = enLocale.distanceInWords.localize;
	    if (locale && locale.distanceInWords && locale.distanceInWords.localize) {
	        localize = locale.distanceInWords.localize;
	    }
	    var localizeOptions = {
	        addSuffix: Boolean(options.addSuffix),
	        comparison: comparison
	    };
	    var dateLeft, dateRight;
	    if (comparison > 0) {
	        dateLeft = parse(dirtyDateToCompare);
	        dateRight = parse(dirtyDate);
	    } else {
	        dateLeft = parse(dirtyDate);
	        dateRight = parse(dirtyDateToCompare);
	    }
	    var seconds = differenceInSeconds(dateRight, dateLeft);
	    var offset = dateRight.getTimezoneOffset() - dateLeft.getTimezoneOffset();
	    var minutes = Math.round(seconds / 60) - offset;
	    var months;
	    if (minutes < 2) {
	        if (options.includeSeconds) {
	            if (seconds < 5) {
	                return localize('lessThanXSeconds', 5, localizeOptions);
	            } else if (seconds < 10) {
	                return localize('lessThanXSeconds', 10, localizeOptions);
	            } else if (seconds < 20) {
	                return localize('lessThanXSeconds', 20, localizeOptions);
	            } else if (seconds < 40) {
	                return localize('halfAMinute', null, localizeOptions);
	            } else if (seconds < 60) {
	                return localize('lessThanXMinutes', 1, localizeOptions);
	            } else {
	                return localize('xMinutes', 1, localizeOptions);
	            }
	        } else {
	            if (minutes === 0) {
	                return localize('lessThanXMinutes', 1, localizeOptions);
	            } else {
	                return localize('xMinutes', minutes, localizeOptions);
	            }
	        }
	    } else if (minutes < 45) {
	        return localize('xMinutes', minutes, localizeOptions);
	    } else if (minutes < 90) {
	        return localize('aboutXHours', 1, localizeOptions);
	    } else if (minutes < MINUTES_IN_DAY) {
	        var hours = Math.round(minutes / 60);
	        return localize('aboutXHours', hours, localizeOptions);
	    } else if (minutes < MINUTES_IN_ALMOST_TWO_DAYS) {
	        return localize('xDays', 1, localizeOptions);
	    } else if (minutes < MINUTES_IN_MONTH) {
	        var days = Math.round(minutes / MINUTES_IN_DAY);
	        return localize('xDays', days, localizeOptions);
	    } else if (minutes < MINUTES_IN_TWO_MONTHS) {
	        months = Math.round(minutes / MINUTES_IN_MONTH);
	        return localize('aboutXMonths', months, localizeOptions);
	    }
	    months = differenceInMonths(dateRight, dateLeft);
	    if (months < 12) {
	        var nearestMonth = Math.round(minutes / MINUTES_IN_MONTH);
	        return localize('xMonths', nearestMonth, localizeOptions);
	    } else {
	        var monthsSinceStartOfYear = months % 12;
	        var years = Math.floor(months / 12);
	        if (monthsSinceStartOfYear < 3) {
	            return localize('aboutXYears', years, localizeOptions);
	        } else if (monthsSinceStartOfYear < 9) {
	            return localize('overXYears', years, localizeOptions);
	        } else {
	            return localize('almostXYears', years + 1, localizeOptions);
	        }
	    }
	}
	module.exports = distanceInWords;
	


/***/ },
/* 45 */
/***/ function(module, exports, __webpack_require__) {

	var buildDistanceInWordsLocale = __webpack_require__(46);
	var buildFormatLocale = __webpack_require__(47);
	module.exports = {
	    distanceInWords: buildDistanceInWordsLocale(),
	    format: buildFormatLocale()
	};
	


/***/ },
/* 46 */
/***/ function(module, exports) {

	function buildDistanceInWordsLocale() {
	    var distanceInWordsLocale = {
	        lessThanXSeconds: {
	            one: 'less than a second',
	            other: 'less than {{count}} seconds'
	        },
	        xSeconds: {
	            one: '1 second',
	            other: '{{count}} seconds'
	        },
	        halfAMinute: 'half a minute',
	        lessThanXMinutes: {
	            one: 'less than a minute',
	            other: 'less than {{count}} minutes'
	        },
	        xMinutes: {
	            one: '1 minute',
	            other: '{{count}} minutes'
	        },
	        aboutXHours: {
	            one: 'about 1 hour',
	            other: 'about {{count}} hours'
	        },
	        xHours: {
	            one: '1 hour',
	            other: '{{count}} hours'
	        },
	        xDays: {
	            one: '1 day',
	            other: '{{count}} days'
	        },
	        aboutXMonths: {
	            one: 'about 1 month',
	            other: 'about {{count}} months'
	        },
	        xMonths: {
	            one: '1 month',
	            other: '{{count}} months'
	        },
	        aboutXYears: {
	            one: 'about 1 year',
	            other: 'about {{count}} years'
	        },
	        xYears: {
	            one: '1 year',
	            other: '{{count}} years'
	        },
	        overXYears: {
	            one: 'over 1 year',
	            other: 'over {{count}} years'
	        },
	        almostXYears: {
	            one: 'almost 1 year',
	            other: 'almost {{count}} years'
	        }
	    };
	    function localize(token, count, options) {
	        options = options || {};
	        var result;
	        if (typeof distanceInWordsLocale[token] === 'string') {
	            result = distanceInWordsLocale[token];
	        } else if (count === 1) {
	            result = distanceInWordsLocale[token].one;
	        } else {
	            result = distanceInWordsLocale[token].other.replace('{{count}}', count);
	        }
	        if (options.addSuffix) {
	            if (options.comparison > 0) {
	                return 'in ' + result;
	            } else {
	                return result + ' ago';
	            }
	        }
	        return result;
	    }
	    return { localize: localize };
	}
	module.exports = buildDistanceInWordsLocale;
	


/***/ },
/* 47 */
/***/ function(module, exports, __webpack_require__) {

	var buildFormattingTokensRegExp = __webpack_require__(48);
	function buildFormatLocale() {
	    var months3char = [
	        'Jan',
	        'Feb',
	        'Mar',
	        'Apr',
	        'May',
	        'Jun',
	        'Jul',
	        'Aug',
	        'Sep',
	        'Oct',
	        'Nov',
	        'Dec'
	    ];
	    var monthsFull = [
	        'January',
	        'February',
	        'March',
	        'April',
	        'May',
	        'June',
	        'July',
	        'August',
	        'September',
	        'October',
	        'November',
	        'December'
	    ];
	    var weekdays2char = [
	        'Su',
	        'Mo',
	        'Tu',
	        'We',
	        'Th',
	        'Fr',
	        'Sa'
	    ];
	    var weekdays3char = [
	        'Sun',
	        'Mon',
	        'Tue',
	        'Wed',
	        'Thu',
	        'Fri',
	        'Sat'
	    ];
	    var weekdaysFull = [
	        'Sunday',
	        'Monday',
	        'Tuesday',
	        'Wednesday',
	        'Thursday',
	        'Friday',
	        'Saturday'
	    ];
	    var meridiemUppercase = [
	        'AM',
	        'PM'
	    ];
	    var meridiemLowercase = [
	        'am',
	        'pm'
	    ];
	    var meridiemFull = [
	        'a.m.',
	        'p.m.'
	    ];
	    var formatters = {
	        'MMM': function (date) {
	            return months3char[date.getMonth()];
	        },
	        'MMMM': function (date) {
	            return monthsFull[date.getMonth()];
	        },
	        'dd': function (date) {
	            return weekdays2char[date.getDay()];
	        },
	        'ddd': function (date) {
	            return weekdays3char[date.getDay()];
	        },
	        'dddd': function (date) {
	            return weekdaysFull[date.getDay()];
	        },
	        'A': function (date) {
	            return date.getHours() / 12 >= 1 ? meridiemUppercase[1] : meridiemUppercase[0];
	        },
	        'a': function (date) {
	            return date.getHours() / 12 >= 1 ? meridiemLowercase[1] : meridiemLowercase[0];
	        },
	        'aa': function (date) {
	            return date.getHours() / 12 >= 1 ? meridiemFull[1] : meridiemFull[0];
	        }
	    };
	    var ordinalFormatters = [
	        'M',
	        'D',
	        'DDD',
	        'd',
	        'Q',
	        'W'
	    ];
	    ordinalFormatters.forEach(function (formatterToken) {
	        formatters[formatterToken + 'o'] = function (date, formatters) {
	            return ordinal(formatters[formatterToken](date));
	        };
	    });
	    return {
	        formatters: formatters,
	        formattingTokensRegExp: buildFormattingTokensRegExp(formatters)
	    };
	}
	function ordinal(number) {
	    var rem100 = number % 100;
	    if (rem100 > 20 || rem100 < 10) {
	        switch (rem100 % 10) {
	        case 1:
	            return number + 'st';
	        case 2:
	            return number + 'nd';
	        case 3:
	            return number + 'rd';
	        }
	    }
	    return number + 'th';
	}
	module.exports = buildFormatLocale;
	


/***/ },
/* 48 */
/***/ function(module, exports) {

	var commonFormatterKeys = [
	    'M',
	    'MM',
	    'Q',
	    'D',
	    'DD',
	    'DDD',
	    'DDDD',
	    'd',
	    'E',
	    'W',
	    'WW',
	    'YY',
	    'YYYY',
	    'GG',
	    'GGGG',
	    'H',
	    'HH',
	    'h',
	    'hh',
	    'm',
	    'mm',
	    's',
	    'ss',
	    'S',
	    'SS',
	    'SSS',
	    'Z',
	    'ZZ',
	    'X',
	    'x'
	];
	function buildFormattingTokensRegExp(formatters) {
	    var formatterKeys = [];
	    for (var key in formatters) {
	        if (formatters.hasOwnProperty(key)) {
	            formatterKeys.push(key);
	        }
	    }
	    var formattingTokens = commonFormatterKeys.concat(formatterKeys).sort().reverse();
	    var formattingTokensRegExp = new RegExp('(\\[[^\\[]*\\])|(\\\\)?' + '(' + formattingTokens.join('|') + '|.)', 'g');
	    return formattingTokensRegExp;
	}
	module.exports = buildFormattingTokensRegExp;
	


/***/ },
/* 49 */
/***/ function(module, exports, __webpack_require__) {

	var compareDesc = __webpack_require__(25);
	var parse = __webpack_require__(2);
	var differenceInSeconds = __webpack_require__(41);
	var enLocale = __webpack_require__(45);
	var MINUTES_IN_DAY = 1440;
	var MINUTES_IN_MONTH = 43200;
	var MINUTES_IN_YEAR = 525600;
	function distanceInWordsStrict(dirtyDateToCompare, dirtyDate, dirtyOptions) {
	    var options = dirtyOptions || {};
	    var comparison = compareDesc(dirtyDateToCompare, dirtyDate);
	    var locale = options.locale;
	    var localize = enLocale.distanceInWords.localize;
	    if (locale && locale.distanceInWords && locale.distanceInWords.localize) {
	        localize = locale.distanceInWords.localize;
	    }
	    var localizeOptions = {
	        addSuffix: Boolean(options.addSuffix),
	        comparison: comparison
	    };
	    var dateLeft, dateRight;
	    if (comparison > 0) {
	        dateLeft = parse(dirtyDateToCompare);
	        dateRight = parse(dirtyDate);
	    } else {
	        dateLeft = parse(dirtyDate);
	        dateRight = parse(dirtyDateToCompare);
	    }
	    var unit;
	    var mathPartial = Math[options.partialMethod ? String(options.partialMethod) : 'floor'];
	    var seconds = differenceInSeconds(dateRight, dateLeft);
	    var offset = dateRight.getTimezoneOffset() - dateLeft.getTimezoneOffset();
	    var minutes = mathPartial(seconds / 60) - offset;
	    var hours, days, months, years;
	    if (options.unit) {
	        unit = String(options.unit);
	    } else {
	        if (minutes < 1) {
	            unit = 's';
	        } else if (minutes < 60) {
	            unit = 'm';
	        } else if (minutes < MINUTES_IN_DAY) {
	            unit = 'h';
	        } else if (minutes < MINUTES_IN_MONTH) {
	            unit = 'd';
	        } else if (minutes < MINUTES_IN_YEAR) {
	            unit = 'M';
	        } else {
	            unit = 'Y';
	        }
	    }
	    if (unit === 's') {
	        return localize('xSeconds', seconds, localizeOptions);
	    } else if (unit === 'm') {
	        return localize('xMinutes', minutes, localizeOptions);
	    } else if (unit === 'h') {
	        hours = mathPartial(minutes / 60);
	        return localize('xHours', hours, localizeOptions);
	    } else if (unit === 'd') {
	        days = mathPartial(minutes / MINUTES_IN_DAY);
	        return localize('xDays', days, localizeOptions);
	    } else if (unit === 'M') {
	        months = mathPartial(minutes / MINUTES_IN_MONTH);
	        return localize('xMonths', months, localizeOptions);
	    } else if (unit === 'Y') {
	        years = mathPartial(minutes / MINUTES_IN_YEAR);
	        return localize('xYears', years, localizeOptions);
	    }
	    throw new Error('Unknown unit: ' + unit);
	}
	module.exports = distanceInWordsStrict;
	


/***/ },
/* 50 */
/***/ function(module, exports, __webpack_require__) {

	var distanceInWords = __webpack_require__(44);
	function distanceInWordsToNow(dirtyDate, dirtyOptions) {
	    return distanceInWords(Date.now(), dirtyDate, dirtyOptions);
	}
	module.exports = distanceInWordsToNow;
	


/***/ },
/* 51 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function eachDay(dirtyStartDate, dirtyEndDate) {
	    var startDate = parse(dirtyStartDate);
	    var endDate = parse(dirtyEndDate);
	    var endTime = endDate.getTime();
	    if (startDate.getTime() > endTime) {
	        throw new Error('The first date cannot be after the second date');
	    }
	    var dates = [];
	    var currentDate = startDate;
	    currentDate.setHours(0, 0, 0, 0);
	    while (currentDate.getTime() <= endTime) {
	        dates.push(parse(currentDate));
	        currentDate.setDate(currentDate.getDate() + 1);
	    }
	    return dates;
	}
	module.exports = eachDay;
	


/***/ },
/* 52 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfDay(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setHours(23, 59, 59, 999);
	    return date;
	}
	module.exports = endOfDay;
	


/***/ },
/* 53 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfHour(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setMinutes(59, 59, 999);
	    return date;
	}
	module.exports = endOfHour;
	


/***/ },
/* 54 */
/***/ function(module, exports, __webpack_require__) {

	var endOfWeek = __webpack_require__(55);
	function endOfISOWeek(dirtyDate) {
	    return endOfWeek(dirtyDate, { weekStartsOn: 1 });
	}
	module.exports = endOfISOWeek;
	


/***/ },
/* 55 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfWeek(dirtyDate, dirtyOptions) {
	    var weekStartsOn = dirtyOptions ? Number(dirtyOptions.weekStartsOn) || 0 : 0;
	    var date = parse(dirtyDate);
	    var day = date.getDay();
	    var diff = (day < weekStartsOn ? -7 : 0) + 6 - (day - weekStartsOn);
	    date.setDate(date.getDate() + diff);
	    date.setHours(23, 59, 59, 999);
	    return date;
	}
	module.exports = endOfWeek;
	


/***/ },
/* 56 */
/***/ function(module, exports, __webpack_require__) {

	var getISOYear = __webpack_require__(7);
	var startOfISOWeek = __webpack_require__(8);
	function endOfISOYear(dirtyDate) {
	    var year = getISOYear(dirtyDate);
	    var fourthOfJanuaryOfNextYear = new Date(0);
	    fourthOfJanuaryOfNextYear.setFullYear(year + 1, 0, 4);
	    fourthOfJanuaryOfNextYear.setHours(0, 0, 0, 0);
	    var date = startOfISOWeek(fourthOfJanuaryOfNextYear);
	    date.setMilliseconds(date.getMilliseconds() - 1);
	    return date;
	}
	module.exports = endOfISOYear;
	


/***/ },
/* 57 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfMinute(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setSeconds(59, 999);
	    return date;
	}
	module.exports = endOfMinute;
	


/***/ },
/* 58 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfMonth(dirtyDate) {
	    var date = parse(dirtyDate);
	    var month = date.getMonth();
	    date.setFullYear(date.getFullYear(), month + 1, 0);
	    date.setHours(23, 59, 59, 999);
	    return date;
	}
	module.exports = endOfMonth;
	


/***/ },
/* 59 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfQuarter(dirtyDate) {
	    var date = parse(dirtyDate);
	    var currentMonth = date.getMonth();
	    var month = currentMonth - currentMonth % 3 + 3;
	    date.setMonth(month, 0);
	    date.setHours(23, 59, 59, 999);
	    return date;
	}
	module.exports = endOfQuarter;
	


/***/ },
/* 60 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfSecond(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setMilliseconds(999);
	    return date;
	}
	module.exports = endOfSecond;
	


/***/ },
/* 61 */
/***/ function(module, exports, __webpack_require__) {

	var endOfDay = __webpack_require__(52);
	function endOfToday() {
	    return endOfDay(new Date());
	}
	module.exports = endOfToday;
	


/***/ },
/* 62 */
/***/ function(module, exports) {

	function endOfTomorrow() {
	    var now = new Date();
	    var year = now.getFullYear();
	    var month = now.getMonth();
	    var day = now.getDate();
	    var date = new Date(0);
	    date.setFullYear(year, month, day + 1);
	    date.setHours(23, 59, 59, 999);
	    return date;
	}
	module.exports = endOfTomorrow;
	


/***/ },
/* 63 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function endOfYear(dirtyDate) {
	    var date = parse(dirtyDate);
	    var year = date.getFullYear();
	    date.setFullYear(year + 1, 0, 0);
	    date.setHours(23, 59, 59, 999);
	    return date;
	}
	module.exports = endOfYear;
	


/***/ },
/* 64 */
/***/ function(module, exports) {

	function endOfYesterday() {
	    var now = new Date();
	    var year = now.getFullYear();
	    var month = now.getMonth();
	    var day = now.getDate();
	    var date = new Date(0);
	    date.setFullYear(year, month, day - 1);
	    date.setHours(23, 59, 59, 999);
	    return date;
	}
	module.exports = endOfYesterday;
	


/***/ },
/* 65 */
/***/ function(module, exports, __webpack_require__) {

	var getDayOfYear = __webpack_require__(66);
	var getISOWeek = __webpack_require__(68);
	var getISOYear = __webpack_require__(7);
	var parse = __webpack_require__(2);
	var isValid = __webpack_require__(69);
	var enLocale = __webpack_require__(45);
	function format(dirtyDate, dirtyFormatStr, dirtyOptions) {
	    var formatStr = dirtyFormatStr ? String(dirtyFormatStr) : 'YYYY-MM-DDTHH:mm:ss.SSSZ';
	    var options = dirtyOptions || {};
	    var locale = options.locale;
	    var localeFormatters = enLocale.format.formatters;
	    var formattingTokensRegExp = enLocale.format.formattingTokensRegExp;
	    if (locale && locale.format && locale.format.formatters) {
	        localeFormatters = locale.format.formatters;
	        if (locale.format.formattingTokensRegExp) {
	            formattingTokensRegExp = locale.format.formattingTokensRegExp;
	        }
	    }
	    var date = parse(dirtyDate);
	    if (!isValid(date)) {
	        return 'Invalid Date';
	    }
	    var formatFn = buildFormatFn(formatStr, localeFormatters, formattingTokensRegExp);
	    return formatFn(date);
	}
	var formatters = {
	    'M': function (date) {
	        return date.getMonth() + 1;
	    },
	    'MM': function (date) {
	        return addLeadingZeros(date.getMonth() + 1, 2);
	    },
	    'Q': function (date) {
	        return Math.ceil((date.getMonth() + 1) / 3);
	    },
	    'D': function (date) {
	        return date.getDate();
	    },
	    'DD': function (date) {
	        return addLeadingZeros(date.getDate(), 2);
	    },
	    'DDD': function (date) {
	        return getDayOfYear(date);
	    },
	    'DDDD': function (date) {
	        return addLeadingZeros(getDayOfYear(date), 3);
	    },
	    'd': function (date) {
	        return date.getDay();
	    },
	    'E': function (date) {
	        return date.getDay() || 7;
	    },
	    'W': function (date) {
	        return getISOWeek(date);
	    },
	    'WW': function (date) {
	        return addLeadingZeros(getISOWeek(date), 2);
	    },
	    'YY': function (date) {
	        return addLeadingZeros(date.getFullYear(), 4).substr(2);
	    },
	    'YYYY': function (date) {
	        return addLeadingZeros(date.getFullYear(), 4);
	    },
	    'GG': function (date) {
	        return String(getISOYear(date)).substr(2);
	    },
	    'GGGG': function (date) {
	        return getISOYear(date);
	    },
	    'H': function (date) {
	        return date.getHours();
	    },
	    'HH': function (date) {
	        return addLeadingZeros(date.getHours(), 2);
	    },
	    'h': function (date) {
	        var hours = date.getHours();
	        if (hours === 0) {
	            return 12;
	        } else if (hours > 12) {
	            return hours % 12;
	        } else {
	            return hours;
	        }
	    },
	    'hh': function (date) {
	        return addLeadingZeros(formatters['h'](date), 2);
	    },
	    'm': function (date) {
	        return date.getMinutes();
	    },
	    'mm': function (date) {
	        return addLeadingZeros(date.getMinutes(), 2);
	    },
	    's': function (date) {
	        return date.getSeconds();
	    },
	    'ss': function (date) {
	        return addLeadingZeros(date.getSeconds(), 2);
	    },
	    'S': function (date) {
	        return Math.floor(date.getMilliseconds() / 100);
	    },
	    'SS': function (date) {
	        return addLeadingZeros(Math.floor(date.getMilliseconds() / 10), 2);
	    },
	    'SSS': function (date) {
	        return addLeadingZeros(date.getMilliseconds(), 3);
	    },
	    'Z': function (date) {
	        return formatTimezone(date.getTimezoneOffset(), ':');
	    },
	    'ZZ': function (date) {
	        return formatTimezone(date.getTimezoneOffset());
	    },
	    'X': function (date) {
	        return Math.floor(date.getTime() / 1000);
	    },
	    'x': function (date) {
	        return date.getTime();
	    }
	};
	function buildFormatFn(formatStr, localeFormatters, formattingTokensRegExp) {
	    var array = formatStr.match(formattingTokensRegExp);
	    var length = array.length;
	    var i;
	    var formatter;
	    for (i = 0; i < length; i++) {
	        formatter = localeFormatters[array[i]] || formatters[array[i]];
	        if (formatter) {
	            array[i] = formatter;
	        } else {
	            array[i] = removeFormattingTokens(array[i]);
	        }
	    }
	    return function (date) {
	        var output = '';
	        for (var i = 0; i < length; i++) {
	            if (array[i] instanceof Function) {
	                output += array[i](date, formatters);
	            } else {
	                output += array[i];
	            }
	        }
	        return output;
	    };
	}
	function removeFormattingTokens(input) {
	    if (input.match(/\[[\s\S]/)) {
	        return input.replace(/^\[|]$/g, '');
	    }
	    return input.replace(/\\/g, '');
	}
	function formatTimezone(offset, delimeter) {
	    delimeter = delimeter || '';
	    var sign = offset > 0 ? '-' : '+';
	    var absOffset = Math.abs(offset);
	    var hours = Math.floor(absOffset / 60);
	    var minutes = absOffset % 60;
	    return sign + addLeadingZeros(hours, 2) + delimeter + addLeadingZeros(minutes, 2);
	}
	function addLeadingZeros(number, targetLength) {
	    var output = Math.abs(number).toString();
	    while (output.length < targetLength) {
	        output = '0' + output;
	    }
	    return output;
	}
	module.exports = format;
	


/***/ },
/* 66 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var startOfYear = __webpack_require__(67);
	var differenceInCalendarDays = __webpack_require__(12);
	function getDayOfYear(dirtyDate) {
	    var date = parse(dirtyDate);
	    var diff = differenceInCalendarDays(date, startOfYear(date));
	    var dayOfYear = diff + 1;
	    return dayOfYear;
	}
	module.exports = getDayOfYear;
	


/***/ },
/* 67 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfYear(dirtyDate) {
	    var cleanDate = parse(dirtyDate);
	    var date = new Date(0);
	    date.setFullYear(cleanDate.getFullYear(), 0, 1);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = startOfYear;
	


/***/ },
/* 68 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var startOfISOWeek = __webpack_require__(8);
	var startOfISOYear = __webpack_require__(11);
	var MILLISECONDS_IN_WEEK = 604800000;
	function getISOWeek(dirtyDate) {
	    var date = parse(dirtyDate);
	    var diff = startOfISOWeek(date).getTime() - startOfISOYear(date).getTime();
	    return Math.round(diff / MILLISECONDS_IN_WEEK) + 1;
	}
	module.exports = getISOWeek;
	


/***/ },
/* 69 */
/***/ function(module, exports, __webpack_require__) {

	var isDate = __webpack_require__(3);
	function isValid(dirtyDate) {
	    if (isDate(dirtyDate)) {
	        return !isNaN(dirtyDate);
	    } else {
	        throw new TypeError(toString.call(dirtyDate) + ' is not an instance of Date');
	    }
	}
	module.exports = isValid;
	


/***/ },
/* 70 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getDate(dirtyDate) {
	    var date = parse(dirtyDate);
	    var dayOfMonth = date.getDate();
	    return dayOfMonth;
	}
	module.exports = getDate;
	


/***/ },
/* 71 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getDay(dirtyDate) {
	    var date = parse(dirtyDate);
	    var day = date.getDay();
	    return day;
	}
	module.exports = getDay;
	


/***/ },
/* 72 */
/***/ function(module, exports, __webpack_require__) {

	var isLeapYear = __webpack_require__(73);
	function getDaysInYear(dirtyDate) {
	    return isLeapYear(dirtyDate) ? 366 : 365;
	}
	module.exports = getDaysInYear;
	


/***/ },
/* 73 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isLeapYear(dirtyDate) {
	    var date = parse(dirtyDate);
	    var year = date.getFullYear();
	    return year % 400 === 0 || year % 4 === 0 && year % 100 !== 0;
	}
	module.exports = isLeapYear;
	


/***/ },
/* 74 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getHours(dirtyDate) {
	    var date = parse(dirtyDate);
	    var hours = date.getHours();
	    return hours;
	}
	module.exports = getHours;
	


/***/ },
/* 75 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getISODay(dirtyDate) {
	    var date = parse(dirtyDate);
	    var day = date.getDay();
	    if (day === 0) {
	        day = 7;
	    }
	    return day;
	}
	module.exports = getISODay;
	


/***/ },
/* 76 */
/***/ function(module, exports, __webpack_require__) {

	var startOfISOYear = __webpack_require__(11);
	var addWeeks = __webpack_require__(19);
	var MILLISECONDS_IN_WEEK = 604800000;
	function getISOWeeksInYear(dirtyDate) {
	    var thisYear = startOfISOYear(dirtyDate);
	    var nextYear = startOfISOYear(addWeeks(thisYear, 60));
	    var diff = nextYear.valueOf() - thisYear.valueOf();
	    return Math.round(diff / MILLISECONDS_IN_WEEK);
	}
	module.exports = getISOWeeksInYear;
	


/***/ },
/* 77 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getMilliseconds(dirtyDate) {
	    var date = parse(dirtyDate);
	    var milliseconds = date.getMilliseconds();
	    return milliseconds;
	}
	module.exports = getMilliseconds;
	


/***/ },
/* 78 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getMinutes(dirtyDate) {
	    var date = parse(dirtyDate);
	    var minutes = date.getMinutes();
	    return minutes;
	}
	module.exports = getMinutes;
	


/***/ },
/* 79 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getMonth(dirtyDate) {
	    var date = parse(dirtyDate);
	    var month = date.getMonth();
	    return month;
	}
	module.exports = getMonth;
	


/***/ },
/* 80 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var MILLISECONDS_IN_DAY = 24 * 60 * 60 * 1000;
	function getOverlappingDaysInRanges(dirtyInitialRangeStartDate, dirtyInitialRangeEndDate, dirtyComparedRangeStartDate, dirtyComparedRangeEndDate) {
	    var initialStartTime = parse(dirtyInitialRangeStartDate).getTime();
	    var initialEndTime = parse(dirtyInitialRangeEndDate).getTime();
	    var comparedStartTime = parse(dirtyComparedRangeStartDate).getTime();
	    var comparedEndTime = parse(dirtyComparedRangeEndDate).getTime();
	    if (initialStartTime > initialEndTime || comparedStartTime > comparedEndTime) {
	        throw new Error('The start of the range cannot be after the end of the range');
	    }
	    var isOverlapping = initialStartTime < comparedEndTime && comparedStartTime < initialEndTime;
	    if (!isOverlapping) {
	        return 0;
	    }
	    var overlapStartDate = comparedStartTime < initialStartTime ? initialStartTime : comparedStartTime;
	    var overlapEndDate = comparedEndTime > initialEndTime ? initialEndTime : comparedEndTime;
	    var differenceInMs = overlapEndDate - overlapStartDate;
	    return Math.ceil(differenceInMs / MILLISECONDS_IN_DAY);
	}
	module.exports = getOverlappingDaysInRanges;
	


/***/ },
/* 81 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getSeconds(dirtyDate) {
	    var date = parse(dirtyDate);
	    var seconds = date.getSeconds();
	    return seconds;
	}
	module.exports = getSeconds;
	


/***/ },
/* 82 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getTime(dirtyDate) {
	    var date = parse(dirtyDate);
	    var timestamp = date.getTime();
	    return timestamp;
	}
	module.exports = getTime;
	


/***/ },
/* 83 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function getYear(dirtyDate) {
	    var date = parse(dirtyDate);
	    var year = date.getFullYear();
	    return year;
	}
	module.exports = getYear;
	


/***/ },
/* 84 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isAfter(dirtyDate, dirtyDateToCompare) {
	    var date = parse(dirtyDate);
	    var dateToCompare = parse(dirtyDateToCompare);
	    return date.getTime() > dateToCompare.getTime();
	}
	module.exports = isAfter;
	


/***/ },
/* 85 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isBefore(dirtyDate, dirtyDateToCompare) {
	    var date = parse(dirtyDate);
	    var dateToCompare = parse(dirtyDateToCompare);
	    return date.getTime() < dateToCompare.getTime();
	}
	module.exports = isBefore;
	


/***/ },
/* 86 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isEqual(dirtyLeftDate, dirtyRightDate) {
	    var dateLeft = parse(dirtyLeftDate);
	    var dateRight = parse(dirtyRightDate);
	    return dateLeft.getTime() === dateRight.getTime();
	}
	module.exports = isEqual;
	


/***/ },
/* 87 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isFirstDayOfMonth(dirtyDate) {
	    return parse(dirtyDate).getDate() === 1;
	}
	module.exports = isFirstDayOfMonth;
	


/***/ },
/* 88 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isFriday(dirtyDate) {
	    return parse(dirtyDate).getDay() === 5;
	}
	module.exports = isFriday;
	


/***/ },
/* 89 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isFuture(dirtyDate) {
	    return parse(dirtyDate).getTime() > new Date().getTime();
	}
	module.exports = isFuture;
	


/***/ },
/* 90 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var endOfDay = __webpack_require__(52);
	var endOfMonth = __webpack_require__(58);
	function isLastDayOfMonth(dirtyDate) {
	    var date = parse(dirtyDate);
	    return endOfDay(date).getTime() === endOfMonth(date).getTime();
	}
	module.exports = isLastDayOfMonth;
	


/***/ },
/* 91 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isMonday(dirtyDate) {
	    return parse(dirtyDate).getDay() === 1;
	}
	module.exports = isMonday;
	


/***/ },
/* 92 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isPast(dirtyDate) {
	    return parse(dirtyDate).getTime() < new Date().getTime();
	}
	module.exports = isPast;
	


/***/ },
/* 93 */
/***/ function(module, exports, __webpack_require__) {

	var startOfDay = __webpack_require__(13);
	function isSameDay(dirtyDateLeft, dirtyDateRight) {
	    var dateLeftStartOfDay = startOfDay(dirtyDateLeft);
	    var dateRightStartOfDay = startOfDay(dirtyDateRight);
	    return dateLeftStartOfDay.getTime() === dateRightStartOfDay.getTime();
	}
	module.exports = isSameDay;
	


/***/ },
/* 94 */
/***/ function(module, exports, __webpack_require__) {

	var startOfHour = __webpack_require__(95);
	function isSameHour(dirtyDateLeft, dirtyDateRight) {
	    var dateLeftStartOfHour = startOfHour(dirtyDateLeft);
	    var dateRightStartOfHour = startOfHour(dirtyDateRight);
	    return dateLeftStartOfHour.getTime() === dateRightStartOfHour.getTime();
	}
	module.exports = isSameHour;
	


/***/ },
/* 95 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfHour(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setMinutes(0, 0, 0);
	    return date;
	}
	module.exports = startOfHour;
	


/***/ },
/* 96 */
/***/ function(module, exports, __webpack_require__) {

	var isSameWeek = __webpack_require__(97);
	function isSameISOWeek(dirtyDateLeft, dirtyDateRight) {
	    return isSameWeek(dirtyDateLeft, dirtyDateRight, { weekStartsOn: 1 });
	}
	module.exports = isSameISOWeek;
	


/***/ },
/* 97 */
/***/ function(module, exports, __webpack_require__) {

	var startOfWeek = __webpack_require__(9);
	function isSameWeek(dirtyDateLeft, dirtyDateRight, dirtyOptions) {
	    var dateLeftStartOfWeek = startOfWeek(dirtyDateLeft, dirtyOptions);
	    var dateRightStartOfWeek = startOfWeek(dirtyDateRight, dirtyOptions);
	    return dateLeftStartOfWeek.getTime() === dateRightStartOfWeek.getTime();
	}
	module.exports = isSameWeek;
	


/***/ },
/* 98 */
/***/ function(module, exports, __webpack_require__) {

	var startOfISOYear = __webpack_require__(11);
	function isSameISOYear(dirtyDateLeft, dirtyDateRight) {
	    var dateLeftStartOfYear = startOfISOYear(dirtyDateLeft);
	    var dateRightStartOfYear = startOfISOYear(dirtyDateRight);
	    return dateLeftStartOfYear.getTime() === dateRightStartOfYear.getTime();
	}
	module.exports = isSameISOYear;
	


/***/ },
/* 99 */
/***/ function(module, exports, __webpack_require__) {

	var startOfMinute = __webpack_require__(100);
	function isSameMinute(dirtyDateLeft, dirtyDateRight) {
	    var dateLeftStartOfMinute = startOfMinute(dirtyDateLeft);
	    var dateRightStartOfMinute = startOfMinute(dirtyDateRight);
	    return dateLeftStartOfMinute.getTime() === dateRightStartOfMinute.getTime();
	}
	module.exports = isSameMinute;
	


/***/ },
/* 100 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfMinute(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setSeconds(0, 0);
	    return date;
	}
	module.exports = startOfMinute;
	


/***/ },
/* 101 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isSameMonth(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    return dateLeft.getFullYear() === dateRight.getFullYear() && dateLeft.getMonth() === dateRight.getMonth();
	}
	module.exports = isSameMonth;
	


/***/ },
/* 102 */
/***/ function(module, exports, __webpack_require__) {

	var startOfQuarter = __webpack_require__(103);
	function isSameQuarter(dirtyDateLeft, dirtyDateRight) {
	    var dateLeftStartOfQuarter = startOfQuarter(dirtyDateLeft);
	    var dateRightStartOfQuarter = startOfQuarter(dirtyDateRight);
	    return dateLeftStartOfQuarter.getTime() === dateRightStartOfQuarter.getTime();
	}
	module.exports = isSameQuarter;
	


/***/ },
/* 103 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfQuarter(dirtyDate) {
	    var date = parse(dirtyDate);
	    var currentMonth = date.getMonth();
	    var month = currentMonth - currentMonth % 3;
	    date.setMonth(month, 1);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = startOfQuarter;
	


/***/ },
/* 104 */
/***/ function(module, exports, __webpack_require__) {

	var startOfSecond = __webpack_require__(105);
	function isSameSecond(dirtyDateLeft, dirtyDateRight) {
	    var dateLeftStartOfSecond = startOfSecond(dirtyDateLeft);
	    var dateRightStartOfSecond = startOfSecond(dirtyDateRight);
	    return dateLeftStartOfSecond.getTime() === dateRightStartOfSecond.getTime();
	}
	module.exports = isSameSecond;
	


/***/ },
/* 105 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfSecond(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setMilliseconds(0);
	    return date;
	}
	module.exports = startOfSecond;
	


/***/ },
/* 106 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isSameYear(dirtyDateLeft, dirtyDateRight) {
	    var dateLeft = parse(dirtyDateLeft);
	    var dateRight = parse(dirtyDateRight);
	    return dateLeft.getFullYear() === dateRight.getFullYear();
	}
	module.exports = isSameYear;
	


/***/ },
/* 107 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isSaturday(dirtyDate) {
	    return parse(dirtyDate).getDay() === 6;
	}
	module.exports = isSaturday;
	


/***/ },
/* 108 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isSunday(dirtyDate) {
	    return parse(dirtyDate).getDay() === 0;
	}
	module.exports = isSunday;
	


/***/ },
/* 109 */
/***/ function(module, exports, __webpack_require__) {

	var isSameHour = __webpack_require__(94);
	function isThisHour(dirtyDate) {
	    return isSameHour(new Date(), dirtyDate);
	}
	module.exports = isThisHour;
	


/***/ },
/* 110 */
/***/ function(module, exports, __webpack_require__) {

	var isSameISOWeek = __webpack_require__(96);
	function isThisISOWeek(dirtyDate) {
	    return isSameISOWeek(new Date(), dirtyDate);
	}
	module.exports = isThisISOWeek;
	


/***/ },
/* 111 */
/***/ function(module, exports, __webpack_require__) {

	var isSameISOYear = __webpack_require__(98);
	function isThisISOYear(dirtyDate) {
	    return isSameISOYear(new Date(), dirtyDate);
	}
	module.exports = isThisISOYear;
	


/***/ },
/* 112 */
/***/ function(module, exports, __webpack_require__) {

	var isSameMinute = __webpack_require__(99);
	function isThisMinute(dirtyDate) {
	    return isSameMinute(new Date(), dirtyDate);
	}
	module.exports = isThisMinute;
	


/***/ },
/* 113 */
/***/ function(module, exports, __webpack_require__) {

	var isSameMonth = __webpack_require__(101);
	function isThisMonth(dirtyDate) {
	    return isSameMonth(new Date(), dirtyDate);
	}
	module.exports = isThisMonth;
	


/***/ },
/* 114 */
/***/ function(module, exports, __webpack_require__) {

	var isSameQuarter = __webpack_require__(102);
	function isThisQuarter(dirtyDate) {
	    return isSameQuarter(new Date(), dirtyDate);
	}
	module.exports = isThisQuarter;
	


/***/ },
/* 115 */
/***/ function(module, exports, __webpack_require__) {

	var isSameSecond = __webpack_require__(104);
	function isThisSecond(dirtyDate) {
	    return isSameSecond(new Date(), dirtyDate);
	}
	module.exports = isThisSecond;
	


/***/ },
/* 116 */
/***/ function(module, exports, __webpack_require__) {

	var isSameWeek = __webpack_require__(97);
	function isThisWeek(dirtyDate, dirtyOptions) {
	    return isSameWeek(new Date(), dirtyDate, dirtyOptions);
	}
	module.exports = isThisWeek;
	


/***/ },
/* 117 */
/***/ function(module, exports, __webpack_require__) {

	var isSameYear = __webpack_require__(106);
	function isThisYear(dirtyDate) {
	    return isSameYear(new Date(), dirtyDate);
	}
	module.exports = isThisYear;
	


/***/ },
/* 118 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isThursday(dirtyDate) {
	    return parse(dirtyDate).getDay() === 4;
	}
	module.exports = isThursday;
	


/***/ },
/* 119 */
/***/ function(module, exports, __webpack_require__) {

	var startOfDay = __webpack_require__(13);
	function isToday(dirtyDate) {
	    return startOfDay(dirtyDate).getTime() === startOfDay(new Date()).getTime();
	}
	module.exports = isToday;
	


/***/ },
/* 120 */
/***/ function(module, exports, __webpack_require__) {

	var startOfDay = __webpack_require__(13);
	function isTomorrow(dirtyDate) {
	    var tomorrow = new Date();
	    tomorrow.setDate(tomorrow.getDate() + 1);
	    return startOfDay(dirtyDate).getTime() === startOfDay(tomorrow).getTime();
	}
	module.exports = isTomorrow;
	


/***/ },
/* 121 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isTuesday(dirtyDate) {
	    return parse(dirtyDate).getDay() === 2;
	}
	module.exports = isTuesday;
	


/***/ },
/* 122 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isWednesday(dirtyDate) {
	    return parse(dirtyDate).getDay() === 3;
	}
	module.exports = isWednesday;
	


/***/ },
/* 123 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isWeekend(dirtyDate) {
	    var date = parse(dirtyDate);
	    var day = date.getDay();
	    return day === 0 || day === 6;
	}
	module.exports = isWeekend;
	


/***/ },
/* 124 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function isWithinRange(dirtyDate, dirtyStartDate, dirtyEndDate) {
	    var time = parse(dirtyDate).getTime();
	    var startTime = parse(dirtyStartDate).getTime();
	    var endTime = parse(dirtyEndDate).getTime();
	    if (startTime > endTime) {
	        throw new Error('The start of the range cannot be after the end of the range');
	    }
	    return time >= startTime && time <= endTime;
	}
	module.exports = isWithinRange;
	


/***/ },
/* 125 */
/***/ function(module, exports, __webpack_require__) {

	var startOfDay = __webpack_require__(13);
	function isYesterday(dirtyDate) {
	    var yesterday = new Date();
	    yesterday.setDate(yesterday.getDate() - 1);
	    return startOfDay(dirtyDate).getTime() === startOfDay(yesterday).getTime();
	}
	module.exports = isYesterday;
	


/***/ },
/* 126 */
/***/ function(module, exports, __webpack_require__) {

	var lastDayOfWeek = __webpack_require__(127);
	function lastDayOfISOWeek(dirtyDate) {
	    return lastDayOfWeek(dirtyDate, { weekStartsOn: 1 });
	}
	module.exports = lastDayOfISOWeek;
	


/***/ },
/* 127 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function lastDayOfWeek(dirtyDate, dirtyOptions) {
	    var weekStartsOn = dirtyOptions ? Number(dirtyOptions.weekStartsOn) || 0 : 0;
	    var date = parse(dirtyDate);
	    var day = date.getDay();
	    var diff = (day < weekStartsOn ? -7 : 0) + 6 - (day - weekStartsOn);
	    date.setHours(0, 0, 0, 0);
	    date.setDate(date.getDate() + diff);
	    return date;
	}
	module.exports = lastDayOfWeek;
	


/***/ },
/* 128 */
/***/ function(module, exports, __webpack_require__) {

	var getISOYear = __webpack_require__(7);
	var startOfISOWeek = __webpack_require__(8);
	function lastDayOfISOYear(dirtyDate) {
	    var year = getISOYear(dirtyDate);
	    var fourthOfJanuary = new Date(0);
	    fourthOfJanuary.setFullYear(year + 1, 0, 4);
	    fourthOfJanuary.setHours(0, 0, 0, 0);
	    var date = startOfISOWeek(fourthOfJanuary);
	    date.setDate(date.getDate() - 1);
	    return date;
	}
	module.exports = lastDayOfISOYear;
	


/***/ },
/* 129 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function lastDayOfMonth(dirtyDate) {
	    var date = parse(dirtyDate);
	    var month = date.getMonth();
	    date.setFullYear(date.getFullYear(), month + 1, 0);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = lastDayOfMonth;
	


/***/ },
/* 130 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function lastDayOfQuarter(dirtyDate) {
	    var date = parse(dirtyDate);
	    var currentMonth = date.getMonth();
	    var month = currentMonth - currentMonth % 3 + 3;
	    date.setMonth(month, 0);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = lastDayOfQuarter;
	


/***/ },
/* 131 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function lastDayOfYear(dirtyDate) {
	    var date = parse(dirtyDate);
	    var year = date.getFullYear();
	    date.setFullYear(year + 1, 0, 0);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = lastDayOfYear;
	


/***/ },
/* 132 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function max() {
	    var dirtyDates = Array.prototype.slice.call(arguments);
	    var dates = dirtyDates.map(function (dirtyDate) {
	        return parse(dirtyDate);
	    });
	    var latestTimestamp = Math.max.apply(null, dates);
	    return new Date(latestTimestamp);
	}
	module.exports = max;
	


/***/ },
/* 133 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function min() {
	    var dirtyDates = Array.prototype.slice.call(arguments);
	    var dates = dirtyDates.map(function (dirtyDate) {
	        return parse(dirtyDate);
	    });
	    var earliestTimestamp = Math.min.apply(null, dates);
	    return new Date(earliestTimestamp);
	}
	module.exports = min;
	


/***/ },
/* 134 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function setDate(dirtyDate, dirtyDayOfMonth) {
	    var date = parse(dirtyDate);
	    var dayOfMonth = Number(dirtyDayOfMonth);
	    date.setDate(dayOfMonth);
	    return date;
	}
	module.exports = setDate;
	


/***/ },
/* 135 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var addDays = __webpack_require__(1);
	function setDay(dirtyDate, dirtyDay, dirtyOptions) {
	    var weekStartsOn = dirtyOptions ? Number(dirtyOptions.weekStartsOn) || 0 : 0;
	    var date = parse(dirtyDate);
	    var day = Number(dirtyDay);
	    var currentDay = date.getDay();
	    var remainder = day % 7;
	    var dayIndex = (remainder + 7) % 7;
	    var diff = (dayIndex < weekStartsOn ? 7 : 0) + day - currentDay;
	    return addDays(date, diff);
	}
	module.exports = setDay;
	


/***/ },
/* 136 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function setDayOfYear(dirtyDate, dirtyDayOfYear) {
	    var date = parse(dirtyDate);
	    var dayOfYear = Number(dirtyDayOfYear);
	    date.setMonth(0);
	    date.setDate(dayOfYear);
	    return date;
	}
	module.exports = setDayOfYear;
	


/***/ },
/* 137 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function setHours(dirtyDate, dirtyHours) {
	    var date = parse(dirtyDate);
	    var hours = Number(dirtyHours);
	    date.setHours(hours);
	    return date;
	}
	module.exports = setHours;
	


/***/ },
/* 138 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var addDays = __webpack_require__(1);
	var getISODay = __webpack_require__(75);
	function setISODay(dirtyDate, dirtyDay) {
	    var date = parse(dirtyDate);
	    var day = Number(dirtyDay);
	    var currentDay = getISODay(date);
	    var diff = day - currentDay;
	    return addDays(date, diff);
	}
	module.exports = setISODay;
	


/***/ },
/* 139 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var getISOWeek = __webpack_require__(68);
	function setISOWeek(dirtyDate, dirtyISOWeek) {
	    var date = parse(dirtyDate);
	    var isoWeek = Number(dirtyISOWeek);
	    var diff = getISOWeek(date) - isoWeek;
	    date.setDate(date.getDate() - diff * 7);
	    return date;
	}
	module.exports = setISOWeek;
	


/***/ },
/* 140 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function setMilliseconds(dirtyDate, dirtyMilliseconds) {
	    var date = parse(dirtyDate);
	    var milliseconds = Number(dirtyMilliseconds);
	    date.setMilliseconds(milliseconds);
	    return date;
	}
	module.exports = setMilliseconds;
	


/***/ },
/* 141 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function setMinutes(dirtyDate, dirtyMinutes) {
	    var date = parse(dirtyDate);
	    var minutes = Number(dirtyMinutes);
	    date.setMinutes(minutes);
	    return date;
	}
	module.exports = setMinutes;
	


/***/ },
/* 142 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var getDaysInMonth = __webpack_require__(16);
	function setMonth(dirtyDate, dirtyMonth) {
	    var date = parse(dirtyDate);
	    var month = Number(dirtyMonth);
	    var year = date.getFullYear();
	    var day = date.getDate();
	    var dateWithDesiredMonth = new Date(0);
	    dateWithDesiredMonth.setFullYear(year, month, 15);
	    dateWithDesiredMonth.setHours(0, 0, 0, 0);
	    var daysInMonth = getDaysInMonth(dateWithDesiredMonth);
	    date.setMonth(month, Math.min(day, daysInMonth));
	    return date;
	}
	module.exports = setMonth;
	


/***/ },
/* 143 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	var setMonth = __webpack_require__(142);
	function setQuarter(dirtyDate, dirtyQuarter) {
	    var date = parse(dirtyDate);
	    var quarter = Number(dirtyQuarter);
	    var oldQuarter = Math.floor(date.getMonth() / 3) + 1;
	    var diff = quarter - oldQuarter;
	    return setMonth(date, date.getMonth() + diff * 3);
	}
	module.exports = setQuarter;
	


/***/ },
/* 144 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function setSeconds(dirtyDate, dirtySeconds) {
	    var date = parse(dirtyDate);
	    var seconds = Number(dirtySeconds);
	    date.setSeconds(seconds);
	    return date;
	}
	module.exports = setSeconds;
	


/***/ },
/* 145 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function setYear(dirtyDate, dirtyYear) {
	    var date = parse(dirtyDate);
	    var year = Number(dirtyYear);
	    date.setFullYear(year);
	    return date;
	}
	module.exports = setYear;
	


/***/ },
/* 146 */
/***/ function(module, exports, __webpack_require__) {

	var parse = __webpack_require__(2);
	function startOfMonth(dirtyDate) {
	    var date = parse(dirtyDate);
	    date.setDate(1);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = startOfMonth;
	


/***/ },
/* 147 */
/***/ function(module, exports, __webpack_require__) {

	var startOfDay = __webpack_require__(13);
	function startOfToday() {
	    return startOfDay(new Date());
	}
	module.exports = startOfToday;
	


/***/ },
/* 148 */
/***/ function(module, exports) {

	function startOfTomorrow() {
	    var now = new Date();
	    var year = now.getFullYear();
	    var month = now.getMonth();
	    var day = now.getDate();
	    var date = new Date(0);
	    date.setFullYear(year, month, day + 1);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = startOfTomorrow;
	


/***/ },
/* 149 */
/***/ function(module, exports) {

	function startOfYesterday() {
	    var now = new Date();
	    var year = now.getFullYear();
	    var month = now.getMonth();
	    var day = now.getDate();
	    var date = new Date(0);
	    date.setFullYear(year, month, day - 1);
	    date.setHours(0, 0, 0, 0);
	    return date;
	}
	module.exports = startOfYesterday;
	


/***/ },
/* 150 */
/***/ function(module, exports, __webpack_require__) {

	var addDays = __webpack_require__(1);
	function subDays(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addDays(dirtyDate, -amount);
	}
	module.exports = subDays;
	


/***/ },
/* 151 */
/***/ function(module, exports, __webpack_require__) {

	var addHours = __webpack_require__(4);
	function subHours(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addHours(dirtyDate, -amount);
	}
	module.exports = subHours;
	


/***/ },
/* 152 */
/***/ function(module, exports, __webpack_require__) {

	var addMilliseconds = __webpack_require__(5);
	function subMilliseconds(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addMilliseconds(dirtyDate, -amount);
	}
	module.exports = subMilliseconds;
	


/***/ },
/* 153 */
/***/ function(module, exports, __webpack_require__) {

	var addMinutes = __webpack_require__(14);
	function subMinutes(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addMinutes(dirtyDate, -amount);
	}
	module.exports = subMinutes;
	


/***/ },
/* 154 */
/***/ function(module, exports, __webpack_require__) {

	var addMonths = __webpack_require__(15);
	function subMonths(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addMonths(dirtyDate, -amount);
	}
	module.exports = subMonths;
	


/***/ },
/* 155 */
/***/ function(module, exports, __webpack_require__) {

	var addQuarters = __webpack_require__(17);
	function subQuarters(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addQuarters(dirtyDate, -amount);
	}
	module.exports = subQuarters;
	


/***/ },
/* 156 */
/***/ function(module, exports, __webpack_require__) {

	var addSeconds = __webpack_require__(18);
	function subSeconds(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addSeconds(dirtyDate, -amount);
	}
	module.exports = subSeconds;
	


/***/ },
/* 157 */
/***/ function(module, exports, __webpack_require__) {

	var addWeeks = __webpack_require__(19);
	function subWeeks(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addWeeks(dirtyDate, -amount);
	}
	module.exports = subWeeks;
	


/***/ },
/* 158 */
/***/ function(module, exports, __webpack_require__) {

	var addYears = __webpack_require__(20);
	function subYears(dirtyDate, dirtyAmount) {
	    var amount = Number(dirtyAmount);
	    return addYears(dirtyDate, -amount);
	}
	module.exports = subYears;
	


/***/ }
/******/ ])
});
;
//# sourceMappingURL=date_fns.js.map