var Points = [];
var StoragesArray = [];
var myMap;
var selectedStorage;

/** Загружает карту, по загрузке выполняет функцию DoOnLoad
*
*/
ymaps.ready(function () {
    myMap = new ymaps.Map("map", {
        center: [53.9022528, 27.561639],
        zoom: 11,
        controls: ["trafficControl", "typeSelector", "fullscreenControl", "zoomControl"]
    });
    DoOnLoad();
});

/** Подготавливает страницу для пользователя: устанавливает ссылки, дату; загружает точки
*
*/
function DoOnLoad()
{
	try {
		function setDateNload(dateToSet, storages, selectedStore) {
			try {
				var _t = new Date(dateToSet * 1000);
				var date_input = _t.getFullYear() + "-";
				(_t.getMonth() < 10) && (date_input += "0");
				date_input += (_t.getMonth() + 1) + "-";
				(_t.getDate() < 10) && (date_input += "0");
				date_input += _t.getDate();
				document.querySelector('#store-date > input[type="date"]').value = date_input;
				var storageContainer = document.querySelector("#store-choose > select");
				storages.storages.forEach(function (item, i) {
					StoragesArray.push(item);
					var elem = document.createElement("option");
					elem.setAttribute("value", i);
					elem.innerHTML = item.name;
					storageContainer.appendChild(elem);
				});
				(!selectedStore) && (selectedStore = 0);
				storageContainer.selectedIndex = parseInt(selectedStore);
				selectedStorage = StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id;
				delVar("pending");
				delVar("onDate");
				delVar("storage");
				loadPoints();
				reCalc();
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		document.getElementsByTagName("html")[0].style.transition = "0.5s";
		document.getElementsByTagName("html")[0].style.opacity = "1";
		/* Меню */
		var top_tabs = document.getElementById("tab-bar").children;
		top_tabs[0].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () {
				var date = new Date(document.querySelector('#store-date > input[type="date"]').value);
				setVar("onDate", date.getTime() / 1000);
				setVar("storage", document.querySelector('#store-choose > select').selectedIndex);
				window.location.href = "/";
			}, 600);
		}
		top_tabs[1].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () {
				var date = new Date(document.querySelector('#store-date > input[type="date"]').value);
				setVar("onDate", date.getTime() / 1000);
				setVar("storage", document.querySelector('#store-choose > select').selectedIndex);
				window.location.href = "/Route";
			}, 600);
		}
		top_tabs[3].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () { window.location.href = "/API/user_exit"; }, 600);
		}
		top_tabs[2].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () { window.location.href = "/Settings"; }, 600);
		}
		reCalc();
		document.querySelector('#footer > div.add-floating-button').onclick = PreparePoint;
		document.querySelector('#store-date > input[type="date"]').onchange = loadPoints;
		document.querySelector('#store-choose > select').onchange = function () { selectedStorage = StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id; loadPoints(); };
		document.querySelector('#calculate').onclick = calcRoutes;
		
		
		
		var storageReq = new Request("/Storages/get_storages");
		storageReq.callback = function (storageResponse) {
			try {
				var storageAnswer = JSON.parse(storageResponse);
				if (storageAnswer.data.state === "success") {
					if (storageAnswer.data.storages.length == 0) {
						new Dialog("Отстутствуют склады. Вам необходимо добавить склад в настройках.",[{text: "Перейти в настройки", func: function () {
							document.getElementsByTagName("html")[0].style.opacity = "";
							setTimeout(function () {
								window.location = "/Settings";
							}, 500);
						}}]);
					}
					else {
						if (getVar("onDate"))
							setDateNload(getVar("onDate"), storageAnswer.data, getVar("storage"));
						else {
							var req = new Request("/API/get_time");
							req.callback = function (Response) {
								try {
									var answer = JSON.parse(Response);
									if (answer.data) {
										setDateNload(answer.data, storageAnswer.data, getVar("storage"));
									}
									else
										new Dialog("Ошибка ответа сервера");
								}
								catch (ex) { console.error(ex); new Dialog(ex.message); }
							}
							req.do();
						}
					}
				}
				else
					new Dialog(storageAnswer.data.message);
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		storageReq.do();
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}

/** Реализует точки, как объекты класса
* @class
*/
function Point()
{
	try {
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
		this.map_id = Points.length + 1;
		this.uniq = null;
		this.coordinates = {
			longitude: null,
			latitude: null
		}
		this.mapObj = null;
		this.note = null;
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}
Point.prototype = {
	/** Сворачивает/разворачивает блок точки
	*
	*/
	toggle: function () {
		try {
			this.Object.style.height = (this.isOpen) ? "" : "232px";
			this.Object.getElementsByClassName("button-toggle")[0].style.transform = (this.isOpen) ? "" : "rotate(90deg)";
			this.isOpen = !this.isOpen;
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	/** Создает и добавляет блок точки, ее метку на карте
	*
	*/
	create: function () {
		try {
			if (this.isAdded)
				throw new Error("Ошибка создания. Элемент уже присутствует.");
			var _this = this;
			this.isAdded = 1;
			var _order = document.createElement("div");
			_order.setAttribute("class", "order");
			_order.setAttribute("data-mapid", this.map_id);
			var _t1 = document.createElement("div");
			var _t2 = document.createElement("div");
			
			_t1.setAttribute("class", "order-header");
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
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class","button-info");
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("input");
			_t3.setAttribute("type", "tel");
			_t3.setAttribute("placeholder", "телефон");
			_t3.setAttribute("disabled", "1");
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("input");
			_t3.setAttribute("type", "text");
			_t3.setAttribute("placeholder", "примечание");
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
			this.mapObj.events.add("click", function () { _this.blink(); });
			myMap.geoObjects.add(this.mapObj);
			setTimeout(function () { _this.Object.style.marginLeft = ""; }, 550);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	/** Открывает диалог редактирования информации точки
	*
	*/
    editDialog: function () {
        try {
			if (!getVar("pending")) {
				var mintime = "18:00";
				var maxtime = "22:00";
				setVar("pending", true);
				var _this = this;
				var _temp = document.createElement("div");
				_temp.setAttribute("id", "edit-order");
				_temp.innerHTML = '<div id="edit-order-window"><p id="order-number"></p><input type="text" placeholder="Улица, проезд, проспект" id="edit-order-address-street"><input type="text" placeholder="дом" id="edit-order-address-house"><input type="text" placeholder="под." id="edit-order-address-entry"><input type="text" placeholder="этаж" id="edit-order-address-floor"><input type="text" placeholder="кв." id="edit-order-address-flat"><div id="edit-order-time"><p>Желаемое время доставки:</p><p style="width: 50px; text-align: center;">с</p><input type="time" min="' + mintime + '" max="' + maxtime + '" step="900" class="time-from"><p style="width: 60px; text-align: center;">по</p><input type="time" class="time-to" min="' + mintime + '" max="' + maxtime + '" step="900"></div><div id="edit-order-phone"><p style=" width: 130px; ">Дополнительно: </p><input placeholder="телефон" type="tel" style=" width: 170px; "><input placeholder="примечание" type="text" style=" width: 380px; margin-left: 20px; margin-right: 15px;"><div class="cashless" id="edit-order-cashless"><p></p><p></p></div></div><div id="edit-order-items"></div><div class="add-floating-button"></div><div class="button-save"></div><div class="button-cancel"></div></div>';
				_temp.getElementsByTagName("p")[0].innerHTML = this.uniq;
				(this.address.street) && (_temp.getElementsByTagName("input")[0].value = this.address.street);
				(this.address.house)  && (_temp.getElementsByTagName("input")[1].value = this.address.house);
				(this.address.entry)  && (_temp.getElementsByTagName("input")[2].value = this.address.entry);
				(this.address.floor)  && (_temp.getElementsByTagName("input")[3].value = this.address.floor);
				(this.address.flat)   && (_temp.getElementsByTagName("input")[4].value = this.address.flat);

				(this.time.start) && (_temp.getElementsByClassName("time-from")[0].value = this.time.start);
				(this.time.end)   && (_temp.getElementsByClassName("time-to")[0].value = this.time.end);

				(this.phone) && (_temp.getElementsByTagName("input")[7].value = this.phone);
				(this.note) && (_temp.getElementsByTagName("input")[8].value = this.note);

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
							try {
								var body = { order_id: item.order_id }
								var req = new Request("/Orders/delete_order", body);
								var __this = this;
								var _onclick = this.onclick;
								this.onclick = void(0);
								req.callback = function (Response) {
									try {
										var answer = JSON.parse(Response);
										if (answer.data.state == "success") {
											__this.parentNode.remove();
											_this.items.splice(_this.items.indexOf(item),1);
										}
										else {
											new Dialog(answer.data.message);
											__this.onclick = _onclick;
										}
									}
									catch (ex) { console.error(ex); new Dialog(ex.message); }
								}
								req.do();
							}
							catch (ex) { console.error(ex); new Dialog(ex.message); }
						}
						_temp.getElementsByTagName("div")[4].appendChild(el);
					}
				_temp.getElementsByClassName("add-floating-button")[0].onclick = function () {
					try {
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
											order_id: answer.data.order_id,
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
											var _onclick = this.onclick;
											this.onclick = void(0);
											req.callback = function (Response) {
												try {
													var answer = JSON.parse(Response);
													if (answer.data.state == "success") {
														__this.parentNode.remove();
														_this.items.splice(_this.items.indexOf(item),1);
													}
													else {
														new Dialog(answer.data.message);
														__this.onclick = _onclick;
													}
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
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				_temp.getElementsByClassName("cashless")[0].onclick = function () {
					try {
						var elem = document.querySelector("#edit-order-cashless");
						if (elem != void(0))
							{
								if (elem.getAttribute("status") != void(0)) 
									elem.removeAttribute("status");
								else
									elem.setAttribute("status", "cashless");
							}
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				_temp.getElementsByClassName("button-cancel")[0].onclick = function () {
					try {
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
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				_temp.getElementsByClassName("button-save")[0].onclick = function () { _this.save(); }
				_temp.style.opacity = "0";
				document.body.appendChild(_temp);
				delVar("pending");
				document.querySelector("#edit-order-time > input.time-from").value = !!(_this.time.start) ? _this.time.start.substr(0,5) : mintime;
				document.querySelector("#edit-order-time > input.time-to").value = !!(_this.time.end) ? _this.time.end.substr(0,5) : maxtime;
				setTimeout(function () { _temp.style.opacity = ""; }, 10);
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
    },
	
	/** Сохраняет информацию точки на сервер
	*
	*/
	save: function () {
		try {
            var _this = this;
            if (!getVar("pending")) {
				setVar("pending", true);
                loader = new PreLoader(document.getElementById("edit-order-window"));
                loader.inprogress = function () {
                    try {
                        var body = {};
                        var _body = body;
						var errmsg;
						var _send = true;
						
						function rep_err(msg) {
							(!errmsg) && (errmsg = msg);
							_send = false;
							return null;
						}
						
                        var blocks = document.getElementsByClassName("item");
                        for (var i = 0; i < blocks.length; i++) {
                            _this.items[i].description = blocks[i].children[0].value;
                            _this.items[i].cost = parseFloat(blocks[i].children[1].value.replace(new RegExp(",","g"),"."));
                            _this.items[i].point_id = parseInt(blocks[i].getAttribute("data-pid"));
                        }
                        body.address = {};
                        body.address.street = (document.getElementById("edit-order-address-street").value) ? document.getElementById("edit-order-address-street").value : rep_err("Не указана улица");
                        body.address.house = (document.getElementById("edit-order-address-house").value) ? document.getElementById("edit-order-address-house").value : rep_err("Не указан дом");
                        body.address.entry = (document.getElementById("edit-order-address-entry").value) ? document.getElementById("edit-order-address-entry").value : null;
                        body.address.floor = (document.getElementById("edit-order-address-floor").value) ? parseInt(document.getElementById("edit-order-address-floor").value) : null;
                        body.address.flat = (document.getElementById("edit-order-address-flat").value) ? parseInt(document.getElementById("edit-order-address-flat").value) : null;

                        body.point_id = parseInt(_this.id);

                        body.time = {};
                        var _tdate = new Date();
                        var _tel = (!!document.querySelector("#edit-order-time > input.time-from").value) ? document.querySelector("#edit-order-time > input.time-from").value : rep_err("Некорректное время начала");
						if (_tel != null) {
							_tdate.setHours(_tel[0]+_tel[1], _tel[3]+_tel[4], 0);
							body.time.start = (_tdate.getHours() > 9) ? _tdate.getHours() : "0" + _tdate.getHours();
							body.time.start += ":";
							body.time.start += (_tdate.getMinutes() > 9) ? _tdate.getMinutes() : "0" + _tdate.getMinutes();
							body.time.start += ":";
							body.time.start += (_tdate.getSeconds() > 9) ? _tdate.getSeconds() : "0" + _tdate.getSeconds();
						}
                        var _tdate = new Date();
						var _tel = (!!document.querySelector("#edit-order-time > input.time-to").value) ? document.querySelector("#edit-order-time > input.time-to").value : rep_err("Некорректное время окончания");
						if (_tel != null) {
							_tdate.setHours(_tel[0]+_tel[1], _tel[3]+_tel[4], 0);
							body.time.end = (_tdate.getHours() > 9) ? _tdate.getHours() : "0" + _tdate.getHours();
							body.time.end += ":";
							body.time.end += (_tdate.getMinutes() > 9) ? _tdate.getMinutes() : "0" + _tdate.getMinutes();
							body.time.end += ":";
							body.time.end += (_tdate.getSeconds() > 9) ? _tdate.getSeconds() : "0" + _tdate.getSeconds();
						}
						(body.time.start > body.time.end) && (rep_err("Неверное время"));
						
                        body.phone = (document.querySelector('#edit-order-phone > input[type="tel"]').value) ? parseInt(document.querySelector('#edit-order-phone > input[type="tel"]').value) : null;
                        var _t = new Date(document.querySelector('#store-date > input[type="date"]').value);
                        body.delivery_date = parseInt(_t.getTime() / 1000);
                        body.note = (document.querySelector('#edit-order-phone > input[type="text"]').value) ? document.querySelector('#edit-order-phone > input[type="text"]').value : "Примечания отсутствуют";
                        body.cashless = document.querySelector('#edit-order-cashless').getAttribute("status") === "cashless";
						body.storage_id = selectedStorage;
                        var req = new Request("/Points/fill_point", body);
                        req.callback = function (Response) {
                            try {
                                var answer = JSON.parse(Response);
                                if (answer.data.state == "success")
                                    {
                                        function ok_end() {
                                            _this.load();
                                            loader.purge();
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
                                                    else { new Dialog(answer.data.message); }
                                                }
                                                catch (ex) { console.error(ex); new Dialog(ex.message); loader.purge(); }
                                            }
                                            req1.do();
                                        }
                                    }
                                else { new Dialog(answer.data.message); }
                            }
                            catch (ex) { console.error(ex); new Dialog(ex.message); loader.purge(); }
                        }
                        if (_send)
							req.do();
						else { new Dialog(errmsg); }
                    }
                    catch (ex) { console.error(ex); new Dialog(ex.message); }
                }
                loader.create();
            }
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	/** Удаляет точку как локально, так и с сервера
	*
	*/
    delete: function () {
        try {
			if (!getVar("pending")) {
				function PointDelete(_this) {
					try {
						var t = {
							point_id: _this.id,
							storage_id: selectedStorage
						};
						var req = new Request("/Points/delete_point", t);
						req.callback = function (Response) {
							try {
								var answer = JSON.parse(Response);
								if (answer.data.state == "success")
									{
										delVar("pending");
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
				setVar("pending", true);
				var _this = this;
				if (this.isOpen) {
					this.toggle();
					setTimeout(PointDelete, 500, _this);
				}
				else
					PointDelete(_this);
			}
        }
        catch (ex) { console.error(ex); new Dialog(ex.message); }
    },
	
	/** Загружает информацию о точке с сервера
	*
	*/
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
										_this.note = answer.data.point_info.note;
										
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
	
	/** Заполняет блок точки информацией, перемещает метку на карте
	*
	*/
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
			(this.note) && (this.Object.getElementsByTagName("input")[8].value = this.note);
			
			var block = this.Object.getElementsByClassName("order-items")[0].children[0];
			block.innerHTML = this.items.length;

			(this.items.length == 1) && (block.innerHTML += " товар на сумму ");
			((this.items.length > 1) && (this.items.length < 5)) && (block.innerHTML += " товара на сумму ");
			((this.items.length == 0) || (this.items.length > 4)) && (block.innerHTML += " товаров на сумму ");
			
			(this.totalcost != void(0)) && (block.innerHTML += this.totalcost);
			(this.mapObj != null) && (this.mapObj.geometry.setCoordinates([this.coordinates.latitude, this.coordinates.longitude]));
			reCalc();
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	/** Удаляет блок точки и метку на карте
	*
	*/
	deleteLocal: function () {
		try {
			if (this.isAdded) {
				var _this = this;
				this.Object.style.marginLeft = "-600px";
				setTimeout(function () {
					try {
						_this.Object.style.height = "0px";
						document.querySelector('#orderlist').children[0] == _this.Object ? _this.Object.style.marginLeft = "-600px" : _this.Object.style.margin = "-20px 0 0 -600px";
						_this.Object.style.padding = "0";
						myMap.geoObjects.remove(_this.mapObj);
						setTimeout(function () {
							try {
								_this.Object.remove();
								Points.splice(Points.indexOf(_this),1);
								reCalc();
							}
							catch (ex) { console.error(ex); new Dialog(ex.message); }
						}, 550);
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}, 500);
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	blink: function () {
		try {
			if (this.isAdded) {
				var _this = this;
				_this.Object.scrollIntoView(false);
				_this.Object.style.backgroundColor = "#03a9f4";
				setTimeout(function () {
					try {
						_this.Object.style.backgroundColor = "";
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}, 340);
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
}

/** Создает запрос на резервирование точки, создает ее локально и вызывает ее метод editDialog
*
*/
function PreparePoint()
{
	try {
		if (!getVar("pending"))
			{
				setVar("pending", true);
				var body = { storage_id: selectedStorage }
				var query = new Request("/Points/add_empty_point", body);
				query.callback = function (Response) {
					try {
						var ans = JSON.parse(Response);
						delVar("pending");
						if (ans.data.state == "success")
							{
								var t = new Point();
								Points.push(t);
								t.id = ans.data.point_id;
								t.uniq = ans.data.identifier_order;
								t.editDialog();
							}
						else
							new Dialog(ans.data.message);
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				query.do();
			}
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}

/** Загружает точки на дату и склад, указанные в header
*
*/
function loadPoints()
{
	try {
		var dateBlock = document.querySelector('#store-date > input[type="date"]');
		if (!getVar("pending")) {
			dateBlock.setAttribute("disabled", "1");
			var delay = 10;
			for (var i = 0; i < Points.length; i++) {
				Points[i].deleteLocal();
			}
			var removeEnded = setInterval(function () {
				try {
					if (Points.length == 0) {
						clearInterval(removeEnded);
						setVar("pending", true);
						var day = new Date(dateBlock.value);
						var body = {
							delivery_date: day.getTime() / 1000,
							storage_id: StoragesArray[parseInt(document.querySelector("#store-choose > select").value)].id
						}
						var req = new Request("Points/get_points_by_date", body);
						req.callback = function (Response) {
							try {
								var answer = JSON.parse(Response);
								if (answer.data.state == "success")
									{
										delVar("pending");
										if (answer.data.points_id)
											for (var i = 0; i < answer.data.points_id.length; i++)
												{
													var t = new Point();
													t.id = answer.data.points_id[i];
													t.load();
													Points.push(t);
												}
										var dateChangeAllow = setInterval(function () {
											var allow = true;
											for (var i = 0; i < Points.length; i++)
												(!Points[i].isAdded) && (allow = false);
											if (allow) {
												dateBlock.removeAttribute("disabled");
												clearInterval(dateChangeAllow);
											}
										}, 200);
									}
								else { new Dialog(answer.data.message); dateBlock.removeAttribute("disabled"); }
							}
							catch (ex) { console.error(ex); dateBlock.removeAttribute("disabled"); new Dialog(ex.message); }
						}
						req.do();
					}
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); dateBlock.removeAttribute("disabled"); }
			}, 200);
		}
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); dateBlock.removeAttribute("disabled"); }
}

/** Рассчитывает блок Итого: расчет количества точек и общей стоимости
*
*/
function reCalc()
{
	try {
		var totalcount = 0;
		var totalcost = 0;
		for (var i = 0; i < Points.length; i++) {
			totalcount++;
			for (var j=0; j<Points[i].items.length; j++)
				totalcost += Points[i].items[j].cost;
		}
		document.querySelector("#right-footer > p:nth-child(1)").innerHTML = totalcount;
		(totalcount == 1) && (document.querySelector("#right-footer > p:nth-child(1)").innerHTML += " точка");
		((totalcount > 1) && (totalcount < 5)) && (document.querySelector("#right-footer > p:nth-child(1)").innerHTML += " точки");
		((totalcount == 0) || (totalcount > 4)) && (document.querySelector("#right-footer > p:nth-child(1)").innerHTML += " точек");
		document.querySelector("#right-footer > p:nth-child(2)").innerHTML = "общей суммой " + totalcost.toFixed(3);
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}

/** Выполняет проверку маршрутизации. Если обнаруживает рассчитанный маршрут, сообщает об этом; в противном случае строит матрицу времени [0..n], содержащую время в пути из точки a в точку b, либо null, в случае отсутствия пути, а так же массив краткой информации о точках
*
*/
function calcRoutes() {
	try {
		if (getVar("pending") == false) {
			setVar("pending", true);
			var date = new Date(document.querySelector('#store-date > input[type="date"]').value);
			var body = {
				date: date.getTime() / 1000,
				storage_id: StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id
			}
			loader = new PreLoader();
			loader.inprogress = function () {
				try {
				var req = new Request("/Route/get_routes", body);
				req.callback = function (Response) {
					try {
						var answer = JSON.parse(Response);
						if (answer.data.state == "success")
							{
								if (answer.data.routes.length == 0)
									{
										if (Points.length < 1)
											throw new Error("Нет точек для построения маршрута");
										var timeMatrix = [];
										var counter = Points.length * (Points.length - 1);
										for (var i = 0; i < Points.length+1; i++) {
											timeMatrix[i] = [];
											for (var j = 0; j < Points.length+1; j++)
												timeMatrix[i][j] = null;
										}
										var lat = StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].Latitude;
										var lon = StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].Longitude;
										if (Points.length > 1) {
											Points.forEach(function (item, i) {
												Points.forEach(function (item, j) {
													if (i != j)
														ymaps.route([[Points[i].coordinates.latitude, Points[i].coordinates.longitude], [Points[j].coordinates.latitude, Points[j].coordinates.longitude]], {avoidTrafficJams: true}).then(
															function (route) {
																//myMap.geoObjects.add(route);
																counter--;
																timeMatrix[i+1][j+1] = route.properties.getAll().RouterRouteMetaData.jamsTime;
																if (counter == 0) {
																	var counter1 = Points.length;
																	var infoArray = [];
																	Points.forEach(function (item, u) {
																		ymaps.route([[lat,lon], [Points[u].coordinates.latitude, Points[u].coordinates.longitude]], {avoidTrafficJams: true}).then(
																			function (route) {
																				counter1--;
																				timeMatrix[0][u+1] = route.properties.getAll().RouterRouteMetaData.jamsTime;
																				infoArray[u] = {
																					point_id: Points[u].id,
																					time_start: (parseInt(Points[u].time.start[0] + Points[u].time.start[1]) * 60 * 60) + (parseInt(Points[u].time.start[3] + Points[u].time.start[4]) * 60) + (parseInt(Points[u].time.start[6] + Points[u].time.start[7])),
																					time_end: (parseInt(Points[u].time.end[0] + Points[u].time.end[1]) * 60 * 60) + (parseInt(Points[u].time.end[3] + Points[u].time.end[4]) * 60) + (parseInt(Points[u].time.end[6] + Points[u].time.end[7]))
																				}
																				//myMap.geoObjects.add(route);
																				if (counter1 == 0) {
																					/*console.table(infoArray);
																					console.table(timeMatrix);*/
																					var bodyCalc = {
																						points: infoArray,
																						timeMatrix: timeMatrix,
																						date: date.getTime() / 1000,
																						storage_id: StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id
																					}
																					var reqCalc = new Request("/Route/calculation", bodyCalc);
																					reqCalc.callback = function (Response) {
																						try {
																							var answer = JSON.parse(Response);
																							if (answer.data.state == "success")
																								{
																									new Dialog("Маршрут успешно построен", [{text: "Посмотреть маршрут", func: function () { setVar("onDate", bodyCalc.date); setVar("storage", document.querySelector('#store-choose > select').selectedIndex); window.location.href = "/Route"; }}]);
																								}
																							else { new Dialog(answer.data.message); }
																						}
																						catch (ex) { console.error(ex); new Dialog(ex.message); }
																					}
																					reqCalc.do();
																				}
																			},
																			function (error) {
																				new Dialog('Возникла ошибка: ' + error.message);
																			}
																		);
																	});
																}
															},
															function (error) {
																new Dialog('Возникла ошибка: ' + error.message);
															}
														);

												});
											});
										}
										else {
											// FIX IF 1 POINT ONLY
											// TODO: реализовать способом, иным от копипаста
																var counter1 = Points.length;
																var infoArray = [];
																Points.forEach(function (item, u) {
																	ymaps.route([[lat,lon], [Points[u].coordinates.latitude, Points[u].coordinates.longitude]], {avoidTrafficJams: true}).then(
																		function (route) {
																			counter1--;
																			timeMatrix[0][u+1] = route.properties.getAll().RouterRouteMetaData.jamsTime;
																			infoArray[u] = {
																				point_id: Points[u].id,
																				time_start: (parseInt(Points[u].time.start[0] + Points[u].time.start[1]) * 60 * 60) + (parseInt(Points[u].time.start[3] + Points[u].time.start[4]) * 60) + (parseInt(Points[u].time.start[6] + Points[u].time.start[7])),
																				time_end: (parseInt(Points[u].time.end[0] + Points[u].time.end[1]) * 60 * 60) + (parseInt(Points[u].time.end[3] + Points[u].time.end[4]) * 60) + (parseInt(Points[u].time.end[6] + Points[u].time.end[7]))
																			}
																			//myMap.geoObjects.add(route);
																			if (counter1 == 0) {
																				/*console.table(infoArray);
																				console.table(timeMatrix);*/
																				var bodyCalc = {
																					points: infoArray,
																					timeMatrix: timeMatrix,
																					date: date.getTime() / 1000,
																					storage_id: StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id
																				}
																				var reqCalc = new Request("/Route/calculation", bodyCalc);
																				reqCalc.callback = function (Response) {
																					try {
																						var answer = JSON.parse(Response);
																						if (answer.data.state == "success")
																							{
																								new Dialog("Маршрут успешно построен", [{text: "Посмотреть маршрут", func: function () { setVar("onDate", bodyCalc.date); setVar("storage", document.querySelector('#store-choose > select').selectedIndex); window.location.href = "/Route"; }}]);
																							}
																						else { new Dialog(answer.data.message); }
																					}
																					catch (ex) { console.error(ex); new Dialog(ex.message); }
																				}
																				reqCalc.do();
																			}
																		},
																		function (error) {
																			new Dialog('Возникла ошибка: ' + error.message);
																		}
																	);
																});
											
											
										}
									}
								else { new Dialog("Маршрут уже существует", [{text: "Посмотреть маршрут", func: function () { setVar("onDate", body.date); setVar("storage", document.querySelector('#store-choose > select').selectedIndex); window.location.href = "/Route"; }}]); }
							}
						else {	new Dialog(answer.data.message); }
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				req.do();
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}
			loader.create();
		}
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}