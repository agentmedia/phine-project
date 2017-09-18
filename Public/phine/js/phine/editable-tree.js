/**
 * Common class for editable trees
 * @param {string|jQuery} container The tree container
 * @param {function(node)} removeHandler the function for removing items
 * @param {function(anchor)} insertHandler The function for inserting items
 * @param {int} indent The indent per level; default is 15
 * @returns {editableTree} Creates a new editable tree
 */
function editableTree(container, removeHandler, insertHandler, indent)
{
    this._table = $(container).find('table');
    this._removeHandler = removeHandler ? removeHandler : this.removeNode;
    this._insertHandler = insertHandler ? insertHandler : this.insert;
    this._clipboard = $(container).find('.tree-clipboard');
    this._indent = indent ? indent : 15;
    var rootNode = this._table.find('tr:first');
    this._initIndents(rootNode);
    this._updateChildClasses(rootNode);
    this._initTogglers();
    this._initRemovers();
    this._initCutters();
    this._initInserters();
    this._initClipboard();
    this._hideInsertElements();
    var me = this;
    $(this._table).find('tr.open').each(function(idx, openNode){
        me._openNode(openNode);
    });
}

/**
 * 
 * @param {type} treeID
 * @returns {jQuery} Returns the node associated with the tree
 */
editableTree.prototype.nodeByTreeID = function(treeID)
{
    return $(this._table).find("[data-tree-id='" + treeID + "']");
};

/**
 * Shows a tree node by id
 * @param {string} treeID The data-tree-id attribute
 * @param {boolean} dontScroll True if node shall not be scrolled to
 * @returns {void}
 */
editableTree.prototype.showTreeID = function(treeID, dontScroll)
{
    this.showNode(this.nodeByTreeID(treeID), dontScroll);
};
/**
 * Show an tree node table row
 * @param {jQuery} node The node to show
 * @param {boolean} dontScroll True if node shall not be scrolled to
 * @returns {undefined}
 */
editableTree.prototype.showNode = function(node, dontScroll)
{
    var parent = this.getParent(node); 
    while(parent)
    {
        this._openNode(parent);
        parent = this.getParent(parent);
    }
    if (!dontScroll)
    {
        $('html, body').scrollTop($(node).offset().top);
    }
};

editableTree.prototype._initClipboard = function()
{
    var me = this;
    this._clipboard.hide(0);
    this._clipboard.click(function(e) {
        e.preventDefault();
        me._clipboard.attr('data-tree-cut-id', '');
        me._hideInsertElements();
  });
};


editableTree.prototype._initInserters = function ()
{
    var anchors = $(this._table).find('.tree-insert-in, .tree-insert-after');
    var me = this;
    $(anchors).each(function (idx, anchor) {
        me._addInsertHandler(anchor);
    });
};

editableTree.prototype._addInsertHandler = function (anchor)
{
    var me = this;
    $(anchor).click(function (e) {
        e.preventDefault;
        me._insertHandler(anchor);
    });
};

editableTree.prototype._insertIn = function (insertNode, targetNode)
{
    $(insertNode).attr('data-tree-parent-id', $(targetNode).attr('data-tree-id'));
    $(insertNode).insertAfter(targetNode);
    this._moveChildren(insertNode);
    this._initIndents(targetNode);
    //open target so you see item is in it
    this._openNode(targetNode);
};

editableTree.prototype._insertAfter = function (insertNode, targetNode)
{
    $(insertNode).attr('data-tree-parent-id', $(targetNode).attr('data-tree-parent-id'));
    var afterNode = targetNode;
    var children = $(this._getChildren(targetNode));
    if (children.length) {
        afterNode = children.last();
    }
    $(insertNode).insertAfter(afterNode);
    this._moveChildren(insertNode);
    this._initIndents(this.getParent(targetNode));
};

editableTree.prototype._moveChildren = function(node)
{
    var prevNode = node;
    var children = this._getChildren(node);
    var me = this;
    
    $(children).each(function(idx, child){
        $(child).insertAfter(prevNode); 
        me._moveChildren(child);
        prevNode = child;
    });
};

/**
 * Gets the recently cut out node
 * @returns {jQuery}
 */
editableTree.prototype.getInsertNode = function()
{
    return this.nodeByTreeID(this._clipboard.attr('data-tree-cut-id'));
};

/**
 * Inserts the previously cut out tree item
 * @param {jQuery} anchor The anchor clicked to perform insertion "here"
 * @returns {void}
 */
editableTree.prototype.insert = function (anchor)
{
    var insertNode = this.getInsertNode();
    var prevParent = this.getParent(insertNode);
    var targetNode = this.nodeOf(anchor);
    
    if ($(anchor).hasClass('tree-insert-in')) {
        this._insertIn(insertNode, targetNode);
        this._updateChildClasses(targetNode);
    }
    else {
       this._insertAfter(insertNode, targetNode);
    }
    this._updateChildClasses(prevParent);
    this._hideInsertElements();
};

editableTree.prototype._hideInsertElements = function()
{
    $(this._table).find('.tree-insert-in, .tree-insert-after').hide(0);
    $(this._table).find('.tree-cutter, .tree-remover, .tree-cut-hidden').show(0);
    $(this._table).find('tr').removeClass('tree-cut-out');
    this._clipboard.hide(0);
};


