jQuery(document).on('click','.api-button',function(){
    jQuery(".message-div").html('<img src="/plugin-assessment/wp-content/plugins/Unit_Plugin/assets/images/spinner.gif" alt="Wait" />');

    jQuery.ajax({
        url: "https://api.sightmap.com/v1/assets/1273/multifamily/units?per-page=250",
        method: 'GET',
        data: {
            'api-key': '7d64ca3869544c469c3e7a586921ba37',
        },
        success: function(unitsData){
            // console.log(result);
            createUnitPosts(unitsData.data);
        }
    });
})

function createUnitPosts(unitsData){
    var data = {
		'action': 'my_action',
        'unitData': unitsData
	};

	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	jQuery.post(ajax_object.ajax_url, data, function(response) {
        jQuery('.message-div').html("API Data Added Successfully.");
	});
}