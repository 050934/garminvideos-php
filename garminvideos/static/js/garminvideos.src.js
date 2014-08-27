(function(){
	var indicator = 1,
		candidates = 4,
		interval = null;
				
	$("#video_back_stage li").hover(function(){
		onStage(true, $(this).children().children("a")[0]);
	}, function(){
		interval = onStage(false);
	});
	$("#video_stage li div.grmn-lockup-thumbnail a").hover(function(){
		interval && clearInterval(interval);
		interval = null;
	}, function(){
		interval = onStage(false);
	});
	
	function onStage(rightNow, target){
		if(rightNow){
			interval && clearInterval(interval);
			interval = null;
			var idx = -1;
			$("#video_back_stage li").children()
			.children("a").each(function(index){
				if(this === target){
					idx = index;
					!$(this).hasClass("onstage") &&
						$(this).addClass("onstage");						
				}
				else{
					$(this).hasClass("onstage") &&
						$(this).removeClass("onstage");
				}
			});
			
			if(idx !== -1 && (idx + 1) !== indicator){
				indicator = idx;
				$("#video_stage li:eq(0)").stop()
				.animate(
					{"margin-top": -indicator * 330 + "px"}, 500
				);
				indicator++;
			}
		}else{
			return setInterval(function(){
				latestFirstMarginTop = 16;

				$("#video_stage li:eq(0)").stop()
				.animate(
					{"margin-top": -indicator * 330 + "px"}, 500
				);
				if(!(indicator % candidates)){
					$("#latest_first").stop()
					.animate(
						{"margin-top": -(300 + latestFirstMarginTop) + "px"}, 500
					, function(){
						$("#video_stage li:eq(0)").stop().css("margin-top", 0);
						$("#latest_first").stop().css("margin-top", latestFirstMarginTop);
					});					
				}				
				
				indicator = indicator % candidates;
				$("#video_back_stage li").each(function(idx){
					var a = $(this).children().children("a");
					if(idx === indicator){
						!a.hasClass("onstage") && 
							a.addClass("onstage");
					}else{
						a.hasClass("onstage") &&
							a.removeClass("onstage");
					}
				});
				indicator++;
			}, 4000);
		}
	}
	
	interval = onStage(false);
	$("#video_back_stage li:eq(0)").children().children("a").addClass("onstage");
	
	$("#pop_by, #pop_orderby").live("change", popVideoChange);
	
	function popVideoChange(){
		var option = $("#pop_by option:selected").val(),
			now = new Date();
			end = [now.getFullYear(), now.getMonth() < 9 ? ("0" + (now.getMonth() + 1)) : now.getMonth() + 1,
				   now.getDate() < 10 ? "0" + now.getDate() : now.getDate()].join("-"),
			start = "";
		
		var baseURL = $("#pop_item_container").attr("data-baseurl"),
			youtube = $("#pop_item_container").attr("data-youtube");
			
		switch(option){
			case "week":
				if(now.getDate() - 7 >= 0){
					var startDate = now.getDate() - 6;
					start = end.substring(0, 8) + (startDate < 10 ? "0" + startDate : startDate);
				}else{
					var monthDays = {"1": 31, "2": 28, "3": 31, "4": 30, "5": 31,
									"6": 30, "7": "31", "8": 31, "9": 30, "10": 31,
									"11": 30, "12": 31};
					if(now.getMonth() == 0){
						start = (now.getFullYear() - 1) + "-12-" + (31 - (6 - now.getDate()));
					}else{
						var preMonth  = now.getMonth();
						start = [now.getFullYear(), preMonth < 10 ? "0" + preMonth : preMonth,
						 		monthDays[preMonth.toString()] - (6 - now.getDate())].join("-");
					}
				}
				break;
			case "month":
				if(now.getMonth() === 0){
					start = (now.getFullYear() - 1) + "-12-" + end.substring(8);
				}else{
					start = [now.getFullYear(), now.getMonth() < 10 ? "0" + now.getMonth() : now.getMonth(),
							end.substring(8)].join("-");
				}
				break;
			case "year":
				var startYear = now.getFullYear() - 1;
				start = startYear + end.substring(4);
				break;
			default:
				break;
		}
		
		$.ajax({
			url: baseURL + "pop/" + (youtube ? "?" + youtube : ""),
			data:{orderby: $("#pop_orderby option:selected").val(),
				  by: option, start: option ? start : "",
				  end: option ? end : "", alt: "html"},
			success: function(data){
				$("#pop_item_container").after(data).remove();
			},
			error: function(jqXHR, textStatus){
				$("#pop_item_container ul").html('<li style="text-align: center;">' + 
					textStatus + " - " + jqXHR.status + 
				'</li>');
			}
		});
	}	

	$("#category_by").change(function(){
		var option = $("#category_by option:selected").val(),
			baseURL = $("#category_container").attr("data-baseurl"),
			queryString = $("#category_container").attr("data-qs");
			
		if(queryString && option)
			queryString += ("&cat=" + option);
		else if(!queryString && option)
			queryString += ("?cat=" + option);
			
		document.location.assign(baseURL + queryString);
	});
	
	$(".grmn-video-category").click(function(){
		if($(this).hasClass("grmn-video-category-checked"))
			return;
		var option = $(this).children("input").val(),
			baseURL = $("#category_container").attr("data-baseurl"),
			queryString = $("#category_container").attr("data-qs");
			
		if(queryString && option)
			queryString += ("&cat=" + option);
		else if(!queryString && option)
			queryString += ("?cat=" + option);
			
		document.location.assign(baseURL + queryString);		
	});
	
}());