define([
  "dojo/_base/declare",
  "dojo/_base/config",
  "dojo/_base/lang",
  "dojo/on",
  "dijit/_WidgetBase",
  "sb/modal/Dialog"
], function(declare, config, lang, on, _WidgetBase, Dialog) {
  return declare([_WidgetBase], {
    postCreate: function() {
      this.inherited(arguments);
      on(this.domNode, "submit", this.onSubmit.bind(this));
      this.button = this.domNode.querySelector("button[type=submit]");
    },
    createDialog: function() {
      var dialog = new Dialog({
        form: this.domNode,
        url: config.websiteUrl + 'cart/add.xhr',
        title: 'Add to Cart'
      });
      on(dialog.domNode, "attach", this.handleXhrResponse.bind(this));
      return dialog;
    },
    onSubmit: function(e) {
      this.dialog = this.dialog || this.createDialog();
      e.preventDefault();
      this.disableButton();
      this.dialog.show();
    },
    handleXhrResponse: function() {
      this.enableButton();
      if (window.dropdownCart) {
        window.dropdownCart.refresh();
      }
    },
    disableButton: function() {
      this.button.classList.add("o-50");
      this.button.innerText = "Adding..";
      this.button.style.pointerEvents = "none";
    },
    enableButton: function() {
      this.button.classList.remove("o-50");
      this.button.innerText = "Add to Cart";
      this.button.style.pointerEvents = "";
    }
  });
});
