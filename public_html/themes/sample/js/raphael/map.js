$(document).ready(function(){
	
var mapWidth = 		232;
var mapHeight =		225;
	 //start map
    var r = new ScaleRaphael('map', 978, 950),
        attributes = {
            fill: '#fff',
            stroke: '#3899E6',
            'stroke-width': 1,
            'stroke-linejoin': 'round'
        },
        arr = new Array();	
	
	for (var country in paths) {
		
		var obj = r.path(paths[country].path);
		
		obj.attr(attributes);
		
		arr[obj.id] = country;
		
		obj
		.hover(function(){
			this.animate({
				fill: '#1669AD'
			}, 300);
		}, function(){
			this.animate({
				fill: attributes.fill
			}, 300);
		})
		.click(function(){
			//document.location.hash = arr[this.id];
			mapclicked(paths[arr[this.id]].name);
			
			
			/*var point = this.getBBox(0);
			
			$('#map').next('.point').remove();
			
			$('#map').after($('<div />').addClass('point'));
			
			$('.point')
			.html(paths[arr[this.id]].name)
			.prepend($('<a />').attr('href', '#').addClass('close').text('Close'))
			.css({
				left: point.x+(point.width/2)-80,
				top: point.y+(point.height/2)-20
			})
			.fadeIn();*/
			
		});
		
		$('.point').find('.close').live('click', function(){
			var t = $(this),
				parent = t.parent('.point');
			
			parent.fadeOut(function(){
				parent.remove();
			});
			return false;
		});
		
		
		 
		resizeMap(r);
	}
	
	
	
    function resizeMap(paper) {

        paper.changeSize(mapWidth, mapHeight, true, false);

        //if (useSideText == 'true') {
        //    $(".mapWrapper").css({
        //        'width': (parseFloat(mapWidth, 10) + parseFloat(textAreaWidth, 10)) + 'px',
        //        'height': mapHeight + 'px'
        //    });
       // } else {
            $(".mapWrapper").css({
                'width': mapWidth + 'px',
                'height': mapHeight + 'px'
            });
       // }

    }
	
	
	
	
	
		
			
});

