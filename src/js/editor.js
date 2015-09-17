var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['editor.js', 'container.js', 'data.js', 'old-form.js']},
        {name: 'widget', files: ['calendar.js']},
        {name: '{C#MODNAME}', files: ['newsList.js', 'lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    var DATA = NS.data || (NS.data = new Brick.util.data.byid.DataSet('news'));

    NS.NewsEditorWidget = Y.Base.create('newsEditorWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            var tp = this.template;

            var Editor = Brick.widget.Editor;

            var instance = this;
            setTimeout(function(){ // bugfix
                instance.editorIntro = new Editor(tp.gel('bodyint'), {
                    'mode': Editor.MODE_VISUAL
                });

                instance.editorBody = new Editor(tp.gel('bodyman'), {
                    'mode': Editor.MODE_VISUAL
                });
            }, 300);

            this.pubDateTime = new Brick.mod.widget.DateInputWidget(tp.gel('pdt'), {
                'date': null,
                'showTime': true
            });

            // менеджер файлов
            if (Brick.componentExists('filemanager', 'api')){
                tp.toggleView(true, 'fm', 'fmwarn');
            }

            if (this.get('newsid') > 0){
                this.initTables();
                if (DATA.isFill(this.tables)){
                    this.renderElements();
                }
                DATA.onComplete.subscribe(this.dsComplete, this, true);
                DATA.request(true);
            } else {
                this.renderElements();
            }
        },
        destructor: function(){
            this.editorIntro.destroy();
            this.editorBody.destroy();

            if (this.get('newsid') > 0){
                DATA.onComplete.unsubscribe(this.dsComplete);
            }
        },
        dsComplete: function(type, args){
            console.log(arguments);
            if (args[0].checkWithParam('news', {id: this.get('newsid')})){
                this.renderElements();
            }
        },
        initTables: function(){
            this.tables = {'news': DATA.get('news', true)};
            this.rows = this.tables['news'].getRows({id: this.get('newsid')});
        },
        renderElements: function(){
            var tp = this.template,
                newsid = this.get('newsid');

            tp.toggleView(newsid > 0, 'bsave', 'bpub,bdraft');
            if (newsid > 0){
                var row = this.rows.getByIndex(0);
                var news = row.cell;

                this.editorIntro.setContent(news['intro']);
                this.editorBody.setContent(news['body']);

                if (news['dp'] > 0){
                    tp.hide('bpub');
                }

                tp.hide('bnewsDraft');
                tp.setValue({
                    'title': news['tl'],
                    'srcname': news['srcnm'],
                    'srclink': news['srclnk']
                });
                this.setImage(news['img']);

                this.pubDateTime.setValue(news['dp'] == 0 ? null : new Date(news['dp'] * 1000));
            }

            tp.one('bcancel').set('disabled', '');
        },
        newsDraft: function(){
            this.save('newsDraft');
        },
        newsPublish: function(){
            this.save('newsPublish');
        },
        save: function(act){
            act = act || "";

            this.initTables();

            var tp = this.template,
                newsid = this.get('newsid'),
                dtp = this.pubDateTime.getValue();

            var tableNews = DATA.get('news');
            var row = newsid > 0 ? this.rows.getByIndex(0) : tableNews.newRow();
            row.update({
                'tl': tp.getValue('title'),
                'intro': this.editorIntro.getContent(),
                'body': this.editorBody.getContent(),
                'srcnm': tp.getValue('srcname'),
                'srclnk': tp.getValue('srclink'),
                'img': this.imageid
            });
            if (act == "newsPublish"){
                if (!dtp){
                    dtp = new Date();
                }
                row.update({'dp': Math.round(dtp.getTime() / 1000)});
            } else if (act == "newsDraft"){
                row.update({'dp': 0});
            } else {
                row.update({'dp': !dtp ? 0 : Math.round(dtp.getTime() / 1000)});
            }
            if (row.isNew()){
                this.rows.add(row);
            }
            if (!row.isNew() && !row.isUpdate()){
                this.go('manager.view');
                return;
            }
            tableNews.applyChanges();
            var tableNewsList = DATA.get('newslist');
            if (tableNewsList){
                tableNewsList.clear();
                DATA.get('newscount').clear();
            }
            DATA.request();
            this.go('manager.view');
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget'},
            newsid: {value: 0}
        },
        CLICKS: {
            cancel: {
                event: function(){
                    this.go('manager.view');
                }
            },
            save: 'save',
            publish: 'newsPublish',
            draft: 'newsDraft'
        }
    });

    NS.NewsEditorWidget.parseURLParam = function(args){
        return {
            newsid: args[0] | 0
        };
    };

};
