$(document).ready(function() {
	// set vars
	var start = 'https://news.ycombinator.com';      // Default start URL
	var api = 'http://localhost/v1/spider_api.php?'; // The API URL
	var output = $('#output');                       // The id for links output
	var maxDepth = 5;                                // The default max page depth
	var maxLinks = 5;                                // The default max links to return per page
	
	// recursive function
	var getLinks = function( location, depth, parent, trail ) {
		// Add please wait... message
		$('#ajax').text('Please wait...');
		var jqxhr = $.getJSON( api, { url : location } )
		.done(function( data ) {
			// Add log entry
			var log = $('<p>').addClass('row-fluid').appendTo('#logs ');
			$('<span>').addClass('span2').text( 'Links: ' + data.links.length ).appendTo( log );
			$('<span>').addClass('span7').text( location ).appendTo( log );
			$('<span>').addClass('span3').text( 'Type: ' + data.cinfo.content_type ).appendTo( log );
			// Add any errors
			if ( data.error.length > 0 ) {
				$.each(data.error, function(i,item) {
					$('<p>').addClass('text-error').text(item).appendTo('#errors');
				});
			}
			// Add Links
			if ( data.links.length > 0 ) {
				var list = $('<ul>').appendTo( parent );
				for ( var i=0, l=data.links.length; i<l; i++ ) {
					var link = data.links[i];
					var item = $('<li>').text( depth + ') ' + trail + link ).appendTo(list);
					if ( depth < maxDepth && link != start ) {
						getLinks( link, depth + 1, item, trail + link + ' -> ' );
					}
					if (i >= maxLinks && maxLinks > 0) break;
				}
			}
		})
		.fail(function( data ) {
			$('<p>').addClass('text-error').text('AJAX Error: on URL: ' + location ).appendTo('#errors');
		})
		.always(function() {
			$('#ajax').text('');
		});
	};
	
	// Add crawller controls
	// Max Depth Dropdown
	$('<label>').attr('for', 'max-depth').addClass('span3').text('Page Depth: ').appendTo('#crawller');
	var select = $('<select>').attr('id', 'max-depth').addClass('span2').appendTo('#crawller').on('change', function() {
		maxDepth = $(this).prop('value');
	});
	for (var i=0; i<10; i++) {
		var option = (i==0)?'Single Page':i+' Page Deep';
		$('<option>').attr('value', i).text(option).appendTo(select);
	}
	$('#max-depth :nth('+maxDepth+')').prop('selected',true);
	// Max Links Dropdown
	$('<label>').attr('for', 'max-links').addClass('span3').text('Max Links: (Per Page) ').appendTo('#crawller');
	var select = $('<select>').attr('id', 'max-links').addClass('span2').appendTo('#crawller').on('change', function() {
		maxLinks = $(this).prop('value');
	});
	var options = [5,10,20,25,50,75,100,150,200,250,0];
	for (i in options) {
		var option = (options[i]==0)?'Unlimited':options[i]+' Links';
		$('<option>').attr('value', options[i]).text(option).appendTo(select);
	}
	$('#max-links option[value="'+maxLinks+'"]').prop('selected',true);
	// Go Button
	var button = $('<span>').addClass('span2').appendTo('#crawller');
	$('<button>').text('Go').appendTo( button ).on('click', function(e) {
		e.preventDefault();
		// Clear last results before getting new results
		$('#output').html('<h3>Links</h3>');
		$('#errors').html('<h3>Errors</h3>');
		$('#logs').html('<h3>Logs</h3>');
		getLinks( start, 0, output, '' );
	});
	// AJAX message
	$('<span>').attr('id', 'ajax').addClass('text-warning').appendTo( button );
	// Perform initial spider crawl
	getLinks( start, 0, output, '' );
});
Enter file contents here
