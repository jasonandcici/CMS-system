/**
 * @copyright
 * @link 
 * @create Created on 2017/10/22
 */
'use strict';

/**
 * editorAreaTool
 *
 * @author 
 * @since 1.0
 */
(function () {
    var plugName = function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var url = document.currentScript.src.split('?');
        var r = url[1].match(reg);
        if (r != null) return unescape(r[2]); return null;
    }('plugName');

    var cssRules = '.clearfix:after{content:".";display:block;height:0;clear:both;visibility:hidden;}' +
        '.clearfix{display:inline-block;}' +
        '* html .clearfix{height:1%;}' +
        '.clearfix{display:block;}' +
        '.coralueditor{position: relative;}' +
        '.coralueditor p{margin-bottom:0;}' +
        '.coralueditor-selected {z-index: 100;}' +
        '.coralueditor-selected section{position: absolute;border-color: #ff8c00;border-style: dashed;border-width:0;z-index:100;}' +
        '.coralueditor-selected .coralueditor-selected-top{border-top-width:1px; width: 100%; top: 0; }' +
        '.coralueditor-selected .coralueditor-selected-bottom{border-top-width:1px; width: 100%; bottom: 0; }' +
        '.coralueditor-selected .coralueditor-selected-top-left{border-top-width:1px; width: 5px; top: 0; left: 0; }' +
        '.coralueditor-selected .coralueditor-selected-top-right{border-top-width:1px; width: 5px; top: 0; right: 0; }' +
        '.coralueditor-selected .coralueditor-selected-bottom-left{border-top-width:1px; width: 5px; bottom: 0; left: 0; }' +
        '.coralueditor-selected .coralueditor-selected-bottom-right{border-top-width:1px; width: 5px; bottom: 0; right: 0; }' +
        '.coralueditor-selected .coralueditor-selected-left{border-left-width:1px; height: 100%; left: 0; }' +
        '.coralueditor-selected .coralueditor-selected-right{border-right-width:1px;height: 100%; right: 0; }' +
        '.coralueditor-selected .coralueditor-selected-left-bottom{border-left-width:1px; height: 5px; bottom: 0; left: 0; }' +
        '.coralueditor-selected .coralueditor-selected-left-top{border-left-width:1px; height: 5px; top: 0; left: 0; }' +
        '.coralueditor-selected .coralueditor-selected-right-bottom{border-left-width:1px; height: 5px; bottom: 0; right: 0; }' +
        '.coralueditor-selected .coralueditor-selected-right-top{border-left-width:1px; height: 5px; top: 0; right: 0; }';
    cssRules = cssRules.replace('coralueditor',plugName);

    var nod = document.createElement('style');
    nod.type='text/css';
    if (nod.styleSheet) { //ie下
        nod.styleSheet.cssText = cssRules;
    } else {
        nod.appendChild(document.createTextNode(cssRules))
    }
    document.getElementsByTagName('head')[0].appendChild(nod);
})();