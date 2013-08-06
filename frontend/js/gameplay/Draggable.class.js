/*
	targetObject = {
		coordinates
			x
			y
			width
			height
		target
		success
		failure
	}
 */


/**
 * Facilitates the dragging of an object into a set of coordinates.
 * @param DOMElement/Object parameter Either the element to drag or an object
 * with the fields "element", holding the element, and "object", holding an object
 * that can be accessed from the success/failure callbacks as "this.element" and 
 * "this.object", respectively.
 */
function Draggable(parameter){
	if(parameter.element){
		this.element = parameter.element;
		this.object = parameter.object;
		if(parameter.bbox){
			this.bbox = parameter.bbox;
		}
	}else{
		this.element = parameter;
	}


	this.dragging = false;
	this.element.setAttributeNS(null, "data-draggableIndex", Draggable.prototype.instances.length);
	Draggable.prototype.instances[Draggable.prototype.instances.length] = this;

	// Stores the target coordinates, the success callback, and the failure callback
	// for each target for the object
	this.targets = [];

	this.eventsSet = false;
}

Draggable.prototype.instances = [];
Draggable.prototype.searchElement = undefined;
Draggable.prototype.draggingObject = undefined;


Draggable.prototype.addTarget = function(data){
	
	// Not doing this onload to fix a bug in Firefox where getBBox can't be called before item is rendered
	if(this.bbox === undefined){
		this.bbox = this.element.getBBox();
	}

	if(!this.eventsSet){
		this.setEvents();
	}

	var targetCoordinates;
	if(data["coordinates"] !== undefined && data["target"] !== undefined){
		throw "Must only pass either a coordinates value or a target value";
	}else if(data["coordinates"] !== undefined){
		targetCoordinates = data["coordinates"];
	}else if(data["target"] !== undefined){ // Add target coordinates based on a target's bounding box
		if(data["target"].get){
			data["target"] = data["target"].get();
		}

		var bbox = data["target"].getBBox();

		// Get coordinates of transform translate as well
		var transform = data["target"].getAttributeNS(null, "transform");
		var transformX = 0;
		var transformY = 0;
		if(transform !== null){
			transformX = parseInt(transform.substring("translate(".length + transform.indexOf("translate("), transform.indexOf(",")));
			transformY = parseInt(transform.substring(transform.indexOf(",")+1, transform.indexOf(")")));	
			if(isNaN(transformX)){
				transformX = 0;
			}
			if(isNaN(transformY)){
				transformY = 0
			}
		}

		targetCoordinates = {
			x: bbox.x+transformX,
			y: bbox.y+transformY,
			width: bbox.width,
			height: bbox.height
		};
	}else{
		throw "Must pass a target element or coordinates";
	}
	

	var successCallback = data["success"];
	var failureCallback;

	if(data["success"] === "snapback"){
		successCallback = this.snapback;
	}else if(data["success"][0] !== undefined){
		// Array-like - multiple callbacks
		successCallback = function(){
			for(var i = 0; i < data["success"].length; ++i){
				if(data["success"][i] === "snapback"){
					this.snapback();
				}else{
					data["success"][i]();
				}
			}
		}
	}else{
		successCallback = data["success"];
	}


	if(data["failure"] === undefined || data["failure"] === "snapback"){
		// If there is no failure callback, make the element snap back to where it was
		failureCallback = this.snapback;
	}else if(data["failure"][0] !== undefined){
		// Array-like - multiple callbacks
		failureCallback = function(){
			for(var i = 0; i < data["failure"].length; ++i){
				if(data["failure"][i] === "snapback"){
					this.snapback();
				}else{
					data["failure"][i]();
				}			
			}
		}
	}else{
		failureCallback = data["failure"];
	}

	var targetObj = {
		coordinates: targetCoordinates,
		success: successCallback,
		failure: failureCallback,
		target: data["target"]
	};

	if(this.targets.length === 0){
		this.element.addEventListener("mousedown", this.mousedownCallback, false);
	}


	this.targets[this.targets.length] = targetObj;
}

Draggable.prototype.snapback = function(){
	// Function to snap back dragged object to where it started
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

Draggable.prototype.removeTarget = function(data){
	var targetCoordinates, target;
	if(data["coordinates"] !== undefined && data["target"] !== undefined){
		throw "Must only pass either a coordinates value or a target value";
	}else if(data["coordinates"] !== undefined){
		targetCoordinates = data["coordinates"];
	
		for(var i = 0; i < this.targets.length; ++i){
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

	}else if(data["target"] !== undefined){ // Add target coordinates based on a target's bounding box
		if(data["target"].get){
			data["target"] = data["target"].get();
		}
		target = data["target"];

		for(var i = 0; i < this.targets.length; ++i){
			console.log(this.targets[i]);
			if(target === this.targets[i].target){
				this.targets.splice(i,1);
				break;
			}
		}
	}else{
		throw "Must pass a target element or coordinates";
	}

	if(this.targets.length === 0){
		console.log(this);
		this.element.removeEventListener("mousedown", this.mousedownCallback, false);
	}
}

Draggable.prototype.removeAllTargets = function(){
	this.targets = [];
	this.element.removeEventListener("mousedown", this.mousedownCallback, false);
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
	if(isNaN(initX)){
		initX = 0;
	}
	if(isNaN(initY)){
		initY = 0
	}

	var eleX = parseInt(this.getAttributeNS(null, "x"));
	var eleY = parseInt(this.getAttributeNS(null, "y"));
	if(isNaN(eleX)){
		eleX = 0;
	}
	if(isNaN(eleY)){
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

	// Bring item dragging to front of DOM
	var parentNode = which.element.parentNode;
	parentNode.removeChild(which.element);
	parentNode.appendChild(which.element);
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
	
	if(which === undefined){
		return;
	}

	which.bbox = which.element.getBBox();
	if(which.bbox.width == 0 && which.bbox.height == 0){
		which.bbox = which.element.childNodes[0].getBBox();
	}

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
