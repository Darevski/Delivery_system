function DoOnLoad()
{
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
	this.adress = {
		street: null,
		house: null,
		block: null,
		entry: null,
		floor: null,
		flat: null
	}
	
	this.ordertime = {
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
			_t2.innerHTML = this.adress.street;
			(!this.adress.house) && (_t2.innerHTML += ", д." + this.adress.house);
			(!this.adress.flat) && (_t2.innerHTML += ", кв. " + this.adress.flat);
			
			_t1.appendChild(_t2);
			_order.appendChild(_t1);
			
			var _t1 = document.createElement("div");
			_t1.setAttribute("class", "order-body");
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "Улица...");
			_t2.setAttribute("class", "order-adress-street");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "дом");
			_t2.setAttribute("class", "order-adress-house");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "корп.");
			_t2.setAttribute("class", "order-adress-block");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "под.");
			_t2.setAttribute("class", "order-adress-entry");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "этаж");
			_t2.setAttribute("class", "order-adress-floor");
			_t2.setAttribute("disabled", "1");
			_t1.appendChild(_t2);
			
			var _t2 = document.createElement("input");
			_t2.setAttribute("type", "text");
			_t2.setAttribute("placeholder", "кв.");
			_t2.setAttribute("class", "order-adress-flat");
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
			_t3.innerHTML = this.items.count;
			(this.items.count == 1) && (_t3.innerHTML += " товар на сумму");
			((this.items.count > 1) && (this.items.count < 5)) && (_t3.innerHTML += " товара на сумму");
			(this.items.count > 4) && (_t3.innerHTML += " товаров на сумму");
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class", "button-more");
			_t3.onclick = function () { new Dialog("Просмотр и редактирование товаров еще недоступно"); }
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
			_t3.onclick = function () { _this.editmode(); }
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class", "button-reset");
			_t3.onclick = function () { new Dialog("Обнуление заказа еще недоступно"); }
			_t2.appendChild(_t3);
			
			var _t3 = document.createElement("div");
			_t3.setAttribute("class", "button-save");
			_t3.onclick = function () { new Dialog("Сохранение заказа еще недоступно"); }
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
    editmode: function () {
        try {
            var _temp = this.Object.getElementsByTagName("input");
            for (var i = 0; i < _temp.length; i++)
                _temp[i].removeAttribute("disabled");
            this.Object.getElementsByClassName("button-reset")[0].style.opacity = 0;
            this.Object.getElementsByClassName("button-save")[0].style.opacity = 0;
            this.Object.getElementsByClassName("button-edit")[0].style.opacity = 0;
            this.Object.getElementsByClassName("button-reset")[0].style.width = "24px";
            this.Object.getElementsByClassName("button-save")[0].style.width = "24px";
            var _this = this;
            setTimeout(function () {
                _this.Object.getElementsByClassName("button-edit")[0].style.width = "0px";
                _this.Object.getElementsByClassName("button-reset")[0].style.opacity = 0.6;
                _this.Object.getElementsByClassName("button-save")[0].style.opacity = 0.6;
            }, 200);
        }
        catch (ex) { console.error(ex); new Dialog(ex.message); }
    }
}