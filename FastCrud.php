<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: "GET,POST"');

	class FastCrud{
		public $pdo;
		public $debug = False;
		public $language = Array(
			"send" => "Send",
			"delete" => "Delete",
			"delete_confirm" => "Are you sure you want to delete this item?"
		);
		private $current_url;

		function __construct($host, $user, $pwd, $db) {
			$this->current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			if(strstr($this->current_url, "?"))
				$this->current_url = explode("?", $this->current_url)[0];

			$this->pdo = new PDO('mysql:host='.$host.';dbname='.$db, $user, $pwd);

			if(!isset($_POST["fast-crud-send"]) && !isset($_GET["fc_delete"]) && !isset($_GET["fc_edit"])){
			?>

			<link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
			<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
			<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>

			<link rel="stylesheet" href="//cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
			<script src="//cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

			<script src="https://cdnjs.cloudflare.com/ajax/libs/sifter/0.5.4/sifter.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/microplugin/0.0.3/microplugin.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/selectize.min.js"></script>
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.default.min.css"> 

			<style>
				.fast_crud_error_form{
					border: 2px solid #f00;
				}

				.lds-ring {
					display: none;
					position: fixed;
					width: 80px;
					height: 80px;
					left: 50%;
					top: 50%;
					transform: translate(-50%, -50%);
					background: rgba(0,0,0,0.7);
					padding: 10px 50px;
					border-radius: 15px;
					z-index: 999;
				}
				.lds-ring div {
				  box-sizing: border-box;
				  display: block;
				  position: absolute;
				  width: 64px;
				  height: 64px;
				  margin: 8px;
				  border: 8px solid #fff;
				  border-radius: 50%;
				  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
				  border-color: #fff transparent transparent transparent;
				}
				.lds-ring div:nth-child(1) {
				  animation-delay: -0.45s;
				}
				.lds-ring div:nth-child(2) {
				  animation-delay: -0.3s;
				}
				.lds-ring div:nth-child(3) {
				  animation-delay: -0.15s;
				}
				@keyframes lds-ring {
				  0% {
					transform: rotate(0deg);
				  }
				  100% {
					transform: rotate(360deg);
				  }
				}
			</style>

			<div class="lds-ring"><div></div></div>

			<?php
			}
		}

		function validation($str, $rule){
			if(
				($rule == "mandatory" && trim($str) != "") ||
				($rule == "email" && filter_var($str, FILTER_VALIDATE_EMAIL)) ||
				(eval("return " . str_replace("%", '$str', $rule) . ";"))
			)
				return True;
			return False;
		}

		function show_attributes($field){
			$ret = "";
			foreach($field as $key => $val){
				if($key != "type" && $key != "validation" && $key != "label" && $key != "options"){
					$ret.= " " . $key . '="' . $val . '"';
				}
			}
			return $ret;
		}

		function check_table($json, $table, $id_column_name){
			$sth = $this->pdo->prepare("SHOW TABLES LIKE ?");
			$sth->execute(array($table));
			if(!count($sth->fetchAll(PDO::FETCH_CLASS))){
				$sql = "CREATE TABLE `".$table."` (
				  `".$id_column_name."` int(11) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				ALTER TABLE `".$table."`
				  ADD PRIMARY KEY (`".$id_column_name."`);

				ALTER TABLE `".$table."`
				  MODIFY `".$id_column_name."` int(11) NOT NULL AUTO_INCREMENT;";

				$sth = $this->pdo->prepare($sql);
				$sth->execute();
			}

			foreach($json as $divs){
				foreach($divs as $field){
					if(isset($field->{"no_db"}))
						continue;
					$col = str_replace("[]","",$field->{"name"});

					$type = "TEXT";
					if(strstr($field->{"type"},"datetime"))
						$type = "DATETIME";
					if($field->{"type"} == "date")
						$type = "DATE";
					if($field->{"type"} == "number")
						$type = "INT(11)";

					$sth = $this->pdo->prepare("SHOW COLUMNS FROM ? LIKE ?");
					$sth->execute(array($table, $col));
					if(!count($sth->fetchAll(PDO::FETCH_CLASS))){
						$sth = $this->pdo->prepare("ALTER TABLE `".$table."` ADD `".$col."` ".$type." NOT NULL");
						$sth->execute();
					}
				}
			}
		}

		function create($template, $table, $id_column_name, $db_function_edit = NULL, $db_function_complete = NULL, $js_function_complete = NULL){
			if(file_exists(($template)))
				$json = json_decode(file_get_contents($template));
			else
				$json = json_decode($template);
			$this->check_table($json, $table, $id_column_name);

			if(isset($_GET["fc_edit"])){
				header('Content-Type: application/json');
				$unique = [];
				$column_list = [];
				foreach($json as $divs){
					foreach($divs as $field){						
						if( $field->type != "submit" && !in_array($field->name, $unique)){
							$unique[] = $field->name;
							$column_list[] = "`" . str_replace("[]", "", $field->name) . "`";
						}
					}
				}

				$sql = "SELECT ". implode(",", $column_list) ." FROM `" . $table . "` WHERE `" . $id_column_name . "` = ?";

				if($this->debug){
					echo $sql . "<br />";
					var_dump($_GET["fc_edit"]);
					echo "<br />";
				}

				$sth = $this->pdo->prepare($sql);

				if (!$sth->execute(array($_GET["fc_edit"])))
					die(json_encode(Array("error select for edit" => $stm->errorInfo())));
				else
					die(json_encode($sth->fetchAll(PDO::FETCH_CLASS)[0]));

			} else if(isset($_GET["fc_delete"])){
				header('Content-Type: application/json');
				$sql = "DELETE FROM `" . $table . "` WHERE `" . $id_column_name . "` = ?";
				if($this->debug){
					echo $sql . "<br />";
					var_dump($_GET["fc_delete"]);
					echo "<br />";
				}
				$stm= $this->pdo->prepare($sql);
				
				if (!$stm->execute([$_GET["fc_delete"]]))
					die(json_encode(Array("error delete" => $stm->errorInfo())));
				else
					die(json_encode(Array("success" => 1)));
			} else if(isset($_POST["fast-crud-send"])){
				header('Content-Type: application/json');
				$arg = [[],[],[]];
				$unique = [];
				$error = [];
				$toevalutate = [];
				$json[] = json_decode('[{"type": "hidden", "name": "'.$id_column_name.'"}]');

				foreach($json as $divs){
					foreach($divs as $field){						
						if( $field->type != "submit" && !in_array($field->name, $unique) && !isset($field->no_db)){
							$unique[] = $field->name;
							$name = str_replace("[]", "", $field->name);

							if(is_array($_POST[$name]))
								$val = json_encode($_POST[$name]);
							else
								$val = $_POST[$name];

							if(isset($field->validation)){
								foreach($field->validation as $valid){
									if(!$this->validation($val, $valid[0]))
										$error[] = [$field->name, $valid[1]];
								}
							}

							$toevalutate[$name] = $val;
						}
					}
				}

				if(count($error)){
					die(json_encode(Array("error" => $error)));
				}

				if(!is_null($db_function_edit))
					$toevalutate = call_user_func($db_function_edit, $toevalutate);

				foreach($toevalutate as $key => $val){
					$arg[0][] = "`" . $key . "`";
					$arg[2][] = $val;
					$arg[1][] = "?";
				}
				
				$sql = "REPLACE INTO `" . $table . "` (".implode(",", $arg[0]).") VALUES (".implode(",", $arg[1]).")";

				if($this->debug){
					echo $sql . "<br />";
					var_dump($arg[2]);
					echo "<br />";
				}

				$stm= $this->pdo->prepare($sql);

				if (!$stm->execute($arg[2]))
					die(json_encode(Array("error replace into" => $stm->errorInfo())));

				if(!is_null($db_function_complete))
					call_user_func($db_function_complete, [$this->pdo->lastInsertId()]);

				die(json_encode(Array("success" => 1)));
			} else {
				$id = str_replace(' ', '-', $template);
				$id = str_replace('.json', '', $id);
				$id = "fc_" . preg_replace('/[^A-Za-z0-9\-]/', '', $id);

				echo '
				<div attr-fast-crud-id="' . $id . '">
				<div id="fast-crud-dialog-' . $id . '" style="display:none;">
				<div id="fast_crud_error_container"></div>
				<form>';

				foreach($json as $divs){ 
					echo '<div class="form_separator">';
					foreach($divs as $field){ 
						if(isset($field->{"label"}))
							echo "<label>" . $field->{"label"} . "</label>";
						if( $field->{"type"} == "textarea" ){
							echo '<textarea';
						} else if( $field->{"type"} == "select" ){
							echo '<select';
							if(isset($field->{"add_new_item"}))
								echo ' attr-add-new-item="1"';
							else
								echo ' attr-add-new-item="0"';
						} else if ($field->{"type"} != "radio" && $field->{"type"} != "checkbox") {
							echo '<input type="'.$field->{"type"}.'"';
						}

						if ($field->{"type"} != "radio" && $field->{"type"} != "checkbox")
							echo $this->show_attributes($field);
						else{
							foreach($field->{"options"} as $opt){
								if(count($opt) == 2)
									$opt_val = $opt[1];
								else
									$opt_val = $opt[0];

								echo '<input' . $this->show_attributes($field) . ' type="'.$field->{"type"}.'" value="'.$opt_val.'"> '.$opt[0];
							}
						}

						if( $field->{"type"} == "textarea" ){
							echo '></textarea>';
						} else if( $field->{"type"} == "select" ){
							echo '>';

							foreach($field->{"options"} as $opt){
								if(count($opt) == 2)
									$opt_val = $opt[1];
								else{
									$opt_val = $opt[0];
									/*$opt_val = strtolower($opt[0]);
									$opt_val = str_replace(' ', '-', $opt_val);
									$opt_val = preg_replace('/[^A-Za-z0-9\-]/', '', $opt_val);*/
								}

								echo '<option value="'.$opt_val.'">'.$opt[0].'</option>';
							}

							echo '</select>';
						} else if ($field->{"type"} != "radio" && $field->{"type"} != "checkbox"){
							echo ' />';
						}
					}
					echo "</div> \n";
				}
				?>
				<div class="form_separator">
					<input type="submit" name="fast-crud-send" value="<?php echo $this->language["send"]; ?>" />
				</div>
				<div class="form_separator">
					<input type="button" name="fast-crud-delete" value="<?php echo $this->language["delete"]; ?>" style="display:none;" />
				</div>
				<form></div></div>

				<script>
					$( function() {
						const now = new Date();
  						now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
						$('input[type="datetime-local"][value="NOW()"],input[type="datetime"][value="NOW()"]').val(now.toISOString().slice(0, -8));
						$('input[type="date"][value="NOW()"]').val(now.toISOString().slice(0, -14));

						var dialog = "#fast-crud-dialog-<?php echo $id; ?>";
						$(document).delegate("#fast-crud-add, .fast-crud-edit", "click", function(){
							$(dialog + ' #fast-crud-id-edit').remove();
							$(dialog + ' input[name="fast-crud-delete"]').css("display", "none");

							$(dialog).css("display", "block");
							$(dialog).dialog({
								close: function( event, ui ) {
									$(dialog + ' form')[0].reset();
								}
							});

							if(typeof $(this).attr("class") !== "undefined" && $(this).attr("class").indexOf("fast-crud-edit") != -1){
								$(".lds-ring").css("display", "inline-block");
								$.ajax({
									url: "<?php echo $this->current_url; ?>",
									method: "GET",
									data: {"fc_edit": $(this).attr("attr-id")},
									success: function(e){
										for(var i in e){
											try {
												$(dialog + ' *[name="' + i + '"]').each(function(){
													$(this).attr("checked", false);
												});

												var val_list = JSON.parse(e[i]);

												if(!Array.isArray(val_list))
													throw i + ' is not array';

												for(var j in val_list){
													$(dialog + ' *[name="' + i + '[]"][value="' + val_list[j] + '"]').prop("checked", true);
													$(dialog + ' *[name="' + i + '[]"] option[value="' + val_list[j] + '"]').prop("selected", true);
												}
											} catch(err) {
												if($(dialog + ' *[name="' + i + '"]').length > 1){
													$(dialog + ' *[name="' + i + '"][value="' + e[i] + '"]').prop("checked", true);
												}
												else{
													$(dialog + ' *[name="' + i + '"]').val(e[i]);
												}
											}
										}
									},
									complete: function(){
										$(".lds-ring").css("display", "none");
									}
								});

								$(dialog + ' input[name="fast-crud-delete"]').css("display", "block").attr("attr-id", $(this).attr("attr-id"))

								$(dialog + " form").append('<input type="hidden" name="<?php echo $id_column_name; ?>" value="' + $(this).attr("attr-id") + '" id="fast-crud-id-edit" />');

								$(dialog + ' input[name="fast-crud-delete"]').click(function(){
									if (confirm("<?php echo $this->language["delete_confirm"]; ?>") == true) {
										$(".lds-ring").css("display", "inline-block");
										$.ajax({
											url: "<?php echo $this->current_url; ?>",
											method: "GET",
											data: {"fc_delete": $(this).attr("attr-id")},
											success: function(e){
												<?php if(!is_null($js_function_complete)){ ?>
													<?php echo $js_function_complete . '({"fc_delete": $(this).attr("attr-id")});' ?>
												<?php } else { ?>
													<?php echo 'location.reload();'; ?>
												<?php } ?>
											},
											complete: function(){
												$(".lds-ring").css("display", "none");
											}
										});
									}
								});
							}
						});

						$(dialog + " form").submit(function(e){
							e.preventDefault();
							$(dialog + ' *').removeClass("fast_crud_error_form");
							$(dialog + ' #fast_crud_error_container').html("");

							$(".lds-ring").css("display", "inline-block");
							var data_send = $(this).serialize();
							$.ajax({
								url: "<?php echo $this->current_url; ?>",
								method: "POST",
								data: data_send + "&fast-crud-send=1",
								success: function(e){
									if(typeof e["error"] !== "undefined"){
										var show_error = false;
										for(var i in e["error"]){
											if(typeof e["error"][i] !== "undefined" && typeof e["error"][i][1] !== "undefined"){

												//console.log(dialog + ' *[name="'+e["error"][i][0]+'"]');
												$(dialog + ' *[name="'+e["error"][i][0]+'"]').addClass("fast_crud_error_form");
												$(dialog + ' #fast_crud_error_container').append(e["error"][i][1] + "<br />");
											}
											else
												show_error = true;
											
										} 
										if(show_error){
											$(dialog + ' #fast_crud_error_container').append(e["error"][2] + "<br />");
										}
									} else {
										<?php if(!is_null($js_function_complete)){ ?>
											<?php echo $js_function_complete . '(data_send);' ?>
										<?php } else { ?>
											<?php echo 'location.reload();'; ?>
										<?php } ?>
										//$(dialog).dialog( "close" );
									}
								},
								error: function(e){
									alert("error");
									console.log(e);
								},
								complete: function(){
									$(".lds-ring").css("display", "none");
								}
							});
						});

						$('select[attr-add-new-item="0"]').selectize()

						$('select[attr-add-new-item="1"]').selectize({
							delimiter: ",",
							removeOption: 1,
							persist: false,
							create: function (input) {
								return {
									value: input,
									text: input,
								};
						  	},
						});
					} );
				</script>
				<?php
			}
		}

		function generateRandomString($length = 10) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}

		function view($data, $head, $DataTable = "{deferRender: true, scrollY: 200, scrollCollapse: true, scroller: true}"){
			$random_id = $this->generateRandomString();
			?>
			<table id="<?php echo $random_id; ?>" class="fast_crud_table display nowrap" style="width:100%">
				<thead>
					<tr>
						<th><?php echo implode("</th><th>", $head); ?></th>
					</tr>
				</thead>
			</table>
			<script>
			$(document).ready(function() {
				var data = <?php echo json_encode($data); ?>;
				var dt = <?php echo $DataTable; ?>;
				dt["data"] = data;
				 
				$('#<?php echo $random_id; ?>').DataTable( dt );
			} );
			</script>
			<?php
		}
	}