editableTree.prototype._initIndents = function (rootNode)
{
    var parentCount = 0;
    var parent = this.getParent(rootNode);
    while (parent)
    {
        ++parentCount;
        parent = this.getParent(parent);
    }
    var padding = this._indent * (parentCount + 1);
    $(rootNode).find('td:first, th:first').css('padding-left', padding);
    var children = this._getChildren(rootNode);
    var me = this;
    $(children).each(function(idx, node){
        me._initIndents(node);
    });
};

editableTree.prototype.getParent = function(node)
{
    var parentId = $(node).attr('data-tree-parent-id');
    if (parentId !== '')
    {
        var parent = $(this._table).find("tr[data-tree-id='" +parentId  + "']");
        return parent.length ? parent : null;
    }
    return null;
};

editableTree.prototype._initCutters = function ()
{
    var anchors = $(this._table).find('.tree-cutter');
    var me = this;
    $(anchors).each(function (idx, anchor) {
        me._addCutHandler(anchor);
    });
};

editableTree.prototype._addCutHandler = function (anchor)
{
    var me = this;
    $(anchor).click(function (e) {
        e.preventDefault;
        me.onCut(anchor);
    });
};

editableTree.prototype.onCut = function (anchor)
{
    var node = this.nodeOf(anchor);
    $(this._table).find('.tree-insert-in, .tree-insert-after').show(0);
    
    this._hideInvalidInserters(node);
    $(node).find('.tree-insert-after, .tree-insert-in').hide(0);
    $(this._table).find('.tree-remover, .tree-cutter, .tree-cut-hidden').hide(0);
    $(this._clipboard).attr('data-tree-cut-id', $(node).attr('data-tree-id'));
    $(this._clipboard).show(0);
};

editableTree.prototype._hideInvalidInserters = function(node)
{
    $(node).find('.tree-insert-after, .tree-insert-in').hide(0);
    var children = this._getChildren(node);
    var me = this;
    $(children).each(function(idx, child){
        me._hideInvalidInserters(child);
    });
};

editableTree.prototype.nodeOf = function (contentElement)
{
    return $(contentElement).parents('tr:first');
};
editableTree.prototype._initRemovers = function ()
{
    var anchors = $(this._table).find('.tree-remover');
    var me = this;
    $(anchors).each(function (idx, anchor) {
        me._addRemoveHandler(anchor);
    });
};

editableTree.prototype._addRemoveHandler = function (anchor)
{
    var me = this;
    var node = this.nodeOf(anchor);
    $(anchor).click(function (e) {
        e.preventDefault();
        me._removeHandler(node);
    });
};

editableTree.prototype._initTogglers = function ()
{
    var anchors = $(this._table).find('.tree-toggler');
    var me = this;
    $(anchors).each(function (idx, anchor) {
        me._addToggleHandler(anchor);
    });
};

editableTree.prototype._addToggleHandler = function (anchor)
{
    var me = this;
    $(anchor).click(function (e) {
        me._onToggle(e, anchor);
    });
};

editableTree.prototype._updateChildClasses = function(rootNode)
{
    var hasChildClass = 'has-children';
    if (!this._hasChildren(rootNode)){
        $(rootNode).removeClass(hasChildClass);
        return;
    }
    if (!$(rootNode).hasClass(hasChildClass)) {
        $(rootNode).addClass(hasChildClass);
    }
    var children = this._getChildren(rootNode);
    var me = this;
    $(children).each(function(idx, child){
        me._updateChildClasses(child);
    });
    
};

editableTree.prototype._onToggle = function (e, anchor)
{
    e.preventDefault();
    var node = this.nodeOf(anchor);
    if ($(node).hasClass('open') && this._hasChildren(node)) {
        this._closeNode(node);
    }
    else {
        this._openNode(node);
    }
};

editableTree.prototype._closeNode = function (node)
{
    $(node).removeClass('open');
    this._hideSubnodes(node);
};

editableTree.prototype._openNode = function (node)
{
    var me = this;
    if (!$(node).hasClass('open')) {
        $(node).addClass('open');
    }
    this._openSubnodes(node);
};

editableTree.prototype._getChildren = function (node)
{
    var nodeID = $(node).attr('data-tree-id');
    return $(this._table).find("tr[data-tree-parent-id='" + nodeID + "']");
};

editableTree.prototype._hasChildren = function (node)
{
    var next = $(node).next('tr');
    return $(next).attr('data-tree-parent-id') ===
            $(node).attr('data-tree-id');
};

editableTree.prototype._nodeClosed = function (node)
{
    if (this._hasChildren(node)) {
        $(node).removeClass('open');
    }
    $(node).data('changing', false);
};

editableTree.prototype._openSubnodes = function (parent)
{
    var children = this._getChildren(parent);
    $(children).show(0);

    var openChildren = $(children).filter('.open');
    var me = this;
    $(openChildren).each(function (idx, node) {
        me._openNode(node);
    });
};


editableTree.prototype._hideSubnodes = function (parent)
{
    var children = this._getChildren(parent);
    var me = this;
    $(children).each(function (idx, node) {
        $(node).hide(0);
        me._hideSubnodes(node);
    });
};

editableTree.prototype.removeNode = function (node)
{
    $(this._getChildren(node)).remove();
    $(node).remove();
};
