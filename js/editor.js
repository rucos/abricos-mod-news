/*
* @version $Id$
* @copyright Copyright (C) 2008 Abricos All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/**
 * @module News
 * @namespace Brick.mod.news
 */

var Component = new Brick.Component();
Component.requires = {
	yahoo: ['tabview', 'dragdrop'],
	mod:[
	     {name: 'news', files: ['roles.js']},
	     {name: 'sys', files: ['editor.js', 'container.js', 'data.js', 'form.js', 'calendar.js']}
    ]
};
Component.entryPoint = function(){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;

	var NS = this.namespace, 
		TMG = this.template,
		API = NS.API,
		R = NS.roles;

	if (!NS.data){
		NS.data = new Brick.util.data.byid.DataSet('news');
	}
	var DATA = NS.data;
	
	var initCSS = false,
		buildTemplate = function(w, ts){
		if (!initCSS){
			Brick.util.CSS.update(Brick.util.CSS['news']['editor']);
			delete Brick.util.CSS['news']['editor'];
			initCSS = true;
		}
		w._TM = TMG.build(ts); w._T = w._TM.data; w._TId = w._TM.idManager;
	};

	var EditorPanel = function(newsId){
		this.newsId = newsId*1 || 0;
		
		EditorPanel.superclass.constructor.call(this, {
			fixedcenter: true,
			width: '830px'
		});
	};
	YAHOO.extend(EditorPanel, Brick.widget.Panel, {
		el: function(name){ return Dom.get(this._TId['editor'][name]); },
		elv: function(name){ return Brick.util.Form.getValue(this.el(name)); },
		setelv: function(name, value){ Brick.util.Form.setValue(this.el(name), value); },
		initTemplate: function(){
			buildTemplate(this, 'editor');
			return this._T['editor'];
		},
		onLoad: function(){
			
			var TM = this._TM;
			var tabView = new YAHOO.widget.TabView(TM.getEl('editor.tabpage'));
			
			this.actionDisable(TM.getElId('editor.bcancel'));

			var Editor = Brick.widget.Editor;

			this.editorIntro = new Editor(TM.getEl('editor.bodyint'), {
				'mode': Editor.MODE_VISUAL
			});

			this.editorBody = new Editor(TM.getEl('editor.bodyman'), {
				'mode': Editor.MODE_VISUAL
			});
			
			this.pubDateTime = new Brick.mod.sys.DateInputWidget(TM.getEl('editor.pdt'), {
				'date': null,
				'showTime': true
			});
			
			// менеджер файлов
			if (Brick.componentExists('filemanager', 'api')){
				this.el('fm').style.display = '';
				this.el('fmwarn').style.display = 'none';
			}
			
			if (this.newsId > 0){
				this.initTables();
				if (DATA.isFill(this.tables)){
					this.renderElements();
				}
				DATA.onComplete.subscribe(this.dsComplete, this, true);
			}else{
				this.renderElements();
			}
		},
		
		dsComplete: function(type, args){
			if (args[0].checkWithParam('news', {id: this.newsId})){ this.renderElements(); }
		},

		initTables: function(){
			this.tables = { 'news': DATA.get('news', true) };
			this.rows = this.tables['news'].getRows({id: this.newsId});
		},
		
		renderElements: function(){
			
	 		var bsave = this.el('bsave');
	 		var bdraft = this.el('bdraft');
	 		var bpub = this.el('bpub');
	 		
	 		if (this.newsId == 0){
	 			bsave.style.display = 'none';
	 		}else{
		 		var row = this.rows.getByIndex(0);
		 		var news = row.cell;
		 		
				this.editorIntro.setContent(news['intro']);
				this.editorBody.setContent(news['body']);

	 			if (news['dp'] > 0){ bpub.style.display = 'none'; }
	 			
 				bdraft.style.display = 'none';
 		 		this.setelv('title', news['tl']);
 		 		this.setelv('srcname', news['srcnm']);
 		 		this.setelv('srclink', news['srclnk']);
 		 		this.setImage(news['img']);
 		 		this.pubDateTime.setValue(new Date(news['dp']*1000));
	 		}
	 		this.actionEnable();
		},

		destroy: function(){
			this.editorIntro.destroy();
			this.editorBody.destroy();
			
			if (this.newsId > 0){
				DATA.onComplete.unsubscribe(this.dsComplete);
			}
			EditorPanel.superclass.destroy.call(this);
		},
		
		onClick: function(el){
			var tp = this._TId['editor']; 
			switch(el.id){
			case tp['bcancel']: this.close(); return true;
			case tp['bsave']: this.save(); return true;
			case tp['bpub']: this.publish(); return true;
			case tp['bdraft']: this.draft(); return true;
			case tp['bimgsel']: this.openImage(); return true;
			case tp['bimgdel']: this.setImage(''); return true;
			}
		},
		setImage: function(imageid){
			this.imageid = imageid;
			var img = this.el('image');
			img.src = imageid ? '/filemanager/i/'+imageid+'/news.gif' : '';
		},
		openImage: function(){
			var __self = this;
			
			Brick.Component.API.fire('filemanager', 'api', 'showFileBrowserPanel', function(result){
				__self.setImage(result.file.id);
        	});
		},
		draft: function(){
	 		this.save('draft');
		},
		publish: function(){
	 		this.save('publish');
		},
		save: function(act){
			act = act || "";
			
			this.initTables();
			
			var dtpv = this.pubDateTime.getValue(),
				dtp = L.isNull(dtpv) ? null : dtpv['date'];

			var tableNews = DATA.get('news');
	 		var row = this.newsId > 0 ? this.rows.getByIndex(0) : tableNews.newRow();
	 		row.update({
	 			'tl': this.elv('title'),
	 			'intro': this.editorIntro.getContent(),
	 			'body': this.editorBody.getContent(),
	 			'srcnm': this.elv('srcname'),
	 			'srclnk': this.elv('srclink'),
	 			'img': this.imageid
	 		});
	 		if (act == "publish"){
	 			if (L.isNull(dtp)){ dtp = new Date(); }
		 		row.update({'dp': Math.round(dtp.getTime()/1000)});
	 		}else if (act == "draft"){
		 		row.update({'dp': 0});
	 		}else{
		 		row.update({'dp': L.isNull(dtp) ? 0 : Math.round(dtp.getTime()/1000)});
	 		}
	 		if (row.isNew()){
	 			this.rows.add(row);
	 		}
	 		if (!row.isNew() && !row.isUpdate()){
				this.close();
				return; 
	 		}
	 		tableNews.applyChanges();
			var tableNewsList = DATA.get('newslist');
	 		if (!L.isNull(tableNewsList)){
	 			tableNewsList.clear();
	 			DATA.get('newscount').clear();
	 		}
			DATA.request();
			this.close();
		}
	});
	
	NS.EditorPanel = EditorPanel;
	
	API.showEditorPanel = function(newsId){
		R.load(function(){
			new NS.EditorPanel(newsId);
			DATA.request(true);
		});
	};
	
};
