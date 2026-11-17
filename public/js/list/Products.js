define([
  "dojo/_base/declare",
  "dojo/_base/config",
  "dojo/_base/lang",
  "sb/grid/List",
  "dgrid/Keyboard",
  "sb/grid/_StoreMixin",
  "sb/grid/_QueryMixin",
  "dojo/on",
  "put-selector/put",
  "dojo/query",
  "sb/form/Number"
], function(declare, config, lang, List, Keyboard, _StoreMixin, _QueryMixin, on, put, query, Number) {
  return declare([List, Keyboard, _StoreMixin, _QueryMixin], {
    cellNavigation: false,
    tabableHeader: false,
    model: "product_lines",
    action: "cart",
    shouldTrackCollection: false,
    noDataMessage: "No items",
    showFooter: true,
    addSku: false,
    buildRendering: function() {
      query("[data-cart-attach]").forEach(lang.hitch(this, function(node) {
        this[node.getAttribute("data-cart-attach")] = node;
      }));
      if (this.totalsNode) {
        this.showFooter = false;
      }
      this.inherited(arguments);
      if (!this.totalsNode) {
        this.totalsNode = put(this.footerNode, "div");
      }
    },
    postCreate: function() {
      this.inherited(arguments);
      this.attachMetadataHandler();
    },
    attachMetadataHandler: function() {
      this.on("dgrid-refresh-complete", lang.hitch(this, function(evt) {
        evt.grid._renderedCollection.results.response.then(lang.hitch(this, "renderTotals"));
      }));
    },
    renderRow: function(product, options){
      var node = put("div.cf.pv3.flex-ns");
      var imageNode = put(node, "div.tc.flex.items-center.justify-center.mb2[style=min-width:100px;max-width:100px]");
      if (product.photos && product.photos.length) {
        put(imageNode, "img.tc.flex.items-center.justify-center.ns[src=$]", product.photos[0]);
      } else {
        put(imageNode, "div.h3.flex.items-center.justify-center span.fa.fa-photo.brand-light-blue");
      }
      var body = put(node, "div.w-100.ml3-ns");
      var title = put(body, "div.fw7.mv0.ml3.flex-auto");
      var desc = put(body, "div.mt0.ml3.flex-auto");
      if (product.description.includes(': ')) {
        const parts = product.description.split(': ');
        nameGeneral = parts[0];
        nameDetail = parts[1];
        put(title, "span", parts[0]);
        put(desc, "span", parts[1]);
      } else {
        put(title, "span", product.description);
      }
      if (this.addSku) {
        if (desc.innerHTML) {
          put(desc, "br");
        }
        put(desc, "span", "SKU: " + product.sku);
      }
      var form = put(body, "div.flex.items-center.cl.cn-ns.ml3");
      put(form, "label.brand-gray.w-auto.nowrap.mr2", "Qty:");
      var input = put(form, "input[type=number][value=$][style=width:5rem][min=1].form-control.mr2", product.qty);
      new Number({}, input);
      put(form, "div.fw5.f6", product.total_formatted);
      var button = put(form, "button[type=button].btn.brand-red.ml-auto", put("span.dn.di-ns", "Remove"));
      put(button, "span.material-icons.v-mid.ml2", "close");
      var grid = this;
      on(input, "change", function() {
        grid.collection.put({id: product.id, qty: this.value}).then(function() {
          grid.refresh();
        });
      });
      on(button, "click", function() {
        grid.collection.remove(product.id).then(function() {
          grid.refresh();
        });
      })
      return node;
    },
    renderTotals: function(response) {
      var subtotal = response.getHeader("Cart-Subtotal");
      var shipping = response.getHeader("Cart-Shipping");
      var tax = response.getHeader("Cart-Tax");
      var total = response.getHeader("Cart-Total");
      this.totalsNode.innerHTML = "";
      put(this.totalsNode, "div.pa3 span $<span.fw5 $", "Total:", total);
      this.updateBadge(this.get("total"));
    },
    updateBadge: function(count) {
      var badgeNode = document.querySelector("[data-cart-attach-point=badge]");
      if (badgeNode) {
        badgeNode.innerText = count;
        if (count > 0) {
          badgeNode.classList.remove("dn");
          badgeNode.classList.add("db");
        } else {
          badgeNode.classList.remove("db");
          badgeNode.classList.add("dn");
        }
      }
    }
  });
});