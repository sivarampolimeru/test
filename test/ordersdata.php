<?php
error_reporting(E_ALL &~ E_NOTICE);
?>
<?php
$con = mysqli_connect("localhost","root", "", "bill_generator" );
if( mysqli_connect_error() ){
	echo "DB Error: ". mysqli_connect_error();
	exit;
}
	############# order #################

if( $_GET['action'] == "load_order" ){
	$orders = [];
	$res = mysqli_query( $con, "select * from orders order by name,date,address");
	while( $row = mysqli_fetch_assoc($res) ){
		$orders[] = $row;
	}
	echo json_encode($orders);
	exit;
}
if( $_GET['action'] == "load_products" ){
	$orders = [];
	$res = mysqli_query( $con, "select * from products order by name");
	while( $row = mysqli_fetch_assoc($res) ){
		$orders[] = $row;
	}
	echo json_encode($orders);
	exit;
}

if($_GET['action'] == "delete_order"){
	$res = mysqli_query($con, "delete from orders where id = " .$_GET['order_id']);
	if( mysqli_error($con) ){
		echo json_encode([
			"status"=>"error",
			"error"=>mysqli_error($con)
		]);	
		exit;
	}
	echo json_encode([
		"status"=>"success",
	]);
	exit;
}

if($_POST['action'] == "add_order"){

	$order = json_decode($_POST['new_order'],true);
	//print_r( $order );	exit;

	$res = mysqli_query($con, "insert into orders set
		name = '".mysqli_escape_string($con,$_POST['name'])."',
		date = '".mysqli_escape_string($con,$_POST['date'])."',
		address = '".mysqli_escape_string($con,$_POST['address'])."',
		amount = '".mysqli_escape_string($con,$_POST['amount'])."'
		");
	if(mysqli_error($con)){
		echo json_encode([
			"status"=> "error",
			"error"=> "Db error: " . mysqli_error($con)
		]);
		exit;
	}
	$new_id = mysqli_insert_id($con);

	foreach( $order['items'] as $i=>$j ){
		$res = mysqli_query($con, "insert into order_items set
			order_id = '" . $new_id . "',
			product_id = '".mysqli_escape_string($con,$j['product_id'])."',
			qty = '".mysqli_escape_string($con,$j['qty'])."',
			price = '".mysqli_escape_string($con,$j['price'])."',
			total = '".mysqli_escape_string($con,$j['total'])."'
			");
		if(mysqli_error($con)){
			echo json_encode([
				"status"=> "error",
				"error"=> "Db error: " . mysqli_error($con)
			]);
			exit;
		}
	}

	echo json_encode([
		"status"=> "success",
		"new_order_id"=> $new_id
	]);
	exit;
}

if( $_GET['action'] == "edit_order" ){
	$res = mysqli_query( $con, "update orders set
		 name = '".mysqli_escape_string($con,$_GET['name'])."',
		date = '".mysqli_escape_string($con,$_GET['date'])."',
		address = '".mysqli_escape_string($con,$_GET['address'])."'
		where id = " . $_GET['order_id']);
	if( mysqli_error($con) ){
		echo mysqli_error($con);exit;
	}
	echo "success";
	exit;
}

	
?>

<html>
<head>
	<script src="vue.min.js" ></script>
	<script src="axios.min.js" ></script>
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
</head>
<body>
<div id="app">

			<div>
				<a href="bill_database.php" class="btn btn-dark">Back</a>
			</div>

	<div id="orders_div" style="position: absolute; display: none; background-color: white;">
			<div class="modal" tabindex="-1" style="display: block;">
				<div class="modal-dialog">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title">Add Cart</h5>
				        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" v-on:click="hide_add_order_form"></button>
				      </div>
				      <div class="modal-body">

				       	<table class="table table-bordered table-sm">
						<tbody>
						<tr>
							<td>Customer Name</td><td>Date</td><td>Address</td>
						</tr>
						<tr>
							<td><input  class="form-control" type="text" v-model="new_order['name']" placeholder="Customer Name"></td>
							<td><input  class="form-control" type="date" v-model="new_order['date']"></td>
							<td><input  class="form-control" type="text" v-model="new_order['address']"></td>
						</tr>
					</tbody></table>

					<table class="table table-bordered table-sm ">
						<tr>
							<td>Product</td>
							<td>Qty</td>
							<td>Price</td>
							<td>Total</td>
							<td>-</td>
						</tr>
						<tr v-for="o,i in new_order['items']" >
							<td>
								<select v-model="new_order['items'][i]['product_id']" v-on:change="select_product(i)">
									<option v-for="o,oi in products" v-bind:value="o['id']">{{o['name']}}</option>
								</select>
							</td>
							<td>
								<input type="number" v-model="new_order['items'][i]['qty']" style="width:50px;" v-on:change="calc_total">
							</td>
							<td>
								<input disabled v-model="new_order['items'][i]['price']" style="width:80px;">
							</td>
							<td>
								<input disabled v-model="new_order['items'][i]['total']" style="width:80px;">
							</td>
							<td>
								<input type="button" value="X" v-on:click="new_order_delete_item(i)">
							</td>
						</tr>
						<tr>
							<td colspan="3"></td>
							<td><input disabled type="text" v-model="new_order['amount']">
							<td><input type="button" v-on:click="new_order_add_item" value="+">
						</tr>
					</table>

				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" v-on:click="hide_add_order_form">Close</button>
				        <input type="button" class="btn btn-primary btn-sm" value="ADD" v-on:click="add_order">
				      </div>
				    </div>
				  </div>
				</div>
			</div>
		<table class="table table-bordered table-sm"><tr>
		<td>
			<div>
				<input type="button" v-on:click="show_add_order_form" value="+" class="btn btn-primary">
			</div>

				<table class="table table-bordered table-sm ">
				<tr><td>Date</td>
					<td>Name</td>
					<td>Address</td>
					<td>Amount</td>
					<td>-</td></tr>
				<tr v-for="p,i in orders" >
					<td>
						<div>
							<div v-if="p['edit']==false">{{ p['date'] }}</div>
							<div v-else >
								<input v-model="orders[i]['date']">
							</div>
						</div>
					</td>
					<td>
						<div>
							<div v-if="p['edit']==false">{{ p['name'] }}</div>
							<div v-else >
								<input v-model="orders[i]['name']">
							</div>
						</div>
					</td>
					<td>
						<div>
							<div v-if="p['edit']==false">{{ p['address'] }}</div>
							<div v-else >
								<input v-model="orders[i]['address']">
							</div>
						</div>
					</td>
					<td>
						<div>
							<div v-if="p['edit']==false">{{ p['amount'] }}</div>
							<div v-else >
								<input v-model="orders[i]['amount']">
							</div>
						</div>
					</td>
					<td>
						<input v-if="p['edit']==false" type="button" v-on:click="edit_order(i)" value="E" >
						<input v-else type="button" v-on:click="save_order(i)" value="save" >
						<input type="button" v-on:click="deleteorder(i)" value="X" >
					</td>
				</tr>
			</table>
			<!-- <pre>{{orders}}</pre> -->
		</td>
	</tr>
</table>
</div>
<script>


var app = new Vue({
	el: "#app",
	data: {
		new_order:{
			"name":"",
			"date":"", 
			"address":"",
			"amount":"",
			"items": [
				{
					"product_id": 0,
					"qty": 1,
					"price": 0,
					"total": 0,
				}
			]
		},
		edit: false,
		orders: [],
		order_items: [],
		products: [],
	},
	mounted: function(){
		this.load_products();
		this.load_order();
		//this.load_order_items();
	},
	methods: {
		new_order_add_item: function(){
			this.new_order['items'].push({
				"product_id":"",
				"qty": 1,
				"price": 0,
				"total": 0,
			});
		},
		new_order_delete_item: function(vi){
			this.new_order['items'].splice(vi,1);
		},
		select_product: function(vi){
			pid = this.new_order['items'][vi]['product_id'];
			for(var i=0;i<this.products.length;i++){
				if( this.products[i]['id'] == pid ){
					this.$set( this.new_order['items'][vi],'price', this.products[i]['price'] );
				}
			}
			this.calc_total();
		},
		calc_total: function(){
			var tot = 0;
			for(var i=0;i<this.new_order['items'].length;i++){
				var t = this.new_order['items'][i]['qty']*this.new_order['items'][i]['price'];
				this.$set( this.new_order['items'][i],'total',t);
				tot = tot + t;
			}
			this.$set( this.new_order,'amount',tot);
		},
		load_order: function(){
			con = new XMLHttpRequest();
			con.open("GET", "?action=load_order",true);
			con.onload = function(){
				var p = JSON.parse(this.responseText);
				for(var i=0;i<p.length;i++){
					//se[i] = false;
					p[i]['edit'] = false;
				}
				app.orders = p;
			} 
			con.send();
		},
		load_products: function(){
			con = new XMLHttpRequest();
			con.open("GET", "?action=load_products",true);
			con.onload = function(){
				var p = JSON.parse(this.responseText);
				app.products = p;
			} 
			con.send();
		},
		show_add_order_form:function(){
			document.getElementById("orders_div").style.display = "block";
		},
		hide_add_order_form:function(){
			document.getElementById("orders_div").style.display = "none";
		},
		add_order: function(){
			if (this.new_order['name'] == '') {
				alert("Please Enter the customer Name !");
			}else if (this.new_order['date'] == '') {
				alert("Please Enter the date !");
			}else if (this.new_order['address'] == '') {
				alert("Please Enter the address !");
			}else if (this.new_order['product_name'] == '') {
				alert("Please Enter the product name!");
			}else if (this.new_order['name'] == '') {
				alert("Please Enter the order Name !");
			}else {
				vpostdata = "action=add_order&new_order="+JSON.stringify(this.new_order)+"&date"+encodeURIComponent(this.new_order['date'])+"&name"+encodeURIComponent(this.new_order['name'])+"&address"+encodeURIComponent(this.new_order['address'])+"&amount"+encodeURIComponent(this.new_order['amount']);
				con = new XMLHttpRequest();
				con.open("POST","?",true);
				con.onload = function(){
					try{
						var st = JSON.parse( this.responseText );
						if(st['status'] == 'success'){
							app.orders.push({
								"id":st["new_order_id"]+"",
								"date":app.new_order['date'],
								"name":app.new_order['name'],
								"address":app.new_order['address'],
								"amount":app.new_order['amount'],
							})
							app.hide_add_order_form();
						}else{
							alert("There was an error at server: " + st['error']);
						}	
					}catch(e){
						alert("Incorrect response from server: \n" + this.responseText);
					}
				}
				con.setRequestHeader("content-type", "application/x-www-form-urlencoded");
				con.send(vpostdata);
			}
		},
		edit_order: function(vi){
			this.editing_order_id = vi;
			this.$set( this.orders[vi], 'edit', true );
		},
		save_order: function(vi){
			con = new XMLHttpRequest();
			con.open("GET", "?action=edit_order&order_id="+ this.orders[vi]['id'] + "&name=" + encodeURIComponent( this.orders[vi]['name'] )+"&date=" + encodeURIComponent( this.orders[vi]['date'] )+"&address=" + encodeURIComponent( this.orders[vi]['address'] ),true );
			con.onload = function(){
				
				if( this.responseText == "success" ){
					app.$set( app.orders[ app.editing_order_id ], 'edit', false );
					//this.load_states();
				}else{
					alert("There was an error while updating order: \n" + this.responseText );
				}
			}
			con.send();
			
		},
		deleteorder: function(vi){
			vurl = "?action=delete_order&order_id="+ this.orders[vi]['id'];
			axios.get(vurl).then(response=>{
				if( response.status == 200 ){
					if( typeof(response.data) == "object" ){
						if( "status" in response.data ){
							if( response.data['status'] == "success" ){
								this.orders.splice(vi,1);
							}else{
								alert("Error: " + response.data['error']);
							}
						}else{
							alert("Incorrect response");
						}
					}else{
						alert("Incorrect response");
					}
				}else{
					alert("Http response page not found");
				}
			})
		}
		
	}
});

</script>
</body>
</html>