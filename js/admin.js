var settingArray = [];
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
		
		var menu = document.getElementById("menu");
		/* STORAGE SETTINGS */
		
		var set = new Settings();
		set.menublock = document.createElement("p");
		set.menublock.innerHTML = "Склады";
		set.menublock.addEventListener("click", function () {
			set.show();
		});
		set.settingblock = document.getElementById("storage-settings");
		set.editWindow = function (data) {
			try {
				var el = document.createElement("div");
				el.setAttribute("class", "ex-bg");
				el.innerHTML = '<div id="storage-edit"><input type="text" placeholder="Название склада"><input type="text" placeholder="Улица"><input type="text" placeholder="Дом"><input type="text" placeholder="Примечание"><div class="button-save"></div><div class="button-cancel"></div>';
				if (data != void(0)) {
					el.getElementsByTagName("input")[0].value = data.name;
					el.getElementsByTagName("input")[1].value = data.street;
					el.getElementsByTagName("input")[2].value = data.house;
					el.getElementsByTagName("input")[3].value = data.note;
				}
				el.querySelector("div.button-save").addEventListener("click", function () {
					try {
						var body = {
							name: el.getElementsByTagName("input")[0].value,
							street: el.getElementsByTagName("input")[1].value,
							house: el.getElementsByTagName("input")[2].value,
							note: el.getElementsByTagName("input")[3].value,
						}
						if (data != void(0)) {
							body.storage_id = parseInt(data.id);
							var req = new Request("/Storages/update_storage", body);
						}
						else
							var req = new Request("/Storages/add_storage", body);
						req.callback = function (Response) {
							try {
								var ans = JSON.parse(Response);
								if (ans.data.state == "success") {
									el.style.opacity = "";
									setTimeout(function () { el.remove(); }, 500);
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
				el.querySelector("div.button-cancel").addEventListener("click", function () {
					el.style.opacity = "";
					setTimeout(function () { el.remove(); }, 500);
				});
				document.body.appendChild(el);
				setTimeout(function () { el.style.opacity = 1; }, 50);
			}
			catch (ex) { console.error(ex); new Dialog(ex.message); }
		}
		
		var storageSet = document.querySelector("#storage-settings > div.option-container");
		var req = new Request("/Storages/get_storages");
		req.callback = function (Response) {
			try {
				var ans = JSON.parse(Response);
				if (ans.data.state == "success") {
					storageSet.innerHTML = "";
					set.items = ans.data.storages;
					ans.data.storages.forEach(function (item) {
						var main = document.createElement("div");
						main.setAttribute("class", "option-storage");
						main.setAttribute("data-id", item.id);

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
							try {
								var body = {
									storage_id: parseInt(item.id)
								}
								var req = new Request("/Storages/delete_storage", body);
								req.callback = function (Response) {
									try {
										var ans = JSON.parse(Response);
										if (ans.data.state != "success")
											new Dialog(ans.data.message);
									}
									catch (ex) { console.error(ex); new Dialog(ex.message); }
								}
								req.do();
							}
							catch (ex) { console.error(ex); new Dialog(ex.message); }
						});
						main.appendChild(el);

						var el = document.createElement("div");
						el.setAttribute("class", "button-edit");
						el.addEventListener("click", function () {
							set.editWindow(item);
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
		set.longpoll = function() {}
		document.getElementById("storage-settings").getElementsByClassName("add-floating-button")[0].addEventListener("click", function () { set.editWindow(); });
		menu.appendChild(set.menublock);
	}
	catch (ex) { console.error(ex); new Dialog(ex.message); }
}

function Settings() {
	this.menublock = null;
	this.settingblock = null;
	this.longpoll = null;
	this.longpullmd5 = null;
	this.index = settingArray.length;
	settingArray.push(this);
}
Settings.prototype = {
	show: function () {
		if (this.menublock.getAttribute("selected") == void(0)) {
			settingArray.forEach(function (item) {
				if (item != this) {
					(item.menublock != void(0)) && (item.menublock.removeAttribute("selected"));
					(item.settingblock != void(0)) && (item.settingblock.removeAttribute("selected"));
					(item.longpoll != void(0)) && (clearInterval(item.longpoll));
				}
			});
			this.menublock.setAttribute("selected", "");
			this.settingblock.setAttribute("selected", "");
			this.longpoll();
		}
	}
}
window.onload = doOnLoad;