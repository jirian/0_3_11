/**
 * Theme: Uplon Admin Template
 * Author: Coderthemes
 * Tree view
 */

$( document ).ready(function() {
	$('#btnAddCabinet').click(function(){
		$('#objectTreeModal').modal('show');
	});
	
	$('#buttonObjectTreeModalAdd').click(function(){
		var node = $('#objTree').jstree('get_selected', false);
		var nodeID = node[0];
		
		//Collect object data
		var data = {
			id: nodeID,
			face: 0,
			view: 'port',
			page: 'diagram'
		};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/create_build_space.php", {data:data}, function(responseJSON){
			var response = JSON.parse(responseJSON);
			if (response.active == 'inactive'){
				window.location.replace("/");
			} else if ($(response.error).size() > 0){
				displayError(response.error);
			} else {
				$.each(response.data.ancestorArray, function(){
					var parentID = this.parentID;
					while(parentID != '#') {
						if(!$('#locationContainer'+this.id).length) {
							var container = 
							$('#diagramWorkspace').append();
						}
					}
					
				});
				$('#diagramWorkspace').html(response.data.html);
				$('#objectTreeModal').modal('hide');
			}
		});
	});
	
		// Ajax Tree
	$('#objTree')
	.jstree({
		'core' : {
			'multiple': false,
			'themes': {
				'responsive': false
			},
			'data' : {
				'url' : function (node) {
					return 'backend/process_environment-tree.php';
				}
			}
		},
		'state' : {
			'key' : 'diagramNavigation'
		},
		"types" : {
			'default' : {
				'icon' : 'fa fa-building'
			},
			'location' : {
				'icon' : 'fa fa-building'
			},
			'pod' : {
				'icon' : 'zmdi zmdi-group-work'
			},
			'cabinet' : {
				'icon' : 'fa fa-server'
			},
			'object' : {
				'icon' : 'fa fa-minus'
			},
			'port' : {
				'icon' : 'fa fa-circle'
			},
			'floorplan' : {
				'icon' : 'fa fa-map-o'
			}
        },
		"plugins" : [ "search", "state", "types", "wholerow" ]
    });
});