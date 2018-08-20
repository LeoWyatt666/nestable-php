<?php
error_reporting(E_ALL ^ E_NOTICE);

/** CONFIG ***************************************************************************************************/
	$mysql['host'] = 'localhost';
	$mysql['username'] = 'test';
	$mysql['password'] = 'test';
	$mysql['dbname'] = 'test';

?>

<?php
/** INIT ***************************************************************************************************/
// Connect db
$db = new PDO("mysql:host={$mysql['host']};dbname={$mysql['dbname']}", $mysql['username'],$mysql['password']);

// load update_menu controller
update_menu();

// Load index controller
index();
?>

<?php
/** CONTROLLERS ***************************************************************************************************/

/**
 * index
 */
function index() {
	// get array menu
    $menu = get_menu();

	// set html menu
	$html = menu_showNested($menu, 0);
	
	// load index view
	index_view($html);
}

/**
 * update_menu
 */
function update_menu() {
    if (is_ajax_request()) {
        $jsonstring = $_POST['json'] ?: "";
        // Decode it into an array
        $jsonDecoded = json_decode($jsonstring, true, 64);

        if (!empty($jsonDecoded)) {
            // Run the function above
            $readbleArray = parseJsonArray($jsonDecoded);

            // Update sql
			upd_menu($readbleArray);
			
			// Echo status message for the update
			echo "The list was updated ".date("y-m-d H:i:s")."!";
		}
		else {
			echo "Error JSON";
		}
        
		die();
	}
}
?>

<?php
/** MODELS ***************************************************************************************************/

/* 
 * get_menu
 * @return array menu
 */
function get_menu() {
	global $db;

	$stmt = $db->query('SELECT COUNT(*) FROM `menu`');
	$num_rows = $stmt->fetchColumn();

    if ($num_rows > 0) {
		$sql = "SELECT * FROM `menu` ORDER BY rang";
		$stmt = $db->query($sql);
		$fmenu = $stmt->fetchAll();

        foreach($fmenu as $row) {
            $menu[$row['parent_id']][] = $row;
        }
	}
	
	return $menu ?: [];
}

/* 
 * upd_menu
 * @return array menu
 */
function upd_menu($array) {
    global $db;

	$sql = "";
	// Loop through the "readable" array and save changes to DB
	foreach ($array as $key => $value) {
		// $value should always be an array, but we do a check
		if (is_array($value)) {
			$sql .= "UPDATE menu SET rang='{$key}', parent_id='{$value['parentID']}' WHERE id='{$value['id']}';";
		}
	}

	$stmt = $db->prepare($sql);
    $stmt->execute();

	return $stmt;
	
}
?>

<? 
/** VIEWS ***************************************************************************************************/

function index_view($content = '', $data = []) {
?>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
	<title>Nestable</title>
	<link href="nestable.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?=$content?>

<!-- Debug: <textarea id='nestableMenu-output'></textarea> -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="jquery.nestable.js"></script>
<script>

$(document).ready(function()
{
	/* The output is ment to update the nestableMenu-output textarea
		* So this could probably be rewritten a bit to only run the menu_updatesort function onchange
	*/
	var updateOutput = function(e)
	{
		var list   = e.length ? e : $(e.target),
			output = list.data('output');
		if (window.JSON) {
			output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
			$.ajax({
				method: "POST",
				url: window.location.href,
				data: { 'json': window.JSON.stringify(list.nestable('serialize'))},
				success: function ( msg ) {
					console.log(msg);
				}
			});
		} else {
			output.val('JSON browser support required for this demo.');
		}
	};
	
	// activate Nestable for list menu
	$('#nestableMenu').nestable({
		group: 1
	})
	.on('change', updateOutput);
		
	// output initial serialised data
	updateOutput($('#nestableMenu').data('output', $('#nestableMenu-output')));

	$('#nestable-menu').on('click', function(e)
	{
		var target = $(e.target),
			action = target.data('action');
		if (action === 'expand-all') {
			$('.dd').nestable('expandAll');
		}
		if (action === 'collapse-all') {
			$('.dd').nestable('collapseAll');
		}
	});

	$('#nestable3').nestable();

});

</script>
</body>
</html>

<? } ?>

<?
/** FUNCTIONS */

/* 
 * menu_showNested
 * @menu array 
 * @parent_id int
 * @return string html
 */
function menu_showNested($menu, $parent_id)	
{
	$html = "";

	if($parent_id === 0) {
		$html .= "<div class='cf nestable-lists'>\n";
		$html .= "\t<div class='dd' id='nestableMenu'>\n\n";
	}

    if (isset($menu[$parent_id])) {
        $html .= "\n";
    	$html .= "\t\t<ol class='dd-list'>\n";
		foreach ($menu[$parent_id] as $row) {
			$html .= "\n";
					
			$html .= "\t\t\t<li class='dd-item' data-id='{$row['id']}'>\n";
			$html .= "\t\t\t\t<div class='dd-handle'>{$row['id']}: {$row['name']}</div>\n";
					
			// Run this function again (it would stop running when the mysql_num_result is 0
			$html .= menu_showNested($menu, $row['id']);
					
			$html .= "\t\t\t</li>\n";
		}
		$html .= "\t\t</ol>\n";
	}

	if($parent_id === 0) {
		$html .= "\t</div>\n";
		$html .= "</div>\n\n";
	}

	return $html;
}

/** 
 * Function to parse the multidimentional array into a more readable array 
 * parseJsonArray
 * @jsonArray array
 * @partentID int
 */
function parseJsonArray($jsonArray, $parentID = 0)
{
	$return = array();
	foreach ($jsonArray as $subArray) {
		$returnSubSubArray = array();
		if (isset($subArray['children'])) {
		$returnSubSubArray = parseJsonArray($subArray['children'], $subArray['id']);
		}
		$return[] = array('id' => $subArray['id'], 'parentID' => $parentID);
		$return = array_merge($return, $returnSubSubArray);
	}

	return $return;
}

/** 
 * is_ajax_request
 */
function is_ajax_request() {
	return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

?>