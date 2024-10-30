/**
 * Max Grid Builder - Builder functions
 */

// Generate random string/Letters
function maxgrid_uniqid() {
	var text = "";
	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	for (var i = 0; i < 8; i++) {
		text += possible.charAt(Math.floor(Math.random() * possible.length));
	}
	return text;
}

// Find matching words
function maxgrid_findMatchingWords(t, s) {
	var re = new RegExp("\\w*" + s + "\\w*", "g");
	return t.match(re);
}

// Sptit option name
function maxgrid_splitAndGetLast(str) {
	if (!str) return;
	var arr = str.split(/[\[\]]+/gi).filter(function (v) {
		return v !== ''
	});
	return arr[arr.length - 1];
}

// Check if an option exist in select element
function maxgrid_is_option_exist(el_id, option) {
	var exist = false,
		select = document.getElementById(el_id);
	for (i = 0; i < select.options.length; ++i) {
		if (select.options[i].value === option){
			exist = true;
			return true;
		}
	}
	return exist;
}