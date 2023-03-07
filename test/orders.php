<?php

$con = mysqli_connect("localhost", "root", "", "clothes_shop");
if( mysqli_connect_error() ){
	echo "Data Base Error: ". mysqli_connect_error();
	exit;
}

?>



<html>

<head>
	<title>order details</title>
	<script src="vue.min.js"></script>
	<script src="axios.min.js"></script>
	<link rel="stylesheet" href="../database/bootstrap/css/bootstrap.min.css">
</head>
<body>
<div id="app" class="container">
	<div><h4>Customer Details : </h4></div>
	<div style="margin-top: 25px; border: 1.5px solid black; padding: 8px;">		
		Name : <input type="text" >
		Mobile no : <input type="text" >
		Adress : <input type="text">
	</div><br>
	<div style="border: 1.5px solid black; padding: 10px">
	<div><h4>Order Details : </h4></div>
	<div><input type="button" v-on:click="add_item" value="Add Item" ></div>
		<table class="table table-bordered table-sm">
			<tr>
				<th>Serial no</th>
				<th>Product ID </th>
				<th>Product Name </th>
				<th>Quantity </th>
				<th>Unit Price </th>
				<th>Total Price </th>
				<th>Edit/Delete </th>
			</tr>			
		</table>

	</div>
</div>

<script>
	var app = new Vue ({
		el : "#app",
		data : {
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
		//products: [],

		},
		methods : {

			add_item: function(){

			} 

		}










	});
</script>
</body>
</html>