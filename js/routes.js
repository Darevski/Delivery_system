var Routes = [];
var myMap;
var StoragesArray = [];
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

/** Подготавливает страницу для пользователя: устанавливает дату, заружает маршруты
*
*/
function DoOnLoad()
{
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
			loadOnDate();
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
	try {
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
		top_tabs[2].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () { window.location.href = "/API/user_exit"; }, 600);
		}
		document.getElementById("routes-print").onclick = printRoutes;
		document.querySelector('#store-date > input[type="date"]').onchange = loadOnDate;
		document.querySelector('#store-choose > select').onchange = function () { selectedStorage = StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id; loadOnDate(); };
		
		
		var storageReq = new Request("/Storages/get_storages");
		storageReq.callback = function (storageResponse) {
			try {
				var storageAnswer = JSON.parse(storageResponse);
				if (storageAnswer.data.state === "success") {
					
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
				else
					new Dialog(storageAnswer.data.message);
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		storageReq.do();
		
		
		
		
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}

/** Реализует маршруты, как объекты класса
* @class
*/
function Route()
{
	this.paths = [];
	this.block = null;
	this.route = null;
	this.isOpen = false;
	this.isAdded = false;
	this.id = Routes.length+1;
	this.totalTime = null;
}
Route.prototype = {
	/** Сворачивает/разворачивает блок маршрута. Если блок свернут, то разворачивает и вызывает showOnMap
	*
	*/
	toggle: function () {
		try {
			if (this.isAdded) {
				if (!this.isOpen) {
					this.isOpen = true;
					this.block.style.height = ((40*this.paths.length) + 50) + "px";
					this.block.children[0].style.backgroundColor = "#03a9f4";
					this.showOnMap();
				}
				else {
					this.isOpen = false;
					this.block.style.height = "40px";
					this.block.children[0].style.backgroundColor = "";
					myMap.geoObjects.removeAll();
				}
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},

	/** Удаляет все другие маршруты с карты и отображает выбранный
	*
	*/
	showOnMap: function () {
		try {
			myMap.geoObjects.removeAll();
			myMap.geoObjects.add(this.route);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	/** Создает блок маршрута
	*
	*/
	create: function () {
		try {
			if (!this.isAdded) {
				this.isAdded = true;
				Routes.push(this);
				var _this = this;
				var elem = document.createElement("div");
				elem.setAttribute("class", "path");
				elem.innerHTML = '<div class="path-header"><p></p></div><div class="path-body"></div>';
				elem.children[0].onclick = function () { _this.select(); }
				var _temp = elem.children[0].children[0];
				_temp.innerHTML = this.id + ". " + this.paths.length;
				(this.paths.length == 1) && (_temp.innerHTML += " точка, ");
				((this.paths.length > 1) && (this.paths.length < 5)) && (_temp.innerHTML += " точки, ");
				((this.paths.length == 0) || (this.paths.length > 4)) && (_temp.innerHTML += " точек, ");
				var h = Math.floor(this.totalTime / 3600);
				_temp.innerHTML += h + "ч " + Math.floor((this.totalTime - (h*3600)) / 60) + "м";

				var _temp = elem.getElementsByClassName("path-body")[0];

				for (var i = 0; i<this.paths.length; i++) {
					var el = document.createElement("div");
					el.setAttribute("class", "path-segment");

					var _p = document.createElement("p");
					_p.innerHTML = (i+1) + ". " + this.paths[i].address;
					el.appendChild(_p);

					var _p = document.createElement("p");
					var h = Math.floor(this.paths[i].time / 3600);
					var m = Math.floor((this.paths[i].time - (h*3600)) / 60);
					(m < 10) && (m = "0" + m);
					_p.innerHTML += h + ":" + m;
					el.appendChild(_p);

					_temp.appendChild(el);
				}
				this.block = elem;
				this.block.style.height = "0px";
				this.block.style.marginLeft = "-600px";

				var _block = this.block;
				document.getElementById("pathlist").appendChild(this.block);
				setTimeout(function () {
					_block.style.height = "";
					setTimeout(function () {
						_block.style.marginLeft = "";
					}, 500);
				}, 100);
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	/** Выбирает текущий маршрут: вызывает toggle для открытого маршрутов, затем toggle для текущего
	*
	*/
	select: function () {
		try {
			if (!this.isOpen)
				for(var i = 0; i < Routes.length;i++)
					if (Routes[i].isOpen)
						Routes[i].toggle();
			this.toggle();
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	
	/** Удаляет блок маршрута
	*
	*/
	delete: function () {
		try {
			var _this = this;
			if (this.isOpen)
				this.toggle();
			this.block.style.marginLeft = "-600px";
			setTimeout(function () {
				try {
					_this.block.style.height = "0px";
					_this.block.style.marginTop = "0px";
					setTimeout(function () {
						_this.block.remove();
						Routes.splice(Routes.indexOf(_this),1);
						_this = null;
					}, 500);
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}, 300);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
}

/** Загружает маршруты на дату
*
*/
function loadOnDate()
{
	try{
		var mainlat = StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].Latitude;
		var mainlon = StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].Longitude;
		var delay = 10;
		for (var i = 0; i<Routes.length; i++) {
			Routes[i].delete();
			delay = 1000;
		}
		setTimeout(function (){
			var temp = new Date(document.querySelector('#store-date > input[type="date"]').value);
			var body = {
				date: temp.getTime() / 1000,
				storage_id: StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id
			}
			var req = new Request("/Route/get_routes", body);
			req.callback = function (Response) {
				try {
					var answer = JSON.parse(Response);
					if (answer.data.state == "success")
						{
							answer.data.routes.forEach(function (item, i) 
								{
									var temp = new Route();
									temp.paths = answer.data.routes[i].points;
									var path = [[mainlat,mainlon]];
									temp.totalTime = answer.data.routes[i].total_time;
									temp.create();
									for (var j = 0; j<temp.paths.length; j++)
										path.push([temp.paths[j].latitude, temp.paths[j].longitude]);
									ymaps.route(path).then(
										function (route) {
											var wayPoints = route.getWayPoints();
											for (var j = 0; j<wayPoints.getLength(); j++)
                                            	wayPoints.get(j).properties.set('iconContent', j);
											wayPoints.get(0).properties.set('iconContent', "С");
											Routes[Routes.indexOf(temp)].route = route;
										},
										function (error) {
											new Dialog('Возникла ошибка: ' + error.message);
										}
									);
								});
						}
					else
						new Dialog(answer.data.message);
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}
			req.do();
		}, delay);
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}

function printRoutes() {
	loader = new PreLoader();
	loader.inprogress = function () {
		var temp = new Date(document.querySelector('#store-date > input[type="date"]').value);
		var body = {
			date: temp.getTime() / 1000,
			storage_id: StoragesArray[parseInt(document.querySelector('#store-choose > select').value)].id
		}
		var afterLoad = function (Response) {
			try {
				if (Response.substring(0,4) != "%PDF") {
					var ans = JSON.parse(Response);
					new Dialog(ans.data.message);
				}
				else {
					loader.purge();
					document.getElementsByTagName("html")[0].style.opacity = "";
					setTimeout(function () {
						window.location = "/Route/get_pdf_routes?Json_input=" + JSON.stringify(body);
					}, 500);
				}

			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		
		var xhr = new XMLHttpRequest();
		var already_processed = false;
		xhr.open("GET", "/Route/get_pdf_routes?Json_input=" + JSON.stringify(body), true);
		xhr.onreadystatechange = function()
		{
			try {
				if (xhr.readyState == 4)
					if (xhr.status == 200)
					{
						var json_response = document.createElement("html");
						json_response.innerHTML = xhr.responseText;
						var answer = json_response.getElementsByTagName("json")[0];
						if (answer != void(0))
							afterLoad(answer.innerHTML);
						else
							afterLoad(xhr.responseText);
					}
					else
					{
						if (!already_processed)
						   {
								var json_response = document.createElement("html");
								json_response.innerHTML = xhr.responseText;
								var answer = json_response.getElementsByTagName("json")[0];
								already_processed = true;

								if (answer != void(0))
									afterLoad(answer.innerHTML);
								else
									{
										var ans = {};
										ans.data.state = "fail";
										ans.data.message = xhr.responseText;
										console.error = xhr.response;
										afterLoad(JSON.stringify(ans));
									}
						   }
					}
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		xhr.send();
	}
	loader.create();
}
