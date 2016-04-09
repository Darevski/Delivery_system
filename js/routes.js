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