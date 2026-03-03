<?php

	$err_msg = "";
	if(mysqli_real_escape_string($connection_server, trim(strip_tags($_GET["err"]))) === "1"){
		$err_msg .= "Error: Empty Fields";
	}

	if(mysqli_real_escape_string($connection_server, trim(strip_tags($_GET["err"]))) === "2"){
		$err_msg .= "Error: Term with same details already exists in database";
	}

	if(mysqli_real_escape_string($connection_server, trim(strip_tags($_GET["err"]))) === "3"){
		$err_msg .= "Error: Another Term has been with same details already exists in database";
	}

	$check_column_query = "SHOW COLUMNS FROM sm_terms LIKE 'next_term_begins'";
	$check_column_result = mysqli_query($connection_server, $check_column_query);
	if(mysqli_num_rows($check_column_result) == 0){
		$alter_table_query = "ALTER TABLE sm_terms ADD next_term_begins DATE NOT NULL AFTER term_name, ADD school_open_days INT NOT NULL AFTER next_term_begins";
		mysqli_query($connection_server, $alter_table_query);
	}

	$header_add_button = "add_term";
	$additional_add_tag = "&id=".$get_logged_user_details['school_id_number'];
	//$header_view_button = "view_term";
	$additional_back_tag .= "&id=".$get_logged_user_details['school_id_number'];

	if((isset($_GET["prevnext"])) && (trim(strip_tags($_GET["prevnext"])) > 0) && (trim(strip_tags($_GET["prevnext"])) !== "")){
		$prev_next = "&prevnext=".trim(strip_tags($_GET["prevnext"]));
		$current_page_no = trim(strip_tags($_GET["prevnext"]));
		if((trim(strip_tags($_GET["prevnext"]))-1) >= 1){
			$prev_btn = (trim(strip_tags($_GET["prevnext"]))-1);
		}else{
			$prev_btn = 1;
		}

		if((trim(strip_tags($_GET["prevnext"]))+1) == 1){
			$next_btn = (trim(strip_tags($_GET["prevnext"]))+2);
		}else{
			if((trim(strip_tags($_GET["prevnext"]))+1) < 1){
				$next_btn = 2;
			}else{
				$next_btn = (trim(strip_tags($_GET["prevnext"]))+1);
			}
		}
	}else{
		$prev_next = "&prevnext=1";
		$current_page_no = "1";
		$prev_btn = 1;
		$next_btn = 2;

	}

	if(isset($_GET["search"])){
		$search_text = trim(strip_tags($_GET["search"]));
	}else{
		$search_text = "";
	}

	if((isset($_GET["pnum"])) && (trim(strip_tags($_GET["pnum"])) > 0) && (trim(strip_tags($_GET["pnum"])) !== "")){
		$page_pnum = trim(strip_tags($_GET["pnum"]));
	}else{
		$page_pnum = "10";
	}
	$page_list_number_link = "/bc-admin.php?page=".trim(strip_tags($_GET["page"]))."&tab=".trim(strip_tags($_GET["tab"])).$additional_add_tag.$prev_next."&search=".$search_text."&pnum=";
	$page_prevnext_link = "/bc-admin.php?page=".trim(strip_tags($_GET["page"]))."&tab=".trim(strip_tags($_GET["tab"])).$additional_add_tag."&search=".$search_text."&pnum=".$page_pnum;

	$chopped_search_text_array = array_filter(explode(" ",trim($search_text)));
	foreach($chopped_search_text_array as $search_items){
		$search_sqli_statement .= "term_name LIKE '%".$search_items."%'";

	}

	$search_sqli_statements .= "(".str_replace("\n"," && school_id_number=".$get_logged_user_details['school_id_number'].") OR (", trim($search_sqli_statement))." && school_id_number=".$get_logged_user_details['school_id_number'].")";

	if((isset($_GET["search"])) && (trim(strip_tags($_GET["search"])) !== "")){
		$select_term_table_lists = mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE $search_sqli_statements LIMIT $page_pnum OFFSET ".((($current_page_no)-1)*$page_pnum));
		$select_all_term_table_lists = mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE $search_sqli_statements");
	}else{
		$select_term_table_lists = mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='".trim(strip_tags($_GET['id']))."' LIMIT $page_pnum OFFSET ".((($current_page_no)-1)*$page_pnum));
		$select_all_term_table_lists = mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='".trim(strip_tags($_GET['id']))."'");
	}

	if(isset($_POST["add-term"])){
		$term_name = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST["term-name"])));
		$next_term_begins = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST["next-term-begins"])));
		$school_open_days = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST["school-open-days"])));
		$school_id = $get_logged_user_details["school_id_number"];

        $all_term_num_count = mysqli_num_rows(mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id'"));
		$check_last_term = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id' ORDER BY id_number DESC LIMIT 1"));
		$term_no = sprintf("%03d",(($check_last_term["id_number"]) + 1));

		$search_term_with_name = mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id' && (term_name='$term_name')");

		if(!empty($term_name) && !empty($next_term_begins) && !empty($school_open_days) && !empty($school_id)){
			if(mysqli_num_rows($search_term_with_name) == 0){
				if(mysqli_query($connection_server, "INSERT INTO sm_terms (school_id_number, id_number, term_name, next_term_begins, school_open_days) VALUES ('$school_id','$term_no','$term_name','$next_term_begins','$school_open_days')") == true){
					$redirect_url = "/bc-admin.php?page=smgt_term&tab=true".$additional_back_tag;
				}
			}else{
				$redirect_url = $_SERVER["REQUEST_URI"]."&err=2";
			}
		}else{
			$redirect_url = $_SERVER["REQUEST_URI"]."&err=1";
		}

		header("Location: ".$redirect_url);
	}

	if(isset($_POST["update-term"])){
		$term_name = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST["term-name"])));
		$next_term_begins = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST["next-term-begins"])));
		$school_open_days = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST["school-open-days"])));
		$school_id = $get_logged_user_details["school_id_number"];

		$current_term_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_GET["edit"])));

		$search_term_with_id = mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id' && id_number='$current_term_id'");

		if(!empty($term_name) && !empty($next_term_begins) && !empty($school_open_days) && !empty($school_id)){
			if(mysqli_num_rows($search_term_with_id) == 1){
					if(mysqli_query($connection_server, "UPDATE sm_terms SET term_name='$term_name', next_term_begins='$next_term_begins', school_open_days='$school_open_days' WHERE (school_id_number='$school_id' && id_number='$current_term_id')") == true){
						$redirect_url = "/bc-admin.php?page=smgt_term&tab=true".$additional_back_tag;
					}
			}else{
				$redirect_url = $_SERVER["REQUEST_URI"]."&err=3";
			}
		}else{
			$redirect_url = $_SERVER["REQUEST_URI"]."&err=1";
		}

		header("Location: ".$redirect_url);
	}

	if(isset($_POST["delete-term"])){
		$term_id = $_POST["term_id"];
		$school_id = $_POST["school_id"];
		foreach($term_id as $index => $term_id_no){
			$term_id_num = mysqli_real_escape_string($connection_server, $term_id[$index]);
			$sch_id_number = mysqli_real_escape_string($connection_server, $school_id[$index]);
			$delete_school_selected_term = mysqli_query($connection_server, "DELETE FROM sm_terms WHERE (school_id_number='$sch_id_number' && id_number='$term_id_num')");
		}
		$redirect_url = $_SERVER["REQUEST_URI"];

		header("Location: ".$redirect_url);
	}

	if(isset($_POST["search-item"])){
		$search_item_text = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST["search-item"])));

		$page_to_go_link = "/bc-admin.php?page=".trim(strip_tags($_GET["page"]))."&tab=".trim(strip_tags($_GET["tab"])).$additional_add_tag."&search=".$search_item_text."&pnum=".$page_pnum;
		header("Location: ".$page_to_go_link);
	}
?>