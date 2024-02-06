define([
    'jquery',
    'Magento_Customer/js/view/customer',
    'Magento_Customer/js/customer-data',
    'jquery-ui-modules/widget'
], function ($, customer, customerData) {
    'use strict';

    $.widget('mage.submitCommentWidget', {
        options: {
            ajax_list_url: '',
            ajax_post_url: '',
            ajax_email_url: '',
            product_id: '',
            listElemSelector: '',
            formSelector: '',
            fieldNameSelector: '',
            fieldEmailSelector: '',
            fieldMessageSelector: ''
        },

        /**
         * Creates widget instance
         *
         * @private
         */
        _create: function () {
            this.bindSubmit();
            this.setCustomerName();
            this.setCustomerEmail();
        },

        bindSubmit: function() {
            let self = this;
            $(this.element).on('submit', function (e) {
                e.preventDefault();
                if ($(this).valid()) {
                    $(this).find('.submit').attr('disabled', true);
                    self.ajaxPostComment();
                    $(this).find('.submit').attr('disabled', false);
                }
            });
        },

        setCustomerName: function () {
            let firstname = customerData.get('customer')().firstname;
            let nameElem = $(this.element).find(this.options.fieldNameSelector);
            if (typeof (firstname) === "undefined") {
                customerData.reload('customer', true)
                    .then(function() {
                        firstname = customerData.get('customer')().firstname;
                    });
            }
            if(typeof (firstname) !== "undefined"){
                nameElem.val(firstname);
                nameElem.attr('disabled', true);
            }
        },

        setCustomerEmail: function () {
            let self = this;
            $.ajax({
                url: this.options.ajax_email_url,
                type: "POST",
            }).done(function (data) {
                if (data.email) {
                    let emailElem =  $(self.options.fieldEmailSelector);
                    emailElem.val(data.email);
                    emailElem.attr('disabled', true);
                }
                return true;
            });
        },

        ajaxList: function () {
            let self = this;
            $.ajax({
                url: this.options.ajax_list_url,
                type: "POST",
                data: {id: this.options.product_id}
            }).done(function (data) {
                if (data.html) {
                    $(self.options.listElemSelector).replaceWith(data.html);
                }
                return true;
            }).fail(function (data){
                console.log(data);
            });
        },

        ajaxPostComment: function () {
            let self = this;
            let data = $(this.options.formSelector).serializeArray();

            $.ajax({
                url: this.options.ajax_post_url,
                type: "POST",
                data: data
            }).done(function (data) {
                $(self.options.fieldMessageSelector).val('');
                self.ajaxList();
                alert('Your comment has been posted!');
                return true;
            }).fail(function (data){
                console.log(data);
            });
        }

    });

    return $.mage.submitCommentWidget;
});
