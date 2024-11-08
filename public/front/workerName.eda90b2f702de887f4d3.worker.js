/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
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
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/front/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/utils/count.worker.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/utils/count.worker.js":
/*!***********************************!*\
  !*** ./src/utils/count.worker.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("// 创建存放数据的obj\r\nconst countObj = {\r\n  timer: null,\r\n  examTime: -1,\r\n  stopTimeStatus: false,\r\n  setTimer(data) {\r\n    this.timer = data;\r\n  },\r\n  setExamTime(data) {\r\n    this.examTime = data;\r\n  },\r\n  setStopTimeStatus(data) {\r\n    this.stopTimeStatus = data;\r\n  },\r\n  getExamTime() {\r\n    return this.examTime\r\n  },\r\n  getStopTimeStatus() {\r\n    return this.stopTimeStatus\r\n  }\r\n};\r\n\r\nfunction countDown() {\r\n  if (countObj.timer) {\r\n    return;\r\n  }\r\n  // 倒计时结束后传递出timeEnd的消息\r\n  if (countObj.examTime === 0) {\r\n    postMessage(['timeEnd', 0]);\r\n    return;\r\n  }\r\n  countObj.timer = setTimeout(() => {\r\n    countObj.timer = null;\r\n    if (!countObj.getStopTimeStatus()) {\r\n      countObj.setExamTime(countObj.getExamTime() - 1);\r\n    }\r\n    // 传递给vue文件倒计时后的时间\r\n    postMessage(['countDown', countObj.getExamTime()]);\r\n    countDown();\r\n  }, 1000);\r\n}\r\n\r\n\r\nonmessage = function (e) {\r\n  const {\r\n    data: { type, data },\r\n  } = e;\r\n  switch (type) {\r\n    case 'start':\r\n      countObj.setExamTime(data);\r\n      countDown();\r\n      break;\r\n    case 'align':\r\n      countObj.setExamTime(data);\r\n      postMessage(['alignTime', data]);\r\n      break;\r\n    case 'pause':\r\n      countObj.setStopTimeStatus(data);\r\n      break;\r\n    case 'recover':\r\n      countObj.setStopTimeStatus(false);\r\n      break;\r\n    case 'init':\r\n      countObj.setTimer(null);\r\n      countObj.setExamTime(-1);\r\n      countObj.setStopTimeStatus(false);\r\n      break;\r\n    case 'clear':\r\n      clearTimeout(countObj.timer);\r\n      countObj.setTimer(null);\r\n      break;\r\n    default:\r\n      break;\r\n  }\r\n};\n\n//# sourceURL=webpack:///./src/utils/count.worker.js?");

/***/ })

/******/ });