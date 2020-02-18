<!--
/////////////////////////////
//Object Details
/////////////////////////////
-->

<div id="detailsContainer">
	<table>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Object Name:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailObjName" class="objDetail"><a href="#" id="inline-objName" data-type="text">-</a></span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Template Name:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailTemplateName" class="objDetail"><a href="#" id="inline-templateName" data-type="text">-</a></span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Category:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailCategory" class="objDetail"><a href="#" id="inline-category" data-type="select">-</a></span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight" valign="top">
				<strong>Trunked To:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailTrunkedTo" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Type:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailObjType" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Function:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailObjFunction" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>RU Size:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailRUSize" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Mount Config:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailMountConfig" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Port Range:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailPortRange" class="objDetail no-modal" data-port-name-action="edit" data-toggle="modal" data-target="#portNameModal">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Port Orientation:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailPortOrientation" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Port Type:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailPortType" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Media Type:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailMediaType" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight">
				<strong>Enclosure Tolerance:&nbsp&nbsp</strong>
			</td>
			<td>
				<span id="detailEnclosureTolerance" class="objDetail">-</span>
			</td>
		</tr>
		<tr>
			<td class="objectDetailAlignRight" valign="top">
				<strong>Image:&nbsp&nbsp</strong>
			</td>
			<td width="100%">
				<span id="detailTemplateImage" class="objDetail">-</span>
			</td>
		</tr>
	</table>
	
	<div class="btn-group">
	<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
	<div class="dropdown-menu">
		<a id="objFind" class="dropdown-item disabled" href="#" data-toggle="modal" data-target="#modalTemplateWhereUsed"><i class="ion-map"></i> Where Used</a>
		<a id="objClone" class="dropdown-item disabled" href="#" ><i class="fa fa-copy"></i></span> Clone</a>
		<a id="objDelete" class="dropdown-item disabled" href="#" data-toggle="modal" data-target="#modalTemplateDeleteConfirm"><i class="fa fa-times"></i></span> Delete</a>
	</div>
	</div>
	
	<!--<div>
		<button id="objFind" type="button" class="btn btn-sm btn-success waves-effect waves-light m-t-10 disabled" disabled data-toggle="modal" data-target="#modalTemplateWhereUsed">
			<span class="btn-label"><i class="fa fa-map-marker"></i></span>Where Used
		</button>
	</div>
	
	<div>
		<button id="objClone" type="button" class="btn btn-sm btn-success waves-effect waves-light m-t-10 disabled" disabled>
			<span class="btn-label"><i class="fa fa-copy"></i></span>Clone
		</button>
	</div>
	
	<div>
		<button id="objDelete" type="button" class="btn btn-sm btn-danger waves-effect waves-light m-t-10 disabled" disabled data-toggle="modal" data-target="#modalTemplateDeleteConfirm" >
			<span class="btn-label"><i class="fa fa-times"></i></span>Delete
		</button>
	</div>
	-->
</div>