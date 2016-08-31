var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js', 'pagination.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys,
        WID = Brick.mod.widget;

    NS.NewsListWidget = Y.Base.create('newsListWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance){
        	var tp = this.template,
        		nameFilter = appInstance.get('filter');
        	
        	
            	this.pagination = new NS.PaginationWidget({
            		srcNode: tp.gel('pag'),
                    parent: this
            	});
            	
                if(nameFilter === 'all'){
                	this.reloadList();	
                } else {
                	this.renderButton(nameFilter);
                	this.reloadFilterList(nameFilter);
                }
        },
        destructor: function(){
            if (this.pagination){
                this.pagination.destroy();
            }
        },
        reloadList: function(){
        	var lib = this.get('appInstance'),
        		page = lib.getPage();
        	
            this.set('waiting', true);
            lib.newsList(page, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('newsList', result.newsList);
                    
                    this.paginationSet(result.newsCount.count, result.newsCount.limit);
                }
                this.renderList();
            }, this);
            	
        },
        reloadFilterList: function(nameFilter){
        	var lib = this.get('appInstance'),
    			page = lib.getPage(),
    			obj = {
        			page: page,
        			nameFilter: nameFilter
        		};
        	
        	this.set('waiting', true);
            lib.newsFilterList(obj, function(err, result){
            	this.set('waiting', false);
            	if (!err){
            		var size = result.newsFilterList.size();
                	
            		if(!size && page > 1){
            			lib.setPage(page - 1);
            			this.reloadFilterList(nameFilter);
            		}
            		
            		this.set('newsList', result.newsFilterList);
                	this.paginationSet(result.newsCount.count, result.newsCount.limit);
                }
                this.renderList();
            }, this);
            	
        },
        renderList: function(){
            var newsList = this.get('newsList');
            if (!newsList){
                return;
            }

            var tp = this.template,
                lst = "";

            newsList.each(function(news){
                lst += tp.replace('row', [
                    {
                        published: news.get('published') ?
                            Brick.dateExt.convert(news.get('published')) :
	                            tp.replace('publishButton', {
	                            	act: news.get('deldate') ? 'Восстановить' : 'Опубликовать'
	                            }),
                        dateline: Brick.dateExt.convert(news.get('dateline')),
                        danger: news.get('deldate') ? 'danger' : news.get('published') ? '' : 'warning'
                    },
                    news.toJSON()
                ]);
            });
            
            this.pagination.renderPaginator();	
            tp.setHTML('list', tp.replace('table', {rows: lst}));
        },
        newsPublish: function(newsid, act, tr){
            var tp = this.template,
            	data = {
            		newsid: newsid,
            		act: act == 'Восстановить' ? 1 : 0 //1: Восстановить, 0:Опубликовать
            	};
            tp.hide('publishButton.btn-' + newsid);
            tp.show('publishButton.loading-' + newsid);
            
            this.get('appInstance').newsPublish(data, function(err, result){
            	var news = result.newsItem;
                tp.hide('publishButton.loading-' + newsid);
                if (!err){
                	tp.show('publishButton.published-' + newsid);
	                	if(news.get('published')){
			                tp.setHTML('publishButton.published-' + newsid, Brick.dateExt.convert(news.get('published')));
			                tr.className = '';
	            		} else {
	            			tp.setHTML('publishButton.published-' + newsid, tp.replace('publishButton', [{
                            		act: 'Опубликовать'
	            			}, news.toJSON()]));
	            			tr.className = 'warning';
	            		}
                }
            }, this);
        },
        newsRemove: function(newsid){
        	var lib = this.get('appInstance'),
        		filter = lib.get('filter');
        		
            this.set('waiting', true);
            lib.newsRemove(newsid, function(err, result){
                this.set('waiting', false);
                if (!err){
                	if(filter === 'all'){
                		this.reloadList();
                	} else {
                		this.reloadFilterList(filter);
                	}
                    
                }
            }, this);
        },
        renderButton: function(nameFilter){
        	var tp = this.template;
        	
        	if(nameFilter === 'all'){
        		tp.removeClass('unPublic', 'btn-primary');
        		tp.removeClass('remove', 'btn-primary');
        		tp.addClass('all', 'hide');
        	} else {
        		switch(nameFilter){
        			case "unPublic":
        				tp.addClass(nameFilter, 'btn-primary');
        				tp.removeClass('remove', 'btn-primary');
        					break;
        			case "remove":
        				tp.addClass(nameFilter, 'btn-primary');
        				tp.removeClass('unPublic', 'btn-primary');
        					break;
        		}
        		tp.removeClass('all', 'hide');
        	}
        },
        paginationSet: function(countRow, limit){
            this.pagination.set('countRow', countRow);
            this.pagination.set('limit', limit);
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget,table,row,publishButton'},
            newsList: {value: null}
        },
        CLICKS: {
            'remove-show': {
                event: function(e){
                    var newsid = e.target.getData('id') | 0;
                    this.template.toggleView(true, 'row.removegroup-' + newsid, 'row.remove-' + newsid);
                }
            },
            'remove-cancel': {
                event: function(e){
                    var newsid = e.target.getData('id') | 0;
                    this.template.toggleView(false, 'row.removegroup-' + newsid, 'row.remove-' + newsid);
                }
            },
            remove: {
                event: function(e){
                    var newsid = e.target.getData('id') | 0;
                    this.newsRemove(newsid);
                }
            },
            publish: {
                event: function(e){
                    var targ = e.target,
                    	newsid = targ.getData('id') | 0,
                    	act = targ.getData('act'),
                    	tr = targ.getDOMNode().parentNode.parentNode;//строка в таблице
                    
                    this.newsPublish(newsid, act, tr);
                }
            },
            edit: {
                event: function(e){
                    var newsid = e.target.getData('id') | 0;
                    this.go('news.editor', newsid);
                }
            },
            filter: {
            	event: function(e){
            		var nameFilter = e.target.getData('name');
            		
            		this.get('appInstance').set('filter', nameFilter);
            		
            		if(nameFilter === 'all'){
            			this.reloadList();
            		} else {
            			this.reloadFilterList(nameFilter);
            		}
            		
            		this.renderButton(nameFilter);
            	}
            }
        }
    });
};