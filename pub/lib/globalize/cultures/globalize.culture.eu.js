/**
 * Globalize Culture eu
 *
 * http://github.com/jquery/globalize
 *
 * Copyright Software Freedom Conservancy, Inc.
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * This file was generated by the Globalize Culture Generator
 * Translation: bugs found in this file need to be fixed in the generator
 */

(function( window, undefined ) {

var Globalize;

if ( typeof require !== "undefined" &&
	typeof exports !== "undefined" &&
	typeof module !== "undefined" ) {
	// Assume CommonJS
	Globalize = require( "globalize" );
} else {
	// Global variable
	Globalize = window.Globalize;
}

Globalize.addCultureInfo( "eu", "default", {
	name: "eu",
	englishName: "Basque",
	nativeName: "euskara",
	language: "eu",
	numberFormat: {
		",": ".",
		".": ",",
		"NaN": "EdZ",
		negativeInfinity: "-Infinitu",
		positiveInfinity: "Infinitu",
		percent: {
			",": ".",
			".": ","
		},
		currency: {
			pattern: ["-n $","n $"],
			",": ".",
			".": ",",
			symbol: "€"
		}
	},
	calendars: {
		standard: {
			firstDay: 1,
			days: {
				names: ["igandea","astelehena","asteartea","asteazkena","osteguna","ostirala","larunbata"],
				namesAbbr: ["ig.","al.","as.","az.","og.","or.","lr."],
				namesShort: ["ig","al","as","az","og","or","lr"]
			},
			months: {
				names: ["urtarrila","otsaila","martxoa","apirila","maiatza","ekaina","uztaila","abuztua","iraila","urria","azaroa","abendua",""],
				namesAbbr: ["urt.","ots.","mar.","api.","mai.","eka.","uzt.","abu.","ira.","urr.","aza.","abe.",""]
			},
			AM: null,
			PM: null,
			eras: [{"name":"d.C.","start":null,"offset":0}],
			patterns: {
				d: "yyyy/MM/dd",
				D: "dddd, yyyy.'eko' MMMM'k 'd",
				t: "HH:mm",
				T: "H:mm:ss",
				f: "dddd, yyyy.'eko' MMMM'k 'd HH:mm",
				F: "dddd, yyyy.'eko' MMMM'k 'd H:mm:ss",
				Y: "yyyy.'eko' MMMM"
			}
		}
	}
});

}( this ));
