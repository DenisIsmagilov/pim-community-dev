var Pim = Pim || {};

Pim.navigate = function(route) {
    Oro.hashNavigationInstance.setLocation(route);
};

function init() {
    // Place code that we need to run on every page load here

    // Disable the oro scrollable container
    $('.scrollable-container').removeClass('scrollable-container').css('overflow', 'visible');

    // Prevent UniformJS from breaking our stuff
    $(document).off('uniformInit').on('uniformInit', function() {
        $(document).uniform.restore();
    });

    // Instantiate sidebar
    $('.has-sidebar').sidebarize();

    // Apply Select2
    $('form select').select2({ allowClear: true });

    // Apply Select2 multiselect
    $('form input.multiselect').each(function() {
        $(this).select2({ tags: $(this).val() });
    });

    // Apply bootstrapSwitch
    $('.switch:not(.has-switch)').bootstrapSwitch();

    // Destroy Select2 where it's not necessary
    $('#default_channel').select2('destroy');

    // Activate a form tab
    $('li.tab.active a').each(function() {
        var paneId = $(this).attr('href');
        $(paneId).addClass('active');
    });

    // Toogle accordion icon
    $('.accordion').on('show hide', function(e) {
        $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('fa-icon-collapse-alt fa-icon-expand-alt');
    });

    $('.remove-attribute').each(function() {
        var target = $(this).parent().find('input:not([type="hidden"]):not([class*=select2]), select, textarea').first();
        $(this).insertAfter(target).css('margin-left', 20).attr('tabIndex', -1);
    });

    $('form div.scopable').scopableField();
    $('form div.currency').currencyField();

    $('#attribute-buttons .dropdown-menu').click(function (e) {
        e.stopPropagation();
    });

    $('#default_channel').change(function() {
        $('.scopable').scopableField({ defaultScope: $(this).val() });
    });

    $('.dropdown-menu.channel a').click(function (e) {
        e.preventDefault();
        $('.scopable').scopableField($(this).data('action'));
    });

    // Add form update listener
    $('form[data-updated]').each(function() {
        new FormUpdateListener($(this).attr('id'), $(this).data('updated'));
    });

    // Instantiate the tree
    $('[data-tree]').each(function() {
        switch ($(this).attr('data-tree')) {
            case 'associate':
                Pim.tree.associate($(this).attr('id'));
                break;
            case 'view':
                Pim.tree.view($(this).attr('id'));
                break;
            case 'manage':
                Pim.tree.manage($(this).attr('id'));
                break;
            default:
                break;
        }
    });

    // Instantiate dialogForm
    $('[data-form="dialog"]').each(function() {
        $(this).dialogForm();
    });

    // Instantiate popin form
    $('[data-form="popin"]').each(function() {
        Pim.popinForm($(this).attr('id'));
    });

    // Clean up multiselect plugin generated content that is appended to body
    $('body>.ui-multiselect-menu').appendTo($('#container'));

    // DELETE request for delete buttons
    $('a[data-dialog]').on('click', function() {
        var $el = $(this);
        var message = $el.data('message');
        var title = $el.data('title');
        if ($el.data('dialog') ==  'confirm') {
            var doAction = function() {
                $el.off('click');
                var $form = $('<form>', { method: 'POST', action: $el.attr('data-url')});
                $('<input>', { type: 'hidden', name: '_method', value: $el.data('method')}).appendTo($form);
                $form.appendTo('body').submit();
            };

            PimDialog.confirm(message, title, doAction);
        } else {
            PimDialog.alert(message, title);
        }
    });

    // Save and restore activated form tabs and groups
    function saveFormState() {
        var activeTab = $('#form-navbar .nav li.active a').attr('href');
        if (activeTab) {
            sessionStorage.activeTab = activeTab;
        }

        var activeGroup = $('.tab-groups li.tab.active a').attr('href');
        if (activeGroup) {
            sessionStorage.activeGroup = activeGroup;
        }
    }

    function restoreFormState() {
        if (sessionStorage.activeTab) {
            var $activeTab = $('[href=' + sessionStorage.activeTab + ']');
            if ($activeTab) {
                $activeTab.tab('show');
            }
            sessionStorage.removeItem('activeTab');
        }

        if (sessionStorage.activeGroup) {
            var $activeGroup = $('[href=' + sessionStorage.activeGroup + ']');
            if ($activeGroup) {
                $activeGroup.tab('show');
            }
            sessionStorage.removeItem('activeGroup');
        }
    }

    if (typeof(Storage) !== 'undefined') {
        restoreFormState();

        $('form.form-horizontal').on('submit', saveFormState);
        $('#locale-switcher a').on('click', saveFormState);
    }
}

$(function() {
    'use strict';

    // Execute the init function on page load
    init();
});

// Listener for form update events (used in product edit form)
var FormUpdateListener = function(formId, message) {
    var self = this;
    this.updated = false;

    this.formUpdated = function() {
        this.updated = true;
        $('#updated').show();
        $('form#' + formId).off('change', this.formUpdated);
        $('form#' + formId + ' ins.jstree-checkbox').off('click', this.formUpdated);

        // This will not work with backbone navigation
        $(window).on('beforeunload', function() {
            if (self.updated) {
                return message;
            }
        });
        $('form#' + formId + ' button[type="submit"]').on('click', function() {
            self.updated = false;
        });
    };

    $('form#' + formId).on('change', this.formUpdated);
    $('form#' + formId + ' ins.jstree-checkbox').on('click', this.formUpdated);
};
