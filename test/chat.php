<?php
error_reporting(E_ALL & ~E_NOTICE);	
$con = mysqli_connect("localhost", "root", "", "chat_room");

//print_r( $_SERVER );
//exit;

if( $_SERVER['REQUEST_METHOD'] == "POST" && preg_match("/json/i",$_SERVER['CONTENT_TYPE']) ){
	$postdata = file_get_contents("php://input" );
	$_POST = json_decode($postdata,true);
}

if( $_POST['action'] == "load_messages" ){
	$query = "select * from messages where id > " . $_POST['last_id'] . " order by id ";
	$res = mysqli_query($con, $query);
	$msgs = [];
	while( $row = mysqli_fetch_assoc($res) ){
		$msgs[] = $row;
	}
	echo json_encode([
		"status"=> "success",
		"messages"=> $msgs
	]);
	exit;
}

if( $_POST['action'] == "send_msg" ){
	$query = "insert into messages set 
	from_user = '" . mysqli_escape_string($con, $_POST['from_user'] ) . "',
	message = '" .  mysqli_escape_string($con, $_POST['message'] ) . "' ";
	mysqli_query($con, $query);
	if( mysqli_error($con) ){
		echo json_encode([
			"status"=>"fail",
			"error"=>mysqli_error($con)
		]);
	}
	echo json_encode([
			"status"=>"success",
			"id"=>mysqli_insert_id($con)
		]);
	exit;
}
?>
<html>
<head>
<title>Vuejs Chat with Axios</title>
<script src="vue.min.js" ></script>
<script src="axios.min.js" ></script>
</head>
<body>
<div id="app" >
	<div>Username: <input type="text" v-model="username" ></div>
	<div id="messages_div" 	style="border:1px solid #ccc;padding: 10px; height: 300px; overflow: auto;" >
		<p v-for="m in messages" >{{ m['from_user'] }}: {{ m['message'] }}</p>
	</div>
	<input type="text" v-model="new_message" v-on:keyup.enter="send_msg" ><input type="button" value="Send" v-on:click="send_msg" >
</div>
<script>
	var app = new Vue({
		el:"#app",
		data: {
			messages: [
				{
					"from_user": "apparao",
					"message": "Hi"
				}
			],
			new_message: "",
			username: "",
			last_read_id: 0,
		},
		mounted: function(){
			setInterval(this.load_messages,3000);
		},
		methods: {

			/*
			vpostdata = "action=send_msg&from_user="+encodeURIComponent(this.username)+"&message="+encodeURIComponent(this.new_message);
				axios.post("?", vpostdata,{
					"headers": {
						"Content-Type": "application/x-www-form-urlencoded"
					}
				}).then(response=>{
			*/
			load_messages: function(){
				axios.post("?", {
					"action": "load_messages",
					"last_id": this.last_read_id,
				}).then(response=>{
					if( response.status == 200 ){
						if( typeof( response.data ) =="object" ){
							if( "status" in response.data ){
								if( response.data['status'] == "success" ){
									for(var i=0;i<response.data['messages'].length;i++){
										console.log( this.username + " : " +response.data['messages'][i]['from_user'] );
										if( this.username != response.data['messages'][i]['from_user'] ){
											console.log("Message is not mine");
											this.messages.push(response.data['messages'][i]);
										}
										this.last_read_id = response.data['messages'][i]['id'];
									}
									document.getElementById('messages_div').scrollTop = 9999999;
									//this.last_read_id = response.data['messages'][ response.data['messages'].length-1 ]['id'];
									setTimeout("document.getElementById('messages_div').scrollTop = 9999999;",200);
								}
							}else{
								alert( "error: " + response.data['error']);
							}
						}else{
							alert( "error");
						}
					}else{
						alert( "error");
					}
				});
			},
			send_msg: function(){
				if( this.username == ""){
					alert("Enter username");
					exit;
				}
				if( this.new_message == ""){
					alert("Enter message");
					exit;
				}
				//con.setRequestHeader("Content-Type": "application/x-form-www-urlencoded");
				axios.post("?",{
					"action": "send_msg",
					"message": this.new_message,
					"from_user": this.username
				}).then(response=>{
					if( response.status == 200 ){
						if( typeof( response.data ) =="object" ){
							if( "status" in response.data ){
								if( response.data['status'] == "success" ){
									this.messages.push({
										"id": response.data['id'],
										"from_user": this.username,
										"message": this.new_message
									});
									this.new_message = '';
									document.getElementById("new_message").focus();
								}
							}else{
								alert( "error: " + response.data['error']);
							}
						}else{
							alert( "error");
						}
					}else{
						alert( "error");
					}
					
				})
			}
		}
	});
</script>
</body>
</html>