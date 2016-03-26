var Points = [];

function DoOnLoad()
{
	document.querySelector('#footer > div.add-floating-button').onclick = PreparePoint;
	delVar("pending");
    ymaps.ready(init);
    var myMap;

    function init(){     
        myMap = new ymaps.Map("map", {
            center: [55.76, 37.64],
            zoom: 7
        });
    }
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
	this.map_id = null;
	this.uniq = null;
	
	this.coordinates = {
		longtitude: null,
		latitude: null
	}
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
			var _t1 = document.createElement("div");
			var _t2 = document.createElement("div");
			
			_t1.setAttribute("class", "order-header");
			var _this = this;
			_t1.onclick = function () { _this.toggle(); }
			_t2.setAttribute("class", "button-toggle");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("p");
			_t2.setAttribute("class", "order-title");
			_t2.innerHTML = this.address.street;
			(!this.address.house) && (_t2.innerHTML += ", д." + this.address.house);
			(!this.address.flat) && (_t2.innerHTML += ", кв. " + this.address.flat);
			
			_t1.appendChild(_t2);
			_order.appendChild(_t1);
			
			var _t1 = document.createElement("div");
			_t1.setAttribute("class", "order-body");
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "Улица...");
			_t2.setAttribute("class", "order-address-street");
			_t2.setAttribute("disabled", "1");
			(this.address.street) && (_t2.value = this.address.street);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "дом");
			_t2.setAttribute("class", "order-address-house");
			_t2.setAttribute("disabled", "1");
			(this.address.house) && (_t2.value = this.address.house);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "корп.");
			_t2.setAttribute("class", "order-address-block");
			_t2.setAttribute("disabled", "1");
			(this.address.block) && (_t2.value = this.address.block);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "под.");
			_t2.setAttribute("class", "order-address-entry");
			_t2.setAttribute("disabled", "1");
			(this.address.entry) && (_t2.value = this.address.entry);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "этаж");
			_t2.setAttribute("class", "order-address-floor");
			_t2.setAttribute("disabled", "1");
			(this.address.floor) && (_t2.value = this.address.floor);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "кв.");
			_t2.setAttribute("class", "order-address-flat");
			_t2.setAttribute("disabled", "1");
			(this.address.flat) && (_t2.value = this.address.flat);
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
			(this.time.start) && (_t3.value = this.time.start);
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
			(this.time.end) && (_t3.value = this.time.end);
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
			(this.phone) && (_t3.value = this.phone);
			_t2.appendChild(_t3);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("div");
			_t2.setAttribute("class", "order-items");
			
			var _t3 = document.createElement("p");
			_t3.innerHTML = this.items.count;
			(this.items.count == 1) && (_t3.innerHTML += " товар на сумму");
			((this.items.count > 1) && (this.items.count < 5)) && (_t3.innerHTML += " товара на сумму");
			(this.items.count > 4) && (_t3.innerHTML += " товаров на сумму");
			_t2.appendChild(_t3);
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("div");
			_t2.setAttribute("class", "order-buttons");
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class", "button-delete");
			_t3.onclick = function () { new Dialog("Удаление заказа еще недоступно"); }
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class", "button-edit");
			_t3.onclick = function () { _this.editDialog(); }
			_t2.appendChild(_t3);
			
			_t1.appendChild(_t2);
			_order.appendChild(_t1);
			
			this.Object = _order;
			this.Object.style.marginLeft = "-600px";
			this.Object.style.height = "0px";
			document.getElementById("orderlist").appendChild(this.Object);
			this.Object.style.height = "";
			setTimeout(function () { _this.Object.style.marginLeft = ""; }, 550);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
    editDialog: function () {
        try {
			var _this = this;
			var _temp = document.createElement("div");
			_temp.setAttribute("id", "edit-order");
			_temp.innerHTML = '<div id="edit-order-window"><p id="order-number"></p><input type="text" placeholder="Улица, проезд, проспект" id="edit-order-address-street"><input type="text" placeholder="дом" id="edit-order-address-house"><input type="text" placeholder="корп." id="edit-order-address-block"><input type="text" placeholder="под." id="edit-order-address-entry"><input type="text" placeholder="этаж" id="edit-order-address-floor"><input type="text" placeholder="кв." id="edit-order-address-flat"><div id="edit-order-time"><p>Желаемое время доставки:</p><p style="width: 50px; text-align: center;">с</p><input type="time" class="time-from"><p style="width: 60px; text-align: center;">по</p><input type="time" class="time-to"></div><div id="edit-order-phone"><p>Телефон: </p><input placeholder="телефон" type="tel"></div><div id="edit-order-items"></div><div class="add-floating-button"></div><div class="button-save"></div><div class="button-cancel"></div></div>';
			_temp.getElementsByTagName("p")[0].innerHTML = this.uniq;
			(this.address.street) && (_temp.getElementsByTagName("input")[0].value = this.address.street);
			(this.address.house)  && (_temp.getElementsByTagName("input")[1].value = this.address.house);
			(this.address.block)  && (_temp.getElementsByTagName("input")[2].value = this.address.block);
			(this.address.entry)  && (_temp.getElementsByTagName("input")[3].value = this.address.entry);
			(this.address.floor)  && (_temp.getElementsByTagName("input")[4].value = this.address.floor);
			(this.address.flat)   && (_temp.getElementsByTagName("input")[5].value = this.address.flat);
			
			(this.time.start) && (_temp.getElementsByClassName("time-from")[0].value = this.time.start);
			(this.time.end)   && (_temp.getElementsByClassName("time-to")[0].value = this.time.end);
			
			(this.phone) && (_temp.getElementsByTagName("input")[8].value = this.phone);
			
			for (var i = 0; i < this.items.length + 1; i++)
				{
					var el = document.createElement("div");
					el.setAttribute("class", "item");
					el.innerHTML = '<input placeholder="описание товара" type="text"><input placeholder="стоимость товара" type="text"><div class="button-delete"></div>';
					el.children[2].onclick = function () { this.parentNode.remove(); }
					if (this.items[i])
						{
							el.children[0].value = this.items[i].description;
							el.children[1].value = this.items[i].cost;
						}
					_temp.getElementsByTagName("div")[3].appendChild(el);
				}
			_temp.getElementsByClassName("add-floating-button")[0].onclick = function () {
					var el = document.createElement("div");
					el.setAttribute("class", "item");
					el.innerHTML = '<input placeholder="описание товара" type="text"><input placeholder="стоимость товара" type="text"><div class="button-delete"></div>';
					el.children[2].onclick = function () { this.parentNode.remove(); }
					document.getElementById("edit-order-items").appendChild(el);
			}
			_temp.getElementsByClassName("button-cancel")[0].onclick = function () { this.parentNode.parentNode.remove(); }
			_temp.getElementsByClassName("button-save")[0].onclick = function () { _this.save(); }
			_temp.style.opacity = 0;
			document.body.appendChild(_temp);
			_temp.style.opacity = "";
        }
        catch (ex) { console.error(ex); new Dialog(ex.message); }
    },
	save: function () {
		try {
			if (!getVar("pending")) {
				var body = {};
				var _this = this;
				var _body = body;
				body.address = {};
				body.address.street = (document.getElementById("edit-order-address-street").value) ? document.getElementById("edit-order-address-street").value : null;
				body.address.house = (document.getElementById("edit-order-address-house").value) ? document.getElementById("edit-order-address-house").value : null;
				body.address.block = (document.getElementById("edit-order-address-block").value) ? document.getElementById("edit-order-address-block").value : null;
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

				var req = new Request("/Points/fill_empty_point", body);
				req.callback = function (Response) {
					try {
						var answer = JSON.parse(Response);
						if (answer.data.state == "success")
							{
								/* TODO Load fn */
								_this.address = _body.address;
								_this.time.start = _body.time.start;
								_this.time.end = _body.time.end;
								_this.phone = _body.phone;
								document.getElementById("edit-order").style.opacity = "0";
								_this.create();
								setTimeout(function () { document.getElementById("edit-order").remove(); delVar("pending"); }, 550);
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