
$(document).ready(function(){
	//function serachUp(inputString) {
	
		var jId = "#suggestionContainer";
		var setArray = null;
		var jH = ".jSuggestHover";
		var jsH = "jSuggestHover";
		
		$("body").append('<div id="suggestionContainer"></div>');
		$(jId).hide();
	/**when user click on the input box then called to ajax to get the all keyword from databse*/
		$("input.serachUp").bind("click", function(event){
				$.getJSON("keyword_select.php?ajax_key=1", function(data){
						if(data['keywords']['0']) {
							numberElement = data['keywords'].length;
							setArray = null;
							setArray  = new Array(numberElement);
							for(var i=0; i<data['keywords'].length; i++ ){
								setArray[i]= data['keywords'][i];
							}
						}
				});								
		});
		
	$("input.serachUp").bind("keyup click", function(e){
		var inputString = $(this).val();
		var inputBoxId = $(this).attr('id');
		
		if(inputString.length != 0) {
		// Hide the suggestion box.
			var getCurrentWord = lastWord(inputString);
					
			var offSet = $(this).offset(); 
			var $content ="";
			
			$(jId).css({
					position: "absolute",
					top: offSet.top + $(this).outerHeight() + "px",
					left: offSet.left,
					width: $(this).outerWidth()-2 + "px",
					zIndex: 20000
				}).show();
				
		//key handling for enter,down,up and ESC
  	   if (e.keyCode == 27 ) {
  					$(jId).hide();
  				}				
  				// if enter key
  			
  			
    	else if (e.keyCode == 13 ) {
    			e.preventDefault();
    			var	getValueOfCurrentbox = $("input[name='"+inputBoxId+"']").attr("value");
    			if ($(jH).length == 1)
						$("input[name='"+inputBoxId+"']").val(validInputBox(getValueOfCurrentbox) +$(jH).text()+' ');
						$(jId).hide();
				return false;
    				}
   
  				// if down arrow
  		else if (e.keyCode == 40) {
  					// if any suggestion is highlighted
  					e.preventDefault();
  					if ($(jH).length == 1) {
  						if (!$(jH).next().length == 0) {
  							$(jH).next().addClass(jsH);
  							$(".jSuggestHover:eq(0)").removeClass(jsH);		
  						}
  					}
  					else {
  						$("#suggestionContainer ul li:first-child").addClass(jsH);
  					}
  					
  				}
  				
  				// if up arrow
  		else if (e.keyCode == 38) {
  					// if any suggestion is highlighted
  					e.preventDefault();
  					if ($(jH).length == 1 ) {
 						if (!$(jH).prev().length == 0) {
  							$(jH).prev().addClass(jsH);
  							$(".jSuggestHover:eq(1)").removeClass(jsH);
  						
  						}
  						// if is first child
  						else {
  							$(jH).removeClass(jsH);
  						}
  					}
  				}
  				
				//suggestions displying 				
		else if(setArray) {				
					$content += "<ul id='seach_box'>";
					for(var i=0; i< setArray.length ; i++){
						if (matchSubset(setArray[i],getCurrentWord)){
							$content += "<li>"+ setArray[i] + "</li>";	
						}
						else{
							continue;
						}
					}
					$content += "</ul>";
					
			$("#suggestionContainer").show();
			$(jId).find('ul').remove();
			$(jId).append($content);
			
								$("#suggestionContainer ul li").bind("mouseover",	function(){
										$(jH).removeClass(jsH);
										$(this).addClass(jsH);
								});
							/**when click the suggestion drop dwon, choosed one will go to the input keyword box*/	
								$("#suggestionContainer ul li").click(function(){
									//$(this).addClass(jsH);
									textVal = $(this).text();
									var	getValueOfCurrentbox = $("input[name='"+inputBoxId+"']").attr("value");
									if(getValueOfCurrentbox.lenght ==0 ){
										setValue = textVal;
									}else{
										setValue = validInputBox(getValueOfCurrentbox)+textVal+" ";
									}									
									$("input[name='"+inputBoxId+"']").val(setValue);
									$("input[name='"+inputBoxId+"']").focus();

								});	
				}
			
		}
});	

	//} // lookup
	$("input.serachUp").blur(function(){
		setTimeout("$('#suggestionContainer').hide();",200);	
	});
	$(".dicttionary").click(function(){   		
    	$("body").append('<div id="dictionary_message"></div>');
		$("#dictionary_message").hide();		
	/**ajax called to update keyword dictionary database*/
		$.ajax({
  		type: "GET",
   		url: "keyword_create_dict.php",
   		success: function(msg){
   			var message_dic ="<p style='position:relative;text-align:center;top:7px; color: #c00000; font-size:14px'><strong>Keyword dictionary updated..</strong></p>"; 
		/**the styles to Message box*/	    
			   $("#dictionary_message").css({
					top:"0px",
					textAlign: "center",
					zIndex: 20000,
					width: "100%",
					height: '30px',
					backgroundColor: '#F4F1DF',
					borderBottom: '3px solid #E1DAB7',
					position: "fixed"
			}).show();
		/**show the message box and will be at top of the page*/	
			$("#dictionary_message").show();
			$("#dictionary_message").find('p').remove();
			$("#dictionary_message").append(message_dic);
		/**Set to time out the Message which at time to hide*/	
     		setTimeout(function () {
				$("#dictionary_message").fadeOut("slow");
				},5000);      		
   			}
 		});
	});
			var blockSubmit=true;
	// prevent form submit in opera when selecting with return key
	$.browser.opera && $(input.form).bind("submit.autocomplete", function() {
		if (blockSubmit) {
			blockSubmit = false;
			return false;
		}
	});	
	
	function trimWords(value) {
		var words = value.split(' ');
		var result = [];
		$.each(words, function(i, value) {
			if ( $.trim(value) )
				result[i] = $.trim(value);
		});
		return result;
	}
	
	function lastWord(value) {
		var words = trimWords(value);
		return words[words.length - 1];
	}
	
	function matchSubset(s, sub) {
			s   = s.toLowerCase();
			sub = sub.toLowerCase();
		var i = s.indexOf(sub);
		if (i == -1) 
			return false;
		return i == 0 || false;
	};
	
	function validInputBox(value){
		var validWords = trimWords(value);
		var temp ="";
			validWords = validWords.splice(0,[validWords.length-1]) 
		$.each(validWords, function(i, value) {
			temp += value + ' ';
		});
		return temp;
	}

});
/**Tis disableEndterKey funtion is used to prevent the aouto submition form event*/
function disableEnterKey(e){
     var key;     
     if(window.event)
          key = window.event.keyCode; //IE
     else
          key = e.which; //firefox     
	/**return false to the enter eky*/
     return (key != 13);
}