var loader;
window.onerror = function (message) {
	new Dialog(message);
	console.error(message);
}
/** Возвращает информацию из хранилища
* @param {string} name Имя ключа в хранилище
* @return {string} item В случае существования возвращает значение, иначе false
*/
function getVar(name) {	var item = localStorage.getItem(name); return item!=null ? item : false; }

/** Сохраняет информацию в хранилище
* @param {string} name Имя ключа
* @param {string} value Значение
*
*/
function setVar(name, value) { localStorage.setItem(name, value); }

/** Удаляет информацию из хранилища
* @param {string} name Имя ключа
*/
function delVar(name) { delete localStorage[name]; }

/** Класс запроса - реализует функцию отправки и обработки запросов
* @class
* @param {string} route Адрес запроса
* @param {Object} body Тело запроса
* @this {Request} Экземпляр класса
* @callback
*/
function Request(route, body)
{
	this.body = body;
	this.route = route;
	this.noJSON = false;
	this.callback = function () {};
}
Request.prototype = {
	/** @private */
	do: function () {
		try {
			var callback = this.callback;
			var xhr = new XMLHttpRequest();
			var already_processed = false;

			xhr.open('POST', this.route, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
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
								callback(answer.innerHTML);
							else
								callback(xhr.responseText);
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
										callback(answer.innerHTML);
									else
										{
											var ans = {};
											ans.data.state = "fail";
											ans.data.message = xhr.responseText;
											console.error = xhr.response;
											callback(JSON.stringify(ans));
										}
							   }
						}
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}
			this.noJSON ? xhr.send(this.body) : xhr.send("Json_input=" + JSON.stringify(this.body));
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
}

/** Создает информационное окно
* @class
* @param {string} message Сообщение
* @param {Array} options Набор дополнительных кнопок. Состоит из { text: "Текст кнопки", func: { Тело функции } }
*/
function Dialog(message, options)
{
	(loader != void(0)) && (loader.purge());
	this.options = (options != undefined) ? options.reverse() : [];
	this.message = (message) ? message : "Что-то пошло не так";
	this.create();
	delVar("pending");
}
Dialog.prototype = {
	DialogClear: function () {
		try {
			var _this = this;
			this.element.style.opacity = 0;
			setTimeout(function () { _this.element.remove(); }, 500);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	create: function () {
		try {
			var _this = this;
			
			this.element = document.createElement("div");
			this.element.setAttribute("class", "ex-bg");
			
			var _temp = document.createElement("div");
			_temp.setAttribute("class", "ex-block");
			this.element.appendChild(_temp);
			
			var _temp = document.createElement("div");
			_temp.setAttribute("class", "ex-message");
			_temp.innerHTML = this.message;
			this.element.children[0].appendChild(_temp);
			
			var _controls = document.createElement("div");
			_controls.setAttribute("class","ex-controls");
			
			var _temp = document.createElement("div");
			_temp.setAttribute("class", "ex-button");
			_temp.innerHTML = "OK";
			_temp.onclick = function () { _this.DialogClear(); }
			_controls.appendChild(_temp);
			
			for (var i = 0; i<this.options.length; i++)
				{
					var _temp = document.createElement("div");
					_temp.setAttribute("class", "ex-button");
					_temp.innerHTML = this.options[i].text.toUpperCase();
					var _func = this.options[i].func;
					_temp.onclick = function () { _func(); _this.DialogClear(); }
					_controls.appendChild(_temp);
				}
			
			this.element.children[0].appendChild(_controls);
			document.body.appendChild(this.element);
			setTimeout(function () {
				try {
					_this.element.style.opacity = 1;
				}
				catch (ex) { console.error(ex); new Dialog(ex.message); }
			}, 50);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
}
/** Класс загрузчика - реализует PreLoader и управление им
* @class
* @param {DOM-Element} block Желаемый блок для закрытия
* @this {PreLoader} Экземпляр класса
* @callback inprogress
*/
function PreLoader(block)
{
	this.fullscreen = (block) ? false : true;
	this.transparent = false;
	this.loader = null;
	this.block = (block) ? block : document.body;
	this.before = function () {};
	this.inprogress = function () {};
}
PreLoader.prototype = {
	create: function () {
		try {
			this.loader = document.createElement("div");
			this.loader.style.opacity = 0;

			if (this.fullscreen)
				{
					this.loader.style.left = "0";
					this.loader.style.top = "0";			
					this.loader.style.width = "100%";
					this.loader.style.height = "100%";
				}
			else
				{
					this.loader.style.width = this.block.offsetWidth + "px";
					this.loader.style.height = this.block.offsetHeight + "px";
					this.loader.style.left = this.block.getBoundingClientRect().left + "px";
					this.loader.style.top = this.block.getBoundingClientRect().top + "px";
				}

			(this.transparent) && (this.loader.style.backgroundColor = "transparent");

			this.loader.className='loader';
			var span_loader = document.createElement("span");
			span_loader.className = "loader-container";

			for (var i =0; i<4; i++)
				span_loader.appendChild(document.createElement("div"));

			this.loader.appendChild(span_loader);
			this.loader.zIndex = 10;
			this.before();

			var _this = this;
			document.body.appendChild(this.loader);

			setTimeout( function () { _this.loader.style.opacity = 1; }, 10);
			setTimeout( function () { _this.inprogress(); }, 1000);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	},
	purge: function () {
		try {
			this.loader.style.opacity = 0;
			var _this = this;
			setTimeout( function () { _this.loader.remove() }, 500);
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
}