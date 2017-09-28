
	//find elements
	_get = function(e,c){
		var _isClass = e.indexOf(".")>=0,_isId = e.indexOf("#")>=0,newEle = e.substring(1);
		//get frist argument
		if(_isClass&&_isId) return;
		if(_isId){
			var obj = typeof newEle=="string"?document.getElementById(newEle):false;
		}else if(_isClass){
			var obj = _class(e);
		}else{
			return;
		}
		//get class
		function _class(c,o){
			var oDom = o?o:document,
				cParent = oDom.getElementsByTagName("*"),
				classAry = [],
				leng = cParent.length;
			for(var i=0;i<leng;i++){
				allC = cParent[i].className;	
				if(allC.match("\\b"+newEle+"(?:$|[^\w\-])")){
					classAry.push(cParent[i])
				}
			}	
			return classAry.length==1?classAry[0]:classAry;
		}
		//second argument
		var _isArr = obj.constructor==Array;
		if(!!c){
			if(_isArr){
				for(var j=0,len=obj.length;j<len;j++){
					var ele = obj[j].getElementsByTagName(c);
					obj[j] = ele.length==1?ele[0]:ele;
				}
			}else{
				obj = obj.getElementsByTagName(c);
			}	
		}	
		return obj.length==1?obj[0]:obj;
	}
	//even
	function Roll(){this.initial.apply(this,arguments)}
	Roll.prototype = {
		initial:function(o,l,r,s){
			var _this = this;
			this.obj = o;
			this.child = o.children;
			this.timeId = null;
			this.interval = null;
			this.funTime = null;
			this.set(s||{});
			this.oAttr = !!this.s.dir?this.child[0].offsetHeight:this.child[0].offsetWidth;
			this.dirNo = !!this.s.dir?"top":"left";
			l.onclick = function(){_this.prev();_this.s.auto = false;};
			r.onclick = function(){_this.next();_this.s.auto = false;};
			o.onmouseover = function(){_this.s.auto = false;clearInterval(_this.funTime);}
			this.s.auto&&(this.next(),o.onmouseout = l.onmouseout = r.onmouseout = function(){_this.funTime = setTimeout(function(){_this.s.auto = true;_this.next()},1500)});
		},
		set:function(v){
			this.s = {
				dir:0,//滚动方向，0为横向滚动，1为纵向滚动
				auto:true//是否自动滚动
			}
			for(c in v){this.s[c] = v[c]};
		},
		prev:function(){
			this.obj.insertBefore(this.child[this.child.length-1],this.obj.firstChild);
			this.obj.style[this.dirNo] = -this.oAttr+"px";
			this.moverIt(0);
		},	
		next:function(){
			this.moverIt(-this.oAttr,function(){
				var dir = !!this.s.dir?"top":"left";
				this.obj.appendChild(this.child[0]);
				this.obj.style[this.dirNo] = 0;
			});			
		},
		moverIt:function(i,callBack){
			var _this = this;
			clearInterval(this.timerId);
			clearInterval(_this.funTime)
			clearInterval(this.interval);
			this.timerId = setInterval(function(){
				var dir = !!_this.s.dir?_this.obj.offsetTop:_this.obj.offsetLeft,
					iSpeed = (i-dir)/2;
				iSpeed = iSpeed > 0 ? Math.ceil(iSpeed) : Math.floor(iSpeed);
				dir==i?(clearInterval(_this.timerId),callBack&&callBack.apply(_this),!!_this.s.auto&&(_this.interval=setTimeout(function(){_this.next();},5000))):_this.obj.style[_this.dirNo] = iSpeed + dir + "px"
			},30);
		}	
	}
	
	//marquee
	var menu = _get(".prize-con101","ul"),lBun = _get(".l-bun"),rBun = _get(".r-bun");
	for(var i=0,leng=menu.length;i<leng;i++){
		var mq = new Roll(menu[i],lBun[i],rBun[i],{dir:i%2?1:0}),mq = null;//实例化，当板块为偶数的时候像左边滚动，反之向上
	}
