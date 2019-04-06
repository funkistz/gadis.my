jQuery(function ($) {
    var params = mobiconnector_intro_js_params;
    var debug = 0;
    var globalStep = 0;
    var firstload = 0;

    /**
     * Print content intro
     */
    function bamobile_print_content_intro_js(step) {
        var html;
        html = '<div id="mobiconnector-intro-js-' + step + '" class="mobiconnector-intro-content mobiconnector-intro-js mobile-intro-left">';
        html += '<div class="mobiconnector-heading-intro">';
        html += '<h3 class="mobiconnector-heading-intro-h3"></h3>';
        html += '</div>';
        html += '<div class="mobiconnector-content-intro">';
        html += '<p class="mobiconnector-content-intro-details"></p>';
        html += '</div>';
        html += '<div class="mobiconnector-buttons-intro">';
        html += '<a class="mobiconnector-close-intros" href="#">Dismiss</a>';
        html += '<button title="Next Step" data-step-action="next" class="button button-primary mobiconnector-button-control-step right">Next</button>';
        html += '</div>';
        html += '<div class="mobiconnector-intro-arrow"><div class="mobiconnector-intro-arrow-inner"></div></div>';
        html += '</div>';
        $('body').append(html);
    }

    /**
     * Change postion Intro and then Message
     * @param {*} position 
     * @param {*} message 
     */
    function bamoblie_change_intro_by_position(position, message, step) {
        if ($('body').children('#mobiconnector-intro-js-' + step).length >= 0) {
            $('body').children('.mobiconnector-intro-content').remove();
        }
        bamobile_print_content_intro_js(step);
        $('body').children('.mobiconnector-intro-js').css({
            'top': position.pTop,
            'left': position.pLeft
        })
        $('.bamobile-mobiconnector-intro-js').children('a').removeClass('mobiconnector-hover');
        $('.mobiconnector_intro_step_' + step).children('a').addClass('mobiconnector-hover');
        $('body').children('.mobiconnector-intro-js').children('.mobiconnector-heading-intro').children('.mobiconnector-heading-intro-h3').html(message.heading);
        $('body').children('.mobiconnector-intro-js').children('.mobiconnector-content-intro').children('.mobiconnector-content-intro-details').html(message.message);
        $('.mobiconnector-button-control-step').on('click', function (e) {
            if ($(this).data('step-action') == 'next') {
                bamobile_next_step(step);
            }
        })
        $('.mobiconnector-close-intros').on('click', function () {
            bamobile_delete_step();
        })
    }

    /**
     * First load print intro
     */
    function bamobile_first_load_step() {
        if (firstload === 0) {
            var firstStep = $('.mobiconnector_intro_step_1');
            var message = bamobile_get_message_of_intro(firstStep);
            var position = bamobile_get_position_of_content(firstStep);
            var step = firstStep.data('step');
            bamoblie_change_intro_by_position(position, message, step);
            bamobile_fix_position_on_intro(firstStep);
            firstload = firstload + 1;
        }
    }

    /**
     * Next step
     * @param {*} stepClick 
     */
    function bamobile_next_step(stepClick) {
        var currentStep = $('.mobiconnector_intro_step_' + stepClick);
        if (stepClick == globalStep) {
            bamobile_delete_step();
        } else {
            var type = currentStep.data('type');
            if (type == 'auth') {
                currentStep.addClass('wp-has-current-submenu');
            }
            if (type == 'children') {
                if (currentStep.is(':last-child')) {
                    currentStep.parent('.wp-submenu').parent('.bamobile-mobiconnector-intro-js').removeClass('wp-has-current-submenu');
                }
            }
            var nextStep = $('.mobiconnector_intro_step_' + (stepClick + 1));
            var message = bamobile_get_message_of_intro(nextStep);
            var position, stepData;
            if (message == 'error-nextstep') {
                nextStep = $('.mobiconnector_intro_step_' + (stepClick + 2));
                message = bamobile_get_message_of_intro(nextStep);
                position = bamobile_get_position_of_content(nextStep);
                stepData = nextStep.data('step');
            } else {
                position = bamobile_get_position_of_content(nextStep);
                stepData = nextStep.data('step');
            }
            bamoblie_change_intro_by_position(position, message, stepData);
            bamobile_fix_position_on_intro(nextStep);
        }
    }

    /**
     * Fix Position
     * @param {*} nextStep 
     */
    function bamobile_fix_position_on_intro(thisElement) {
        if (thisElement.data('type') == 'auth') {
            var heightBox = $('.mobiconnector-content-intro').height();
            var height = heightBox / 2;
            var position = thisElement.position();
            var pTop = position.top - height;
            $('body').children('.mobiconnector-intro-js').css({
                'top': pTop
            })
        } else if (thisElement.data('type') == 'children') {
            var parentElement = thisElement.parent('.wp-submenu').parent('li');
            var parentStep = parentElement.data('step');
            var parentElementa = parentElement.children('a');
            var step = thisElement.data('step');
            var substep = thisElement.data('sub-step');
            var position = parentElement.position();
            var height = thisElement.height();
            if ((step - 1) == parentStep) {
                height = parentElementa.height() + 5;
            } else {
                if (substep < 5) {
                    height = parentElementa.height() - (1 * substep);
                } else if (substep < 7) {
                    height = parentElementa.height() - (1 * substep) + (substep - 4);
                } else if (substep < 10) {
                    height = parentElementa.height() - (1 * substep) + (substep - 5);
                }

            }
            var heightBox = $('.mobiconnector-content-intro').height();
            var heightOfBox = heightBox / 2;
            var pTop = position.top - heightOfBox;
            var oPtop = pTop + (height * substep);
            $('body').children('.mobiconnector-intro-js').css({
                'top': oPtop
            })
        }
    }

    /**
     * Delete Intros
     */
    function bamobile_delete_step() {
        $('.mobiconnector-intro-content').remove();
        bamobile_start_event_mouse();
        $.post(params.ajax_url, {
            action: 'bamobile_disable_menu_intros',
            security: params.security
        });
    }

    /**
     * Get Message content
     * @param {*} thisElement 
     */
    function bamobile_get_message_of_intro(thisElement) {
        var heading, message;
        if (thisElement.data('type') == 'auth') {
            var keymessage = thisElement.data('step-content');
            heading = params[keymessage].heading;
            message = params[keymessage].message;
        } else if (thisElement.data('type') == 'children') {
            var keyMessage = thisElement.data('step-content');
            var parentElement = thisElement.parent('.wp-submenu').parent('li');
            var parentKey = parentElement.data('step-content');
            if (typeof params[parentKey].children[keyMessage] !== 'undefined') {
                heading = params[parentKey].children[keyMessage].heading;
                message = params[parentKey].children[keyMessage].message;
            } else {
                return "error-nextstep";
            }
        }
        var output = { heading: heading, message: message };
        return output;
    }

    /**
     * Get position of content
     * @param {*} thisElement 
     */
    function bamobile_get_position_of_content(thisElement) {
        var positionTop, positionLeft, width, height;
        if (thisElement.data('type') == 'auth') {
            var position = thisElement.position();
            width = (thisElement.width()) + 16;
            positionTop = position.top;
            positionLeft = position.left + width;
        } else if (thisElement.data('type') == 'children') {
            var parentElement = thisElement.parent('.wp-submenu').parent('li');
            var parentStep = parentElement.data('step');
            var parentElementa = parentElement.children('a');
            var step = thisElement.data('step');
            var substep = thisElement.data('sub-step');
            var position = parentElement.position();
            var height;
            if ((step - 1) == parentStep) {
                height = parentElementa.height() + 5;
            } else {
                if (substep < 5) {
                    height = parentElementa.height() - (1 * substep);
                } else if (substep < 7) {
                    height = parentElementa.height() - (1 * substep) + (substep - 4);
                } else if (substep < 10) {
                    height = parentElementa.height() - (1 * substep) + (substep - 5);
                }

            }
            width = (parentElement.width()) + 16;
            positionTop = position.top + (height * substep) - 29;
            positionLeft = position.left + width;
        }
        var output = { pTop: positionTop, pLeft: positionLeft };
        return output
    }

    /**
     * Add data to sub content
     * 
     * @param {*} thisElement 
     * @param {*} step 
     */
    function bamobile_add_data_to_subcontent(thisElement, step) {
        if (firstload === 0) {
            var children = thisElement.children('.wp-submenu');
            if (children.length > 0) {
                var indexChildren = 0;
                var idChildrenContent = 0;
                var listChildren = children.find('li');
                var stepFinal = step;
                listChildren.each(function (key, val) {
                    if ($(this).hasClass('wp-submenu-head')) {
                        return true;
                    }
                    stepFinal++;
                    indexChildren++;
                    var valueChildren = $(this).children('a').attr('href');
                    $(this).attr('data-type', 'children');
                    $(this).attr('data-step', stepFinal);
                    $(this).addClass('bamobile-mobiconnector-intro-js');
                    $(this).addClass('mobiconnector_intro_step_' + stepFinal);
                    if (valueChildren !== null && valueChildren !== undefined) {
                        if (typeof params.bamobile_check_message[valueChildren] !== 'undefined') {
                            $(this).attr('data-sub-step', indexChildren);
                            $(this).attr('data-parent-step', step);
                            $(this).attr('data-step-content', params.bamobile_check_message[valueChildren]);
                        } else {
                            idChildrenContent++;
                            $(this).attr('data-sub-step', indexChildren);
                            $(this).attr('data-parent-step', step);
                            $(this).attr('data-step-content', 'step' + idChildrenContent);
                        }
                    }
                    globalStep = stepFinal;
                })
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Add data to menu
     * 
     * @param {*} thisElement 
     * @param {*} step 
     */
    function bamobile_add_data_to_menu(thisElement, step) {
        if (firstload === 0) {
            var classMessage = bamobile_get_class_by_element(thisElement);
            thisElement.attr('data-type', 'auth');
            thisElement.attr('data-step', step);
            thisElement.attr('data-step-content', classMessage);
            thisElement.addClass('mobiconnector_intro_step_' + step);
            return false;
        }
    }

    /**
     * get class message of element
     * 
     * @param {*} thisElement 
     */
    function bamobile_get_class_by_element(thisElement) {
        if (firstload === 0) {
            var classElement = thisElement.attr('class');
            var listClass = classElement.split(' ');
            var classAfterFor;
            for (var i = 0; i < listClass.length; i++) {
                if (listClass[i].match(/bamobile/gi) !== null && listClass[i].match(/bamobile/gi) !== undefined) {
                    classAfterFor += listClass[i] + ' ';
                }
            }
            var classBamobile = $.trim(classAfterFor);
            var message = classBamobile.replace(classBamobile.substr(0, classBamobile.indexOf('bamobile_message_')), '');
            return message;
        }
    }

    if (firstload === 0) {
        /**
         * Loaded
         */
        $(window).load(function (e) {
            $('.wp-has-submenu').removeClass('wp-has-current-submenu');
            $.each($('.bamobile-mobiconnector-intro-js'), function (key, val) {
                if ($(this).is('li') == true) {
                    if ($(this).hasClass('wp-submenu-head')) {
                        return true;
                    }
                    globalStep = globalStep + 1;
                    bamobile_add_data_to_menu($(this), globalStep);
                    bamobile_add_data_to_subcontent($(this), globalStep);
                }
            })
            bamobile_block_event_mouse();
            setTimeout(function () {
                bamobile_first_load_step();
            }, 200);
        })
    }

    /**
     * Event Scroll stop
     */
    function bamobile_block_event_mouse() {
        var wheelEvent = bamobile_isEventSupported('mousewheel') ? 'mousewheel' : 'wheel';
        $('body').bind(wheelEvent, function (e) {
            return false;
        });
    }

    /**
     * Event Scroll start
     */
    function bamobile_start_event_mouse() {
        var wheelEvent = bamobile_isEventSupported('mousewheel') ? 'mousewheel' : 'wheel';
        $('body').unbind(wheelEvent);
    }

    /**
     * Check event
     * @param {*} eventName 
     */
    function bamobile_isEventSupported(eventName) {
        var el = document.createElement('div');
        eventName = 'on' + eventName;
        var isSupported = (eventName in el);
        if (!isSupported) {
            el.setAttribute(eventName, 'return;');
            isSupported = typeof el[eventName] == 'function';
        }
        el = null;
        return isSupported;
    }
})