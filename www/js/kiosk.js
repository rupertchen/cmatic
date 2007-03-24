
	var lastSearch = "asdf";

	function getSearchLoop(targetID){
		var target = document.getElementById(targetID);
		//setResult(target.value);
		if(target.value != lastSearch){
		 lastSearch = target.value
		setResult(searchData(target.value));
		}
		setTimeout("getSearchLoop('" + targetID + "');", 500);
	}




var tbody = new Array();
var curPage = 0;
var lastPage = 0;
	function setResult(content){
		var ret = HTML.makeElement(null, "div");
		var drawDest = document.getElementById("results");
		
//Table
		 var table = HTML.makeTable(ret);
  		  table.addClass("resultsList");

//Headers

	    var thead = HTML.makeElement(table, "thead");
	    var headers = ["Event Code", "Age", "Gender", "Level", "Form"];
	    for (var i = 0; i < headers.length; i++) {
	        var th = HTML.makeElement(table, "th", {"scope": "col"});
	        HTML.makeText(th, headers[i]);
	    }

i=0;
console.debug("NUM RESULTS: "+content.length)
//Contents
for(var q = 0; q<1&& q*20+i < content.length; q++){
 tbody[q] = HTML.makeElement(table, "tbody");
 lastPage = q;
    for (var i = 0; q*20+i < content.length && i < 20; i++) {
        var tr = HTML.makeElement(tbody[q], "tr", {"onMouseDown": "displayEvent("+content[i].event_id+")"});
       // var tr=HTML.makeElement(null, "span");
        tr.addClass((0 == i%2) ? "evenRow" : "oddRow");

        var c = content[q*20+i].form_blowout[0];
        
        var cells;
        cells = new Array(5);

	  cells[0] =  HTML.makeText(null, content[q*20+i].event_code);
        cells[1] = HTML.makeText(null, CMAT.formatAgeGroupId(c.age_group_id));
        cells[2] = HTML.makeText(null, CMAT.formatGenderId(c.gender_id));
        cells[3] = HTML.makeText(null, CMAT.formatLevelId(c.level_id));
        cells[4] = HTML.makeText(null, CMAT.formatFormId(c.form_id));

		

               // Assemble all
        for (var j = 0; j < cells.length; j++) {
            var td = HTML.makeElement(tr, "td");
            td.addClass("registrationTableCell");
            td.appendChild(cells[j]);
        }


	


    }

	if(q != 0) tbody[q].style.display = 'none';
//	console.debug(q);
	//tbody[q].style.display='none';
	//tbody[2].style.display='block';

}



    // nothing fancy for now
    this.root = ret;

    // Draw to page
   // var drawDest = $(this.drawLocation);
    drawDest.innerHTML = "";
    drawDest.appendChild(this.root);






	}

function PageRight(){
//	tbody[2].style.display='inline';
	console.debug(curPage);
if(curPage != lastPage){
tbody[curPage].style.display = 'none';
curPage++;
tbody[curPage].style.display = ''	;
}
}
function PageLeft(){
if(curPage != 0){
tbody[curPage].style.display = 'none';
curPage--;
tbody[curPage].style.display = '';	
}	
	
}



function drawHelp(){
	console.debug("HELPING");
	var drawDest = document.getElementById("eventArea");
	drawDest.innerHTML = "Welcome to the Event Kiosk<br> Here you can search and view results for completed events.<br> To find an event, simply type in relevant data into the search box to narrow the results<br><br>For Example: 'Fem Trad beg' will return any Female Traditional Beginner level event";	
	
}


