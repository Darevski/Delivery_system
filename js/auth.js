function authTry() {
	var body = {
		login: document.getElementsByName("login")[0].value,
		password: document.getElementsByName("password")[0].value
	}
	var req = new Request("/API/user_enter", body);
	req.callback = function(Response) {
		try {
			var ans = JSON.parse(Response);
			if (ans.data.state === "success") {
				document.getElementById("auth-message").style.opacity = "";
				var elems = document.getElementById("auth-enter").children;
				elems[0].setAttribute("status", "good");
				elems[1].setAttribute("status", "good");
				setTimeout(function () {
					document.getElementsByTagName("html")[0].style.opacity = "";
					setTimeout(function () {
						window.location="/";
					}, 500);
				}, 800);
			}
			else {
				if (ans.data.response_code === 403) {
					document.getElementById("auth-message").style.opacity = "1";
					var elems = document.getElementById("auth-enter").children;
					elems[0].setAttribute("status", "error");
					elems[1].setAttribute("status", "error");
				}
				else {
					document.getElementById("auth-message").style.opacity = "1";
					document.getElementById("auth-message").innerHTML = ans.data.message;
				}
			}
		}
		catch (ex) { console.error(ex); new Dialog(ex.message); }
	}
	req.do();
}
window.onload = function () {
	document.getElementsByTagName("html")[0].style.transition = "0.5s 0.3s";
	document.getElementsByTagName("html")[0].style.opacity = "1";
	document.getElementById("auth-enter").children[0].addEventListener("change", function () { this.removeAttribute("status"); });
	document.getElementById("auth-enter").children[1].addEventListener("change", function () { this.removeAttribute("status"); });
	if (document.getElementsByTagName("json")[0].innerHTML != "")
		new Dialog("Ваши данные авторизации недействительны. Пожалуйста, авторизируйтесь вновь.");
}