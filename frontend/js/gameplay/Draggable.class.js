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

Draggable.prototype.addTarget = function(data){
	var targetCoordinates;
	if(data["coordinates"] !== undefined){
		targetCoordinates = data["coordinates"];
	}else if(data["target"] !== undefined){ // Add target coordinates based on a target's bounding box
		if(target.get){
			target = target.get();
		}
		var bbox = target.getBBox();
		targetCoordinates = {
			x: bbox.x,
			y: bbox.y,
			width: bbox.width,
			height: bbox.height
		};
	}else{
		throw "Must pass a target element or coordinates";
	}

	var successCallback = data["success"];
	var failureCallback = data["failure"];

	if(failureCallback === undefined){
		// If there is no failure callback, make the element snap back to where it was
		failureCallback = function(element, target){
			if(this.attributeInit.x !== 0 || this.attributeInit.y !== 0){
				if($.svg){
					$(this.element).animate({
						svgX: this.attributeInit.x,
						svgY: this.attributeInit.y
					});
				}else{
					this.element.setAttributeNS(null, "x", this.attributeInit.x);
					this.element.setAttributeNS(null, "y", this.attributeInit.y);
				}
			}

			if(this.transformInit.x !== 0 || this.transformInit.y !== 0){
				if($.svg){
					// Animate if just transform is set
					$(this.element).animate({
						svgTransform: "translate("+this.transformInit.x+","+this.transformInit.y+")"
					});
				}else{
					this.element.setAttributeNS(null, "transform", "translate("+this.transformInit.x+","+this.transformInit.y+")");
				}
			}
		}
	}

	var targetObj = {
		coordinates: targetCoordinates,
		success: successCallback,
		failure: failureCallback,
	};

	if(this.targets.length === 0){
		this.element.addEventListener("mousedown", this.mousedownCallback, false);
	}


	this.targets[this.targets.length] = targetObj;
}

Draggable.prototype.removeTarget = function(data){
	var targetCoordinates;
	if(data["coordinates"] !== undefined){
		targetCoordinates = data["coordinates"];
	}else if(data["target"] !== undefined){ // Add target coordinates based on a target's bounding box
		if(target.get){
			target = target.get();
		}
		var bbox = target.getBBox();
		targetCoordinates = {
			x: bbox.x,
			y: bbox.y,
			width: bbox.width,
			height: bbox.height
		};
	}else{
		throw "Must pass a target element or coordinates";
	}

	for(var i = 0; i < this.targets.length; ++i){
		console.log(this.targets[i]);
		if(
			this.targets[i].coordinates.x === targetCoordinates.x &&
			this.targets[i].coordinates.y === targetCoordinates.y &&
			this.targets[i].coordinates.width === targetCoordinates.width &&
			this.targets[i].coordinates.height === targetCoordinates.height
			){
			this.targets.splice(i,1);
			break;
		}
	}

	if(this.targets.length === 0){
		console.log(this);
		this.element.removeEventListener("mousedown", this.mousedownCallback, false);
	}
}

Draggable.prototype.setEvents = function(){

	// Find the top SVG element to set the mousemove and mouseup events on
	if(Draggable.prototype.searchElement === undefined){
		var searchElement;
		searchElement = this.element;
		while(searchElement.tagName.toLowerCase() !== "svg"){
			searchElement = searchElement.parentNode;
		}
		Draggable.prototype.searchElement = searchElement;

		searchElement.addEventListener("mousemove", this.mousemoveCallback, false);
		searchElement.addEventListener("mouseup", this.mouseupCallback, false);
	}

	this.element.addEventListener("mousedown", this.mousedownCallback, false);
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
	var eleX = parseInt(this.getAttributeNS(null, "x"));
	var eleY = parseInt(this.getAttributeNS(null, "y"));
	if(Number.isNaN(eleX)){
		eleX = 0;
	}
	if(Number.isNaN(eleY)){
		eleY = 0
	}

	which.transformInit = {
		x: initX,
		y: initY
	};

	which.attributeInit = {
		x: eleX,
		y: eleY
	};
}

Draggable.prototype.mousemoveCallback = function(event){
	var which = Draggable.prototype.draggingObject;
	if(which !== undefined){
		// Compute new x/y values
		if(!(which.transformInit.x == 0 && which.transformInit.y == 0)){
			var newCoordinates = {
				x: (event.clientX-which.cursorInit.x) + which.transformInit.x + which.attributeInit.x,
				y: (event.clientY-which.cursorInit.y) + which.transformInit.y + which.attributeInit.y
			};
			which.element.setAttributeNS(null, "transform", "translate("+newCoordinates.x+","+newCoordinates.y+")");
		}else{
			var newCoordinates = {
				x: (event.clientX-which.cursorInit.x) + which.attributeInit.x,
				y: (event.clientY-which.cursorInit.y) + which.attributeInit.y
			};
			which.element.setAttributeNS(null, "x", newCoordinates.x);
			which.element.setAttributeNS(null, "y", newCoordinates.y);
		}
	}
}

Draggable.prototype.mouseupCallback = function(event){
	var which = Draggable.prototype.draggingObject;
	if(which !== undefined){
		Draggable.prototype.draggingObject = undefined;
		which.dragging = false;

		var newCoordinates = {
			x: (event.clientX-which.cursorInit.x) + which.transformInit.x + which.attributeInit.x,
			y: (event.clientY-which.cursorInit.y) + which.transformInit.y + which.attributeInit.y
		};
		
		// For each which.targets, test coordinates and call either each success or failures
		for(var i = 0; i < which.targets.length; ++i){
			if(newCoordinates.x + which.bbox.width/2 > which.targets[i].coordinates.x && newCoordinates.x + which.bbox.width/2 < which.targets[i].coordinates.x + which.targets[i].coordinates.width){
				if(newCoordinates.y + which.bbox.height/2 > which.targets[i].coordinates.y && newCoordinates.y + which.bbox.height/2 < which.targets[i].coordinates.y + which.targets[i].coordinates.height){
					which.targets[i].success.call(which, which.element, which.targets[i]);
					continue;
				}
			}
			which.targets[i].failure.call(which, which.element, which.targets[i]);
		}
	}
}
