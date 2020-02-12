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
				$.each(response.data.ancestorIDArray, function(index, ancestor){
					var locationID = ancestor.id;
					var locationName = ancestor.name;
					var parentID = ancestor.parentID;
					if(parentID == '#') {
						parentDOM = $('#diagramWorkspace');
					} else {
						parentDOM = $('#locationBox'+parentID);
					}
					if(!$('#locationBox'+locationID).length) {
						var locationBoxHTML = '<div id="locationBox'+locationID+'" class="diagramLocationBox"><div class="diagramLocationBoxTitle">'+locationName+'</div><div class="diagramLocationSubBox"></div></div>';
						$(parentDOM).append(locationBoxHTML);
					}
				});
				var cabinetLocationID = response.data.locationID;
				$('#locationBox'+cabinetLocationID).children('.diagramLocationSubBox').first().append('<div class="diagramCabinetContainer">'+response.data.html+'</div>');
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