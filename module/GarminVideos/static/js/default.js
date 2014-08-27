function MessageBox(text, fun, delay, option){
	MessageBox.option = MessageBox.option || {bgColor: "green"};
	option = option && Object.prototype.toString.call(option) === "[object Object]" && option;
	if(option && Object.prototype.toString.call(option) === "[object Object]");
	else{
		option = MessageBox.option;
	}
	MessageBox.d = delay || 5000;
	MessageBox.t = MessageBox.t || null;
	MessageBox.c = MessageBox.c || 
		function(){
			$("#MSG_BOX").animate({opacity: 0.0}, 200, function(){
				clearTimeout(MessageBox.t);
				MessageBox.t = null;
				$(this).parent().hide();
			});
		};
	MessageBox.close = MessageBox.close || function(){
		clearTimeout(MessageBox.t);
		MessageBox.t = null;
		$("#MSG_BOX").stop().animate({opacity: 0.0}, 200, function(){
			$(this).parent().hide();
			if(MessageBox.close.handler) MessageBox.close.handler();
			MessageBox.close.handler = null;
		});
	};
	if(typeof fun === "function") MessageBox.close.handler = fun;
	else MessageBox.close.handler = null;
	
	if(MessageBox.t != null) clearTimeout(MessageBox.t);
	$("#MSG_TEXT").get(0).innerHTML = text;
	if(option.bgColor){
		$("#MSG_TEXT").get(0).style.backgroundColor = option.bgColor;
	}
	$("#MSG_BOX").parent().show();
	$("#MSG_BOX").show().stop().css("opacity", 0.0).animate({opacity: 0.85}, 200, function(){
		if(MessageBox.close.handler) MessageBox.t = setTimeout(MessageBox.c, 0x7fffffff);
		else MessageBox.t = setTimeout(MessageBox.c, MessageBox.d);
	});
}