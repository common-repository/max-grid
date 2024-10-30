// JavaScript Document
// Simply Button FontSelect SearchBar Filter
function fontSelectFilter() {
    // Declare variables	
    var input, filter, ul, li, a, i, dd;
    input = document.getElementById('fontselect_search');
    filter = input.value.toUpperCase();
	
    ul = document.getElementById("fontselect_ul");
    li = ul.getElementsByTagName('li');
	
    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
		if (li[i].innerHTML.toUpperCase().indexOf(filter) > -1 ) {
			li[i].style.display = "";
		} else {
			li[i].style.display = "none";
		}
		if ( li[i].className === 'optgroup' ) {
			li[i].style.display = "";
		}
		
    }
}
jQuery(document).ready(function($){	
	$('#fontSelect-maxgrid_font_selector').on('click', function(event){
		document.getElementById("fontselect_search").focus(); 
	});
	
});