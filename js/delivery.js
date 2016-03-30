var Points = [];
var myMap;
ymaps.ready(function () {
    myMap = new ymaps.Map("map", {
        center: [53.9022528, 27.561639],
        zoom: 11,
        controls: ["trafficControl", "typeSelector", "fullscreenControl", "zoomControl"]
    });
    DoOnLoad();
});
function DoOnLoad()
{
	document.querySelector('#footer > div.add-floating-button').onclick = PreparePoint;
	document.querySelector('#store-date > input[type="date"]').onchange = loadPoints;
	delVar("pending");
	loadPoints();
}

function Point()
{
	this.address = {
		street: null,
		house: null,
		block: null,
		entry: null,
		floor: null,
		flat: null
	}
	
	this.time = {
		start: null,
		end: null
	}
	
	this.phone = null;
	this.items = [];
	
	this.totalcost = null;
	this.isOpen = false;
	
	this.Object = null;
	this.id = null;
	this.isAdded = false;
	this.map_id = (Points.length == 0) ? 1 : (Points[Points.length-1].map_id + 1);
	this.uniq = null;
	
	this.coordinates = {
		longitude: null,
		latitude: null
	}
    this.mapObj = null;
}
Point.prototype = {
	toggle: function () {
		try {
			this.Object.style.height = (this.isOpen) ? "" : "232px";
			this.Object.getElementsByClassName("button-toggle")[0].style.transform = (this.isOpen) ? "" : "rotate(90deg)";
			this.isOpen = !this.isOpen;
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	create: function () {
		try {
			if (this.isAdded)
				throw new Error("Ошибка создания. Элемент уже присутствует.");
			this.isAdded = 1;
			var _order = document.createElement("div");
			_order.setAttribute("class", "order");
			_order.setAttribute("data-mapid", this.map_id);
			var _t1 = document.createElement("div");
			var _t2 = document.createElement("div");
			
			_t1.setAttribute("class", "order-header");
			var _this = this;
			_t1.onclick = function () { _this.toggle(); }
			_t2.setAttribute("class", "button-toggle");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("p");
			_t2.setAttribute("class", "order-title");
			
			_t1.appendChild(_t2);
			_order.appendChild(_t1);
			
			var _t1 = document.createElement("div");
			_t1.setAttribute("class", "order-body");
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "Улица...");
			_t2.setAttribute("class", "order-address-street");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "дом");
			_t2.setAttribute("class", "order-address-house");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
		
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "под.");
			_t2.setAttribute("class", "order-address-entry");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "этаж");
			_t2.setAttribute("class", "order-address-floor");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "кв.");
			_t2.setAttribute("class", "order-address-flat");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("div");
			_t2.setAttribute("class", "order-time");
			
			var _t3 = document.createElement("p");
			_t3.innerHTML = "Желаемое время доставки:";
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("p");
			_t3.innerHTML = "с";
			_t3.style.width = "25px";
			_t3.style.textAlign = "center";
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("input");
			_t3.setAttribute("class", "time-from");
			_t3.setAttribute("type", "time");
			_t3.setAttribute("disabled", "1");
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("p");
			_t3.innerHTML = "по";
			_t3.style.width = "35px";
			_t3.style.textAlign = "center";
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("input");
			_t3.setAttribute("class", "time-to");
			_t3.setAttribute("type", "time");
			_t3.setAttribute("disabled", "1");
			_t2.appendChild(_t3);
			
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("div");
			_t2.setAttribute("class", "order-phone");
			
			var _t3 = document.createElement("p");
			_t3.innerHTML = "Телефон:";
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("input");
			_t3.setAttribute("type", "tel");
			_t3.setAttribute("placeholder", "телефон");
			_t3.setAttribute("disabled", "1");
			_t2.appendChild(_t3);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("div");
			_t2.setAttribute("class", "order-items");
			
			var _t3 = document.createElement("p");
			_t2.appendChild(_t3);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("div");
			_t2.setAttribute("class", "order-buttons");
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class", "button-delete");
			_t3.onclick = function () { _this.delete(); }
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class", "button-edit");
			_t3.onclick = function () { _this.editDialog(); }
			_t2.appendChild(_t3);
			
			_t1.appendChild(_t2);
			_order.appendChild(_t1);
			
			this.Object = _order;
			this.fill();
			this.Object.style.marginLeft = "-600px";
			this.Object.style.height = "0px";
			var blocks = document.getElementById("orderlist").children;
			var _id = -1;
			var min_distance = -1;
			for (var i = 0; i < blocks.length; i++) {
				var _t = parseInt(blocks[i].getAttribute("data-mapid"));
				if ( _t > this.map_id)
					((min_distance == -1) || (min_distance > Math.abs(this.map_id - _t))) && ((min_distance = Math.abs(this.map_id - _t)) && (_id = i));
			}
			(_id == -1) ? document.getElementById("orderlist").appendChild(this.Object) : document.getElementById("orderlist").insertBefore(this.Object, blocks[_id]);
			this.Object.style.height = "";
			this.mapObj = new ymaps.GeoObject({
					geometry: {
						type: "Point",
						coordinates: [this.coordinates.latitude, this.coordinates.longitude]
					},
					properties: {
								iconContent: this.map_id,
								balloonContent: this.address.street + ", " + this.address.house
					}
				}, { preset: 'islands', iconColor: '#1faee9' });
			myMap.geoObjects.add(this.mapObj);
			setTimeout(function () { _this.Object.style.marginLeft = ""; }, 550);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
    editDialog: function () {
        try {
			var _this = this;
			var _temp = document.createElement("div");
			_temp.setAttribute("id", "edit-order");
			_temp.innerHTML = '<div id="edit-order-window"><p id="order-number"></p><input type="text" placeholder="Улица, проезд, проспект" id="edit-order-address-street"><input type="text" placeholder="дом" id="edit-order-address-house"><input type="text" placeholder="под." id="edit-order-address-entry"><input type="text" placeholder="этаж" id="edit-order-address-floor"><input type="text" placeholder="кв." id="edit-order-address-flat"><div id="edit-order-time"><p>Желаемое время доставки:</p><p style="width: 50px; text-align: center;">с</p><input type="time" class="time-from"><p style="width: 60px; text-align: center;">по</p><input type="time" class="time-to"></div><div id="edit-order-phone"><p>Телефон: </p><input placeholder="телефон" type="tel"></div><div id="edit-order-items"></div><div class="add-floating-button"></div><div class="button-save"></div><div class="button-cancel"></div></div>';
			_temp.getElementsByTagName("p")[0].innerHTML = this.uniq;
			(this.address.street) && (_temp.getElementsByTagName("input")[0].value = this.address.street);
			(this.address.house)  && (_temp.getElementsByTagName("input")[1].value = this.address.house);
			(this.address.entry)  && (_temp.getElementsByTagName("input")[2].value = this.address.entry);
			(this.address.floor)  && (_temp.getElementsByTagName("input")[3].value = this.address.floor);
			(this.address.flat)   && (_temp.getElementsByTagName("input")[4].value = this.address.flat);
			
			(this.time.start) && (_temp.getElementsByClassName("time-from")[0].value = this.time.start);
			(this.time.end)   && (_temp.getElementsByClassName("time-to")[0].value = this.time.end);
			
			(this.phone) && (_temp.getElementsByTagName("input")[7].value = this.phone);
			
			for (var i = 0; i < this.items.length; i++)
				{
					var el = document.createElement("div");
					el.setAttribute("class", "item");
					el.setAttribute("data-pid", this.items[i].order_id);
					el.innerHTML = '<input placeholder="описание товара" type="text"><input placeholder="стоимость товара" type="text"><div class="button-delete"></div>';
					el.children[0].value = this.items[i].description;
					el.children[1].value = this.items[i].cost;
					var item = this.items[i];
					el.children[2].onclick = function () {
						var body = { order_id: item.order_id }
						var req = new Request("/Orders/delete_order", body);
						var __this = this;
						req.callback = function (Response) {
							try {
								var answer = JSON.parse(Response);
								if (answer.data.state == "success") {
									__this.parentNode.remove();
									_this.items.splice(_this.items.indexOf(item),1);
								}
								else
									new Dialog(answer.data.message);
							}
							catch (ex) { console.error(ex); new Dialog(ex.message); }
						}
						req.do();
					}
					_temp.getElementsByTagName("div")[3].appendChild(el);
				}
			_temp.getElementsByClassName("add-floating-button")[0].onclick = function () {
				var body = {
					point_id: _this.id,
					description: "",
					cost: 0
				}
				var __this = this;
				var req = new Request("/Orders/add_order", body);
				req.callback = function (Response) {
					try {
						var answer = JSON.parse(Response);
						if (answer.data.state == "success") {
							var item = {/* TODO считать из ответа сервера */
								point_id: answer.data.point_id,
								description: "",
								cost: 0
							}
							_this.items.push(item);
							var el = document.createElement("div");
							el.setAttribute("class", "item");
							el.innerHTML = '<input placeholder="описание товара" type="text"><input placeholder="стоимость товара" type="text"><div class="button-delete"></div>';
							el.children[2].onclick = function () {
								var body = { order_id: item.order_id }
								var req = new Request("/Orders/delete_order", body);
								var __this = this;
								req.callback = function (Response) {
									try {
										var answer = JSON.parse(Response);
										if (answer.data.state == "success") {
											__this.parentNode.remove();
											_this.items.splice(_this.items.indexOf(item),1);
										}
										else
											new Dialog(answer.data.message);
									}
									catch (ex) { console.error(ex); new Dialog(ex.message); }
								}
								req.do();
							}
							document.getElementById("edit-order-items").appendChild(el);
						}
						else
							new Dialog(answer.data.message);
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				req.do();
			}
			_temp.getElementsByClassName("button-cancel")[0].onclick = function () {
                if (_this.isAdded)
                    {
						_this.load();
                        this.parentNode.parentNode.style.opacity = "0";
                        var _t = this;
                        setTimeout(function () { _t.parentNode.parentNode.remove(); }, 550);
                    }
                else
                    _this.delete();
            }
			_temp.getElementsByClassName("button-save")[0].onclick = function () { _this.save(); }
			_temp.style.opacity = "0";
			document.body.appendChild(_temp);
			setTimeout(function () { _temp.style.opacity = ""; }, 10);
        }
        catch (ex) { console.error(ex); new Dialog(ex.message); }
    },
	save: function () {
		try {
			if (!getVar("pending")) {
				var body = {};
				var _this = this;
				var _body = body;
				var blocks = document.getElementsByClassName("item");
				for (var i = 0; i < blocks.length; i++) {
					this.items[i].description = blocks[i].children[0].value;
					this.items[i].cost = parseFloat(blocks[i].children[1].value.replace(new RegExp(",","g"),"."));
					this.items[i].point_id = parseInt(blocks[i].getAttribute("data-pid"));
				}
				body.address = {};
				body.address.street = (document.getElementById("edit-order-address-street").value) ? document.getElementById("edit-order-address-street").value : null;
				body.address.house = (document.getElementById("edit-order-address-house").value) ? document.getElementById("edit-order-address-house").value : null;
				body.address.entry = (document.getElementById("edit-order-address-entry").value) ? document.getElementById("edit-order-address-entry").value : null;
				body.address.floor = (document.getElementById("edit-order-address-floor").value) ? parseInt(document.getElementById("edit-order-address-floor").value) : null;
				body.address.flat = (document.getElementById("edit-order-address-flat").value) ? parseInt(document.getElementById("edit-order-address-flat").value) : null;

				body.point_id = parseInt(this.id);

				body.time = {};
				body.time.start = document.querySelector("#edit-order-time > input.time-from").value + ":00";
				body.time.end = document.querySelector("#edit-order-time > input.time-to").value + ":00";

				body.phone = (document.querySelector('#edit-order-phone > input[type="tel"]').value) ? parseInt(document.querySelector('#edit-order-phone > input[type="tel"]').value) : null;
				var _t = new Date(document.querySelector('#store-date > input[type="date"]').value);
				body.delivery_date = parseInt(_t.getTime() / 1000);
				body.note = "TODO"; /* TODO: примечание */
				var req = new Request("/Points/fill_point", body);
				req.callback = function (Response) {
					try {
						var answer = JSON.parse(Response);
						if (answer.data.state == "success")
							{
                                function ok_end() {
                                    _this.load();
                                    document.getElementById("edit-order").style.opacity = "0";
                                    setTimeout(function () { document.getElementById("edit-order").remove(); delVar("pending"); }, 550);
                                }
								var counter = _this.items.length;
                                (_this.items.length == 0) && (ok_end());
								for (var i = 0; i< _this.items.length; i++) {
									var req1 = new Request("/Orders/update_order", _this.items[i]);
									req1.callback = function (Response) {
										try {
											var answer = JSON.parse(Response);
											if (answer.data.state == "success") {
												counter--;
												if (counter == 0)
                                                    ok_end();
											}
											else
												new Dialog(answer.data.message);
										}
										catch (ex) { console.error(ex); new Dialog(ex.message); }
									}
									req1.do();
								}
							}
						else
							new Dialog(answer.data.message);
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				req.do();
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
    delete: function () {
        try {
			function PointDelete(_this) {
				try {
					var t = {};
					t.point_id = _this.id;
					var req = new Request("/Points/delete_point", t);
					req.callback = function (Response) {
						try {
							var answer = JSON.parse(Response);
							if (answer.data.state == "success")
								{
									var block = document.getElementById("edit-order");
									if (block) {
										block.style.opacity = "0";
										setTimeout(function () { block.remove(); }, 550);
									}
									if (_this.isAdded) {
										_this.deleteLocal();
									}
									else {
										Points.splice(_this.point_id,1);
									}
								}
							else
								new Dialog(answer.data.message);
						}
						catch (ex) { console.error(ex); new Dialog(ex.message); }
					}
					req.do();
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}
			var _this = this;
			if (this.isOpen) {
				this.toggle();
				setTimeout(PointDelete, 500, _this);
			}
			else
				PointDelete(_this);
        }
        catch (ex) { console.error(ex); new Dialog(ex.message); }
    },
	load: function () {
		try {
			var body = {};
			var _this = this;
			body.point_id = this.id;
			
			var req = new Request("/Orders/get_list_orders_by_point_id", body);
			req.callback = function (Response) {
				try {
					var answer = JSON.parse(Response);
					if (answer.data.state == "success"){
						_this.items.splice(0, _this.items.length);
						for (var j = 0; j<answer.data.orders.length; j++)
							_this.items.push(answer.data.orders[j]);
						
						var req = new Request("/Points/get_info_about_point", body);
						req.callback = function (Response) {
							try {
								var answer = JSON.parse(Response);
								if (answer.data.state == "success")
									{
										_this.address.street = answer.data.point_info.street;
										_this.address.house = answer.data.point_info.house;
										_this.address.entry = answer.data.point_info.entry;
										_this.address.floor = answer.data.point_info.floor;
										_this.address.flat = answer.data.point_info.flat;

										_this.time.start = answer.data.point_info.time_start;
										_this.time.end = answer.data.point_info.time_end;

										_this.phone = answer.data.point_info.phone_number;

										_this.totalcost = answer.data.point_info.total_cost;

										_this.coordinates.longitude = answer.data.point_info.longitude;
										_this.coordinates.latitude = answer.data.point_info.latitude;
										(_this.isAdded) ? (_this.fill()) : (_this.create());
									}
								else
									new Dialog(answer.data.message);
							}
							catch (ex) { console.error(ex); new Dialog(ex.message); }
						}
						req.do();
					}
					else
						new Dialog(answer.data.message);
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}
			req.do();
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	fill: function () {
		try {
			var block = this.Object.getElementsByClassName("order-title")[0];
			block.innerHTML = "";
			
			(this.map_id) && (block.innerHTML += this.map_id + ". ");
			(this.address.street) && (block.innerHTML += this.address.street);
			(this.address.house) && (block.innerHTML += ", д. " + this.address.house);
			(this.address.flat) && (block.innerHTML += ", кв. " + this.address.flat);
			
			(this.address.street) && (this.Object.getElementsByClassName("order-address-street")[0].value = this.address.street);
			(this.address.house)  &&  (this.Object.getElementsByClassName("order-address-house")[0].value = this.address.house);
			(this.address.entry)  &&  (this.Object.getElementsByClassName("order-address-entry")[0].value = this.address.entry);
			(this.address.floor)  &&  (this.Object.getElementsByClassName("order-address-floor")[0].value = this.address.floor);
			(this.address.flat)   &&   (this.Object.getElementsByClassName("order-address-flat")[0].value = this.address.flat);
			
			(this.time.start) && (this.Object.getElementsByClassName("time-from")[0].value = this.time.start);
			(this.time.end) && (this.Object.getElementsByClassName("time-to")[0].value = this.time.end);
			
			(this.phone) && (this.Object.getElementsByTagName("input")[7].value = this.phone);
			
			var block = this.Object.getElementsByClassName("order-items")[0].children[0];
			block.innerHTML = this.items.length;

			(this.items.length == 1) && (block.innerHTML += " товар на сумму ");
			((this.items.length > 1) && (this.items.length < 5)) && (block.innerHTML += " товара на сумму ");
			((this.items.length == 0) || (this.items.length > 4)) && (block.innerHTML += " товаров на сумму ");
			
			(this.totalcost != void(0)) && (block.innerHTML += this.totalcost);
			(this.mapObj != null) && (this.mapObj.geometry.setCoordinates([this.coordinates.latitude, this.coordinates.longitude]));
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	deleteLocal: function () {
		try {
			if (this.isAdded) {
				var _this = this;
				this.Object.style.marginLeft = "-600px";
				setTimeout(function () {
					_this.Object.style.height = "0px";
					_this.Object.style.margin = "-20px 0 0 -600px";
					_this.Object.style.padding = "0";
					myMap.geoObjects.remove(_this.mapObj);
					setTimeout(function () {
						_this.Object.remove();
						Points.splice(Points.indexOf(_this),1);
					}, 550);
				}, 500);
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
}

function PreparePoint()
{
	try {
		if (getVar("pending") == false)
			{
				setVar("pending", true);
				var query = new Request("/Points/add_empty_point");
				query.callback = function (Response) {
					try {
						var ans = JSON.parse(Response);
						if (ans.data.state == "success")
							{
								var t = new Point();
								Points.push(t);
								t.id = ans.data.point_id;
								t.uniq = ans.data.identifier_order;
								t.editDialog();
								delVar("pending");
							}
						else
							{
								new Dialog(ans.data.message);
							}
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				query.do();
			}
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}

function loadPoints()
{
	try {
		for (var i = 0; i < Points.length; i++)
			Points[i].deleteLocal();
		var day = new Date(document.querySelector('#store-date > input[type="date"]').value);
		var body = { delivery_date: day.getTime() / 1000 }
		var req = new Request("Points/get_points_by_date", body);
		req.callback = function (Response) {
			try {
				var answer = JSON.parse(Response);
				if (answer.data.state == "success")
					{
						if (answer.data.points_id)
							for (var i = 0; i < answer.data.points_id.length; i++)
								{
									var t = new Point();
									t.id = answer.data.points_id[i];
									t.load();
									Points.push(t);
								}
					}
				else
					new Dialog(answer.data.message);
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		req.do();
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}