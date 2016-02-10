var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.NewsListWidget = Y.Base.create('newsListWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance){
            this.reloadList();
			this.countNews();
        },
		countNews: function(){
            this.set('waiting', true);
            this.get('appInstance').newsCount(function(err, result){
                this.set('waiting', false);
	                if (!err){
	                    this.set('countNews', result.newsCount.count);
	                }
                this.renderPaginator();
            }, this);
        },
        reloadList: function(){
           	var lib = this.get('appInstance'),
        		page = lib.getPage();
        	
            this.set('waiting', true);
            
            lib.get('appInstance').newsList(page, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('newsList', result.newsList);
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
                            tp.replace('publishButton'),
                        dateline: Brick.dateExt.convert(news.get('dateline'))
                    },
                    news.toJSON()
                ]);
            });

            tp.setHTML('list', tp.replace('table', {rows: lst}));
        },
        newsPublish: function(newsid){
            var tp = this.template;
            tp.hide('publishButton.btn-' + newsid);
            tp.show('publishButton.loading-' + newsid);
            this.get('appInstance').newsPublish(newsid, function(err, result){
                tp.hide('publishButton.loading-' + newsid);
                if (!err){
                    tp.show('publishButton.published-' + newsid);
                    tp.setHTML('publishButton.published-' + newsid, Brick.dateExt.convert(result.newsItem.get('published')))
                }
            }, this);
        },
        newsRemove: function(newsid){
            this.set('waiting', true);
            this.get('appInstance').newsRemove(newsid, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.reloadList();
                }
            }, this);
        },
        renderPaginator: function(){
        	var tp = this.template,
        		countNews = this.get('countNews'),
        		countPage = Math.ceil(countNews / 20),
        		page = this.get('appInstance').getPage(),
        		lst = "",
        		start = Math.max(1, page - 2),
        		end = Math.min(+page + 2, countPage);
        		
        	for(var i = start; i <= end; i++){
        		lst += tp.replace('liPagePagin', {
        			cnt: i,
        			active: i == page ? 'active' : ''
        		});
        	}
        	
        	tp.setHTML('pag', tp.replace('paginator', {
        		lipage: lst,
        		dp: page == 1 ? 'none' : '',
        		dn: page == countPage ? 'none' : '',
        		lastpage: countPage,
        		firstpage: 1
        	}));
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget,table,row,publishButton,paginator,liPagePagin'},
            page: {value: 1},
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
                    var newsid = e.target.getData('id') | 0;
                    this.newsPublish(newsid);
                }
            },
            edit: {
                event: function(e){
                    var newsid = e.target.getData('id') | 0;
                    this.go('news.editor', newsid);
                }
            },
        	changePage: {
                event: function(e){
	                	var page =  0,
	                		type = e.target.getData('type'),
	                		lib = this.get('appInstance');
	                	
	                	switch(type){
	                		case 'prev': page = lib.getPage(); lib.setPage(--page); break;
	                		case 'next': page = lib.getPage(); lib.setPage(++page); break;
	                		case 'curent': 
	                		case 'first': 
	                		case 'last': page = e.target.getData('page'); lib.setPage(page); break;
	                	}
	                		this.renderPaginator();
	                			this.reloadList();
                }
            }
        }
    });
};