Mage.core.PanelImages = function(region, config) {
    this.region = region;
    this.tbItems = new Ext.util.MixedCollection();
    Ext.apply(this, config);
    this.panel = this.region.add(new Ext.ContentPanel(Ext.id(), {
        autoCreate : true,
       	autoScroll : true,
       	fitToFrame : true,   
       	background : config.background || true,    	
        title : this.title || 'Images'
    }));
    
    this.panel.on('activate', this._loadActions, this);
    this.panel.on('deactivate', this._unLoadActions, this);
    this._build();
};

Ext.extend(Mage.core.PanelImages, Mage.core.Panel, {
    update : function(config) {
        if (this.region.getActivePanel() === this.panel) {
            this.imagesView.store.proxy.getConnection().url = this.storeUrl;
            this.imagesView.store.load();
        }
    },
    
    _loadActions : function() {
        if (this.toolbar) {
            if (this.tbItems.getCount() == 0) {
                var disabled = false
                if (this.imagesView) {
                    disabled = this.imagesView.store.getCount() <= 0;
                }
                this.tbItems.add('image_sep', new Ext.Toolbar.Separator());
                this.tbItems.add('image_delete', new Ext.Toolbar.Button({
                    text : 'Delete Image',
                    disabled : disabled,
                    handler : this._onDeleteImage,
                    scope : this
                }));
                
                this.tbItems.each(function(item){
                    this.toolbar.add(item);
                }.createDelegate(this));
            } else {
                this.tbItems.each(function(item){
                    item.show();
                }.createDelegate(this));
            }
        }
    },
    
    _unLoadActions : function() {
        this.tbItems.each(function(item){
            item.hide();
        }.createDelegate(this));
    },
    
    
    _build : function() {
        this.containerEl = this._buildTemplate();
        var formContainer = this.containerEl.createChild({tag : 'div'});        
        var viewContainer = this.containerEl.createChild({tag : 'div', cls:'x-productimages-view'});
        
        this._buildForm(formContainer);
        this._buildImagesView(viewContainer);  
    },
    
    _buildForm : function(formContainer) {
        this.frm = new Mage.form.JsonForm({
            fileUpload : this.form.config.fileupload,
            method : this.form.config.method,
            action : this.form.config.action,
            metaData : this.form.elements,
            waitMsgTarget : formContainer
        }); 
        
        this.frm.render(formContainer);       
        
        this.frm.on({
            actionfailed : function(form, action) {
                Ext.MessageBox.alert('Error', 'Error');
            },
            actioncomplete : function(form, action) {
                this.imagesView.store.add(new this.dataRecord(action.result.data));
                this.imagesView.refresh();
                form.reset();
            }.createDelegate(this)
        });
     },
     
     _onDeleteImage : function(button, event) {
         var record = this.imagesView.store.getAt(this.imagesView.selections[0].nodeIndex);
         this.imagesView.store.remove(record);
     },
    
    _buildImagesView : function(viewContainer) {
        
        this.dataRecord = Ext.data.Record.create([
            {name: 'id'},
            {name: 'src'},
            {name: 'alt'},
            {name: 'description'}
        ]);

        var dataReader = new Ext.data.JsonReader({
            root: 'items',
            totalProperty: 'totalRecords'
        }, this.dataRecord);
    
    
        var store = new Ext.data.Store({
            proxy: new Ext.data.HttpProxy({url: this.storeUrl}),
            reader: dataReader
        });
        
        store.on('load', function() {
            if (this.imagesView) {
                this.imagesView.select(0);
            }
        }.createDelegate(this));
        
        var viewTpl = new Ext.Template('<div class="thumb-wrap" id="{name}">' +
                '<div id="{id}" class="thumb"><img src="{src}" alt="{alt}"></div>' +
                '<span>{description}</span>' +
                '</div>');
        viewTpl.compile();
                   
        this.imagesView = new Ext.View(viewContainer, viewTpl,{
            singleSelect: true,
            store: store,
            emptyText : 'Images not found'
        });
        
        this.imagesView.on('beforeselect', function(view){
            return view.store.getCount() > 0;
        });
        this.imagesView.on('selectionchange', function(view, selections){
            if (this.tbItems.get('image_delete')) {
                if (selections.length) {
                    this.tbItems.get('image_delete').enable();
                } else {
                    this.tbItems.get('image_delete').disable();
                }
            }
        }.createDelegate(this));
        
        store.load();
    },
    
    _buildTemplate : function() {
        this.tpl = new Ext.Template('<div>' +
            '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>' +
            '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc">' +
            '<div id="{containerElId}">' +
            '</div>' +
            '</div></div></div>' +
            '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>' +
            '</div>');
       containerElId = Ext.id();
       var tmp = this.tpl.append(this.panel.getEl(), {containerElId : containerElId}, true);
       return Ext.get(containerElId);
    }
})