var Routes = [];
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
	/* Меню */
	var top_tabs = document.getElementById("tab-bar").children;
	top_tabs[0].onclick = function () { window.location.href = "/"; }
	top_tabs[1].onclick = function () { window.location.href = "/Route"; }
	document.querySelector('#store-date > input[type="date"]').onchange = loadOnDate;
	var req = new Request("/API/get_time");
	req.callback = function (Response) {
		try {
			var answer = JSON.parse(Response);
			if (answer.data) {
				var _t = new Date(answer.data * 1000);
				var date_input = _t.getFullYear() + "-";
				(_t.getMonth() < 10) && (date_input += "0");
				date_input += (_t.getMonth() + 1) + "-";
				(_t.getDate() < 10) && (date_input += "0");
				date_input += _t.getDate();
				document.querySelector('#store-date > input[type="date"]').value = date_input;
				delVar("pending");
			}
			else
				new Dialog("Ошибка ответа сервера");
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
	req.do();
}
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
	toggle: function () {
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
	},
	showOnMap: function () {
		myMap.geoObjects.removeAll();
		myMap.geoObjects.add(this.route);
	},
	create: function () {
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
	},
	select: function () {
		if (!this.isOpen)
			for(var i = 0; i < Routes.length;i++)
				if (Routes[i].isOpen)
					Routes[i].toggle();
		this.toggle();
	},
	delete: function () {
		var _this = this;
		if (this.isOpen)
			this.toggle();
		this.block.style.marginLeft = "-600px";
		setTimeout(function () {
			_this.block.style.height = "0px";
			_this.block.style.marginTop = "0px";
			setTimeout(function () {
				_this.block.remove();
				Routes.splice(Routes.indexOf(_this),1);
				_this = null;
			}, 500);
		}, 300);
	}
}

function loadOnDate()
{
	try{
		var mainlat = 53.9383;
		var mainlon = 27.5783;
		for (var i = 0; i<Routes.length; i++)
			Routes[i].delete();
		setTimeout(function (){
			var body = {
				date: new Date(document.querySelector('#store-date > input[type="date"]').value)
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
									temp.totalTime = answer.data.routes[i].total_time - 64800;
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
		}, 10);
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}