function displayEvent(eventID){
	console.debug(eventID);
	//var drawDest = document.getElementById("eventArea");
	//drawDest.innerHTML = ""+eventID;	
	
	
	
	      var ajax = new Json.Remote("../query/get_event_scoring.php?e=" + eventID,
                {"onComplete" : function (x) { new EventDisplay("eventArea", x); }});
            ajax.send();
      
      //new EventDisplay("eventArea", oneEventBlob);  

}








	function doJson(){
	
		var myAjax = new Json.Remote("../query/get_event_summary_list.php",
            {"onComplete" : function (x) {
		
                loadEventData(x);
            }});
		myAjax.send();
		
	//	loadEventData(hugeBlob);
	}

	function loadEventData(data){
		d = data;
	}

	function loadCompetitorData(data){
		cd = data;
	}
	var temp;
	var temp2;
	function searchData(values){
		
		var curData = d;
		
		var newCompetitors;
		values = values.toLowerCase();
		var valueArray = values.split(" ");
		
		for( i=0;i<valueArray.length; i++){
			var query = valueArray[i];
			curData = curData.filter(MatchGenerator(query, []));
		}
		
		return curData;


	}


	function competitorMatchGenerator(name){
		
		return function isCompetitorMatch(element){
			fName = element.first_name.toLowerCase();
			lName = element.last_name.toLowerCase()
			if(fName.indexOf(name) != -1 || lName.indexOf(name) != -1){
				return true;
			}
			return false;
		}


	}

	function MatchGenerator(value, competitor){


		return function isMatched(element){
			//console.debug(element+ " " + value);
/*
		if(competitor.length > 0){
		console.debug(competitor.length);
		 var returnFalse = true;
		 for(i = 0; i < competitor.length; i++){
			if(competitor[i].age_group_id == element.age_group_id && competitor[i].gender_id == element.gender_id && competitor[i].level_id == element.level_id){

				for(j =0; j < competitor[i].registration.length; j++){
			 		if(competitor[i].registration[j].form_id == element.form_id) returnFalse = false;
				}

			}
		 }
		 if(returnFalse) return false;
		}
*/
			if(element.form_blowout.length==0)return false;
			var searchable = CMAT.makeEventSearchString(element);
			searchable = searchable.toLowerCase();
		//console.debug(value + " " + value.toString()+" " +searchable);

			if(searchable.indexOf(value) == -1){
		//console.debug("miss "+searchable);	
			 return false;
			}

			return true;

		}
	}

	
	
	
	
	
	
	
/**
 * Event Scoring
 */
function EventDisplay (drawLocation, data) {
    this.drawLocation = drawLocation;
    this.d = null;

    // DOM
    this.root = null;
    this.titleBar = null;
    this.contentBox = null;

    // initialize data
    if (data) {
        this.setData(data);
    }
};


EventDisplay.prototype.setData = function (data) {
    this.d = data;
    this.makeDom();
};

EventDisplay.prototype.repaint = function () {
};

EventDisplay.prototype.makeDom = function () {
    this.root = HTML.makeElement(null, "div");
    this.root.addClass("module");
    this.root.addClass("eventScoring");

    // Title bar
    this.titleBar = HTML.makeElement(this.root, "div");
    this.titleBar.addClass("eventScoringTitleBar");
    var controls = HTML.makeElement(this.titleBar, "div");
    controls.addClass("eventScoringControlBox");

 

    HTML.makeText(this.titleBar, this.d.event_code);

    // Content
    this.contentBox = HTML.makeElement(this.root, "div");
    this.contentBox.addClass("eventScoringContent");

    var table = HTML.makeTable(this.contentBox);
    var thead = HTML.makeElement(table, "thead");
    for (var i = 0; i < EVENT_SCORING.HEADER_TEXT_5.length; i++) {
        var th = HTML.makeElement(thead, "th", {"scope":"col"});
        HTML.makeText(th, EVENT_SCORING.HEADER_TEXT_5[i]);
    }
    var tbody = HTML.makeElement(table, "tbody");
    for (var i = 0; i < this.d.scoring.length; i++) {
        var row = HTML.makeElement(tbody, "tr");
        new Scoring(row, this.d.scoring[i]);
    }

    // Create handlers
    var self = this;


    // Extra
    this.repaint();

    var drawDest = $(this.drawLocation);
    drawDest.setHTML("");
    drawDest.appendChild(this.root);
};
	