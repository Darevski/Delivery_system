function doOnLoad() {
	try {
		document.getElementsByTagName("html")[0].style.transition = "0.5s";
		document.getElementsByTagName("html")[0].style.opacity = "1";
		/* MENU */
		var top_tabs = document.getElementById("tab-bar").children;
		top_tabs[0].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () { window.location.href = "/"; }, 600);
		}
		top_tabs[1].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () { window.location.href = "/Route"; }, 600);
		}
		top_tabs[3].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () { window.location.href = "/API/user_exit"; }, 600);
		}
		top_tabs[2].onclick = function () {
			document.getElementsByTagName("html")[0].style.opacity = "";
			setTimeout(function () { window.location.href = "/Settings"; }, 600);
		}
		
		document.getElementById("storage-settings").getElementsByClassName("add-floating-button")[0].onclick = addStorage;
		loadStores();
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}/*
function storageOption() {
	this.street = null;
	this.house = null;
	this.note = null;
	this.name = null;
	this.id = null;
	
	this.block = null;
}
storageOption.prototype = {
	getData: function () {
		return this;
	},
	update: function () {
		try {
			var body = {
				storage_id: this.id,
				name: this.name,
				street: this.street,
				house: this.house,
				note: this.note
			}
			var _this = this;
			var req = new Request("/Storages/update_storage", body);
			req.callback = function (Response) {
				try {
					var ans = JSON.parse(Response);
					if (ans.data.state == "success") {
						AllSettings[0].remove(_this);
					}
					else
						new Dialog(ans.data.message);
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}
			req.do();
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	display: function () {
		
	}
}
function storageSettings() {
	this.options = [];
}
storageSettings.prototype = {
	load: function () {
		try {
			var storageSet = document.querySelector("#storage-settings > div.option-container");
			var req = new Request("/Storages/get_storages");
			req.callback = function (Response) {
				try {
					var ans = JSON.parse(Response);
					if (ans.data.state == "success") {
						storageSet.innerHTML = "";
						ans.data.storages.forEach(function (item) {

							el.addEventListener("click", function () {
								var body = {
									storage_id: _this.id
								}
								var reqDelete = new Request("/Storages/delete_storage", body);
								reqDelete.callback = function (ResponseDelete) {
									
								}
								reqDelete.do();
							});
							main_el.appendChild(el);
						});
					}
					else
						new Dialog(ans.data.message);
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}
			req.do();
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
}
*/

function loadStores () {
	try {
		var storageSet = document.querySelector("#storage-settings > div.option-container");
		var req = new Request("/Storages/get_storages");
		req.callback = function (Response) {
			try {
				var ans = JSON.parse(Response);
				if (ans.data.state == "success") {
					storageSet.innerHTML = "";
					ans.data.storages.forEach(function (item) {
						var main = document.createElement("div");
						main.setAttribute("class", "option-storage");
						main.setAttribute("data-id", item.id);
						main.setAttribute("data-full", JSON.stringify(item));
						
						var el = document.createElement("p");
						el.innerHTML = item.name;
						main.appendChild(el);
						
						var el = document.createElement("p");
						el.innerHTML = item.street + " " + item.house;
						main.appendChild(el);
						
						var el = document.createElement("p");
						el.innerHTML = item.note;
						main.appendChild(el);
						
						var el = document.createElement("div");
						el.setAttribute("class", "button-cancel");
						el.addEventListener("click", function () {
							deleteStorage(main);
						});
						main.appendChild(el);

						var el = document.createElement("div");
						el.setAttribute("class", "button-edit");
						el.addEventListener("click", function () {
							editStorage(main);
						});
						main.appendChild(el);
						
						storageSet.appendChild(main);
					});
				}
				else
					new Dialog(ans.data.message);
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		req.do();
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}
function addStorage() {
	try {
		var el = document.createElement("div");
		el.setAttribute("id", "storage-edit");
		el.innerHTML = '<input type="text" placeholder="Название склада"><input type="text" placeholder="Улица"><input type="text" placeholder="Дом"><input type="text" placeholder="Примечание"><div class="button-save"></div><div class="button-cancel">';
		el.getElementsByTagName("div")[0].addEventListener("click", function () {
			try {
				var body = {
					name: el.getElementsByTagName("input")[0].value,
					street: el.getElementsByTagName("input")[1].value,
					house: el.getElementsByTagName("input")[2].value,
					note: el.getElementsByTagName("input")[3].value
				}
				var req = new Request("/Storages/add_storage", body);
				req.callback = function (Response) {
					try {
						var ans = JSON.parse(Response);
						if (ans.data.state == "success") {
							el.remove();
							loadStores();
						}
						else
							new Dialog(ans.data.message);
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				req.do();
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		});
		el.getElementsByTagName("div")[1].addEventListener("click", function () {
			el.remove();
		});
		document.body.appendChild(el);
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}
function editStorage(elem) {
	try {
		var el = document.createElement("div");
		el.setAttribute("id", "storage-edit");
		el.innerHTML = '<input type="text" placeholder="Название склада"><input type="text" placeholder="Улица"><input type="text" placeholder="Дом"><input type="text" placeholder="Примечание"><div class="button-save"></div><div class="button-cancel"></div>';
		var info = JSON.parse(elem.getAttribute("data-full"));
		el.getElementsByTagName("input")[0].value = info.name;
		el.getElementsByTagName("input")[1].value = info.street;
		el.getElementsByTagName("input")[2].value = info.house;
		el.getElementsByTagName("input")[3].value = info.note;
		el.getElementsByTagName("div")[0].addEventListener("click", function () {
			try {
				var body = {
					name: el.getElementsByTagName("input")[0].value,
					street: el.getElementsByTagName("input")[1].value,
					house: el.getElementsByTagName("input")[2].value,
					note: el.getElementsByTagName("input")[3].value,
					storage_id: parseInt(elem.getAttribute("data-id"))
				}
				var req = new Request("/Storages/update_storage", body);
				req.callback = function (Response) {
					try {
						var ans = JSON.parse(Response);
						if (ans.data.state == "success") {
							el.remove();
							loadStores();
						}
						else
							new Dialog(ans.data.message);
					}
					catch (ex) { console.error(ex); new Dialog(ex.message); }
				}
				req.do();
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		});
		el.getElementsByTagName("div")[1].addEventListener("click", function () {
			el.remove();
		});
		document.body.appendChild(el);
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}
function deleteStorage(el) {
	try {
		var body = {
			storage_id: parseInt(el.getAttribute("data-id"))
		}
		var req = new Request("/Storages/delete_storage", body);
		req.callback = function (Response) {
			try {
				var ans = JSON.parse(Response);
				if (ans.data.state == "success") {
					el.remove();
				}
				else
					new Dialog(ans.data.message);
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		req.do();
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}
window.onload = doOnLoad;