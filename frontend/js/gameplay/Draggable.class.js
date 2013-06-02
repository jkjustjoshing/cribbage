/**
 * Facilitates the dragging of an object into a set of coordinates.
 * @param DOMElement element The element to drag
 */
function Draggable(element){
	this.element = element;

	this.bbox = this.element.getBBox();

	this.dragging = false;
	this.element.setAttributeNS(null, "data-draggableIndex", Draggable.prototype.instances.length);
	Draggable.prototype.instances[Draggable.prototype.instances.length] = this;

	// Stores the target coordinates, the success callback, and the failure callback
	// for each target for the object
	this.targets = [];

	this.setEvents();
}

Draggable.prototype.instances = [];
Draggable.prototype.searchElement = undefined;
Draggable.prototype.draggingObject = undefined;

Draggable.prototype.addTarget = function(targetCoordinates, successCallback, failureCallback){
	this.targets[this.targets.length] = {
		coordinates: targetCoordinates,
		success: successCallback,
		failure: failureCallback
	};
}

Draggable.prototype.setEvents = function(){

	// Find the top SVG element to set the mousemove and mouseup events on
	var searchElement;
	if(Draggable.prototype.searchElement === undefined){
		searchElement = this.element;
		while(searchElement.tagName.toLowerCase() !== "svg"){
			searchElement = searchElement.parentNode;
		}
		Draggable.prototype.searchElement = searchElement;
	}else{
		searchElement = Draggable.prototype.searchElement;
	}

	this.element.addEventListener("mousedown", this.mousedownCallback, false);
	searchElement.addEventListener("mousemove", this.mousemoveCallback, false);
	searchElement.addEventListener("mouseup", this.mouseupCallback, false);
	
}

Draggable.prototype.mousedownCallback = function(event){
	var draggingIndex = parseInt(this.getAttributeNS(null, "data-draggableIndex"));
	var which = Draggable.prototype.instances[draggingIndex];
	Draggable.prototype.draggingObject = which;
	which.dragging = true;
	which.cursorInit = {
		x: event.clientX,
		y: event.clientY
	};

	var transform = this.getAttributeNS(null, "transform");
	var initX = parseInt(transform.substring("translate(".length + transform.indexOf("translate("), transform.indexOf(",")));
	var initY = parseInt(transform.substring(transform.indexOf(",")+1, transform.indexOf(")")));	
	if(Number.isNaN(initX)){
		initX = 0;
	}
	if(Number.isNaN(initY)){
		initY = 0
	}
	which.elementInit = {
		x: initX,
		y: initY
	};
}

Draggable.prototype.mousemoveCallback = function(event){
	var which = Draggable.prototype.draggingObject;
	if(which !== undefined){
		// Compute new x/y values
		var newCoordinates = {
			x: (event.clientX-which.cursorInit.x) + which.elementInit.x,
			y: (event.clientY-which.cursorInit.y) + which.elementInit.y
		};

		which.element.setAttributeNS(null, "transform", "translate("+newCoordinates.x+","+newCoordinates.y+")");
	}
}

Draggable.prototype.mouseupCallback = function(event){
	var which = Draggable.prototype.draggingObject;
	if(which !== undefined){
		Draggable.prototype.draggingObject = undefined;
		which.dragging = false;

		var newCoordinates = {
			x: (event.clientX-which.cursorInit.x) + which.elementInit.x,
			y: (event.clientY-which.cursorInit.y) + which.elementInit.y
		};
		
		// For each which.targets, test coordinates and call either each success or failures
		for(var i = 0; i < which.targets.length; ++i){
			if(newCoordinates.x + which.bbox.width/2 > which.targets[i].x && newCoordinates.x + which.bbox.width/2 < which.targets[i].x + which.targets[i].width){
				if(newCoordinates.y + which.bbox.height/2 > which.targets[i].y && newCoordinates.y + which.bbox.height/2 < which.targets[i].y + which.targets[i].height){
					which.targets[i].success();
					continue;
				}
			}
			which.targets[i].failure();
		}
	}
}
