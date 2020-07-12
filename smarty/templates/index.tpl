{include file="header.tpl"}

<script type="text/javascript">

    var lastTimeout = null;
    var stopTimer = false;

    function doAction(what, element, isInModal, repeatTimes, reSetUpHooks){
        var actionResultSelector = $('div#action_result');

        if(isInModal)
            actionResultSelector = $(element).closest('.uk-modal').find('.notify_modal');

        if(what !== 'stopTimer'){

            if(!repeatTimes){
                $(element).data('original', $(element).html());
                $(element).data('originalDisabled' ,$(element).prop('disabled'));
                $(element).data('originalId' ,$(element).attr('id'));
                $(element).html('<div uk-spinner></div>').addClass('spinner').prop('disabled', true);
            }else{
                $(element).html('Close garage').prop('disabled', false).attr('id', 'stopTimer');
            }


            what = what.replace(/_options/g,'');

            $.post(window.location.self, { action : what, repeat_times: repeatTimes }, function(data){
                $(actionResultSelector).find('p').text(data.message);

                var doNotHideActionResult = false;

                if(data.status === 'ok'){
                    //$(actionResultSelector).removeClass('uk-alert-danger').addClass('uk-alert-success').show(200);

                    window.navigator.vibrate(100);

                }else{
                    $(actionResultSelector).removeClass('uk-alert-success').addClass('uk-alert-danger').show(200);
                }

                if(!doNotHideActionResult){

                    setTimeout(function(){ $(actionResultSelector).hide(200);}, 3000);

                    setTimeout(function(){
                        $(element).removeClass('spinner').html($(element).data('original')).prop('disabled', $(element).data('originalDisabled')).attr('id', $(element).data('originalId')).removeClass('success');
                        if(reSetUpHooks) {
                            setUpHooks($(element).find('div.single_open'));
                            setUpHooksCamera($(element).find('div.camera'));
                        }
                    }, 2000);

                    $(element).html('<svg id="successAnimation" class="animated" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 70 70">' +
                        '  <path id="successAnimationResult" fill="#D8D8D8" d="M35,60 C21.1928813,60 10,48.8071187 10,35 C10,21.1928813 21.1928813,10 35,10 C48.8071187,10 60,21.1928813 60,35 C60,48.8071187 48.8071187,60 35,60 Z M23.6332378,33.2260427 L22.3667622,34.7739573 L34.1433655,44.40936 L47.776114,27.6305926 L46.223886,26.3694074 L33.8566345,41.59064 L23.6332378,33.2260427 Z"/>' +
                        '  <circle id="successAnimationCircle" cx="35" cy="35" r="24" stroke="#979797" stroke-width="2" stroke-linecap="round" fill="transparent"/>' +
                        '  <polyline id="successAnimationCheck" stroke="#979797" stroke-width="2" points="23 34 34 43 47 27" fill="transparent"/>' +
                        '</svg>').removeClass('active').addClass('success');
                }
            }, 'json');
        }else{
            $(element).html('<div uk-spinner></div>').prop('disabled', true);
        }
    }

    var open_garage_gate_modal;


    var timeOut = null;

    var touchMoved = false;

    var clicksWithoutAction = 0;

    function setUpHooksCamera(element){

        $(element).click(function() {

            var button_element = $(this).parent();
            var button_id = button_element.attr('id');

            var new_button_id = button_id.replace(/_options/g,'');
            var camera_id = new_button_id.replace(/open_/g,'');

            var picture_element = $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture');
            var picture2_element = $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture2');

            $(open_garage_gate_modal).find('button.open_garage_gate_dummy_button').attr('id', new_button_id);

            var button_open_1min_element = $(open_garage_gate_modal).find('button.open_garage_gate_1min_dummy_button');

            $(open_garage_gate_modal).find('h2#open_garage_gate_modal_title').text('Open ' + $(this).parent().find('div.single_open').text());

            picture_element.attr('src', 'loading.jpg').attr('src', 'camera.php?camera='+camera_id+'&now'+Date.now());

            if(button_element.hasClass('entrance')){
                picture2_element.hide();
                button_open_1min_element.hide();
            }else{
                picture2_element.show().attr('src', 'loading.jpg').attr('src', 'camera.php?camera='+camera_id+'_in&now'+Date.now());
                button_open_1min_element.show().attr('id', new_button_id+'_1min');
            }


            var modal = UIkit.modal(open_garage_gate_modal);
            modal.show();

        });
    }

    function setUpHooks(element){

        console.log('setup hooks');
        console.log(element);

        $(element).on('mousedown touchstart', function(e) {

            var this_object = this;

            var reSetUpHooks = false;

            if(this_object.tagName === 'DIV') {
                this_object = $(this).parent();
                reSetUpHooks = true;
            }

            console.log(this_object);

            $(this_object).addClass('active');

            touchMoved = false;

            var this_id = $(this_object).attr('id');
            console.log('clicked: '+this_id);

            var isInModal = $(this_object).hasClass('clickable_modal');

            timeOut = setTimeout(function(){
                if(touchMoved !== true || isInModal) {
                    clicksWithoutAction = 0;
                    console.log('held: ' + this_id);
                    doAction(this_id, this_object, isInModal, 0, reSetUpHooks);
                }
            }, 250);

        }).bind('mouseup mouseleave touchend', function() {

            var this_object = this;

            if(this_object.tagName === 'DIV')
                this_object = $(this).parent();

            var this_id = $(this_object).attr('id');
            console.log('held end: '+this_id);
            $(this_object).removeClass('active');
            clearInterval(timeOut);
        }).bind('touchmove', function () {

            var this_object = this;

            if(this_object.tagName === 'DIV')
                this_object = $(this).parent();

            var this_id = $(this_object).attr('id');
            console.log('touch moved: '+this_id);
            $(this_object).removeClass('active');
            touchMoved = true;
        }).bind('click', function () {
            if(++clicksWithoutAction > 2){
                window.alert('You have to press and hold the button, not just click.');
            }
            console.log('clicked (without action: ' + clicksWithoutAction + ')');
        });

    }

    $(document).ready(function(){


        setUpHooks('button.clickable,button.garage_gate_modal div.single_open');

        open_garage_gate_modal = $('div#open_garage_gate_modal');

        setUpHooksCamera('button.garage_gate_modal div.camera');

        $('img#open_garage_gate_modal_camera_picture,img#open_garage_gate_modal_camera_picture2').click(function () {
            $(this).attr('src', $(this).attr('src')+'1');
        });

    });

    window.history.pushState({ page: 1 }, "", "");

    window.addEventListener('popstate', function(event) {
        // The popstate event is fired each time when the current history entry changes.

        var modal = UIkit.modal(open_garage_gate_modal);

        if(modal.isToggled()){
            history.pushState(null, null, window.location.pathname);
            modal.hide();
        } else {
            history.back();
        }

        history.pushState(null, null, window.location.pathname);

    }, false);

</script>


<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>DOCKontrol</span></h2>

    <div class="uk-alert-success uk-text-center uk-margin-medium" style="display:none;" id="action_result" uk-alert>
        <p></p>
    </div>


    <div class="uk-grid-small uk-text-center uk-grid-row-small" uk-grid>

        {if $permissions.gate}
            <div class="uk-width-1-2@l"><button name="action" id="enter" type="button" class="uk-button uk-button-large uk-button-primary uk-width-1-1 clickable" value="enter">CAR ENTER</button></div>
            <div class="uk-width-1-2@l"><button name="action" id="exit" type="button" class="uk-button uk-button-large uk-button-danger uk-width-1-1 clickable" value="exit">CAR EXIT</button></div>
        {/if}

        <div class="uk-width-1-1"><h4 class="uk-h4">Gates</h4></div>

        {if $permissions.gate}
            <div class="uk-width-1-2@l"><button id="open_gate_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">Gate</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.z9garage}
            <div class="uk-width-1-2@l"><button id="open_garage_z9_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">Garage Z9</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button>
            </div>
        {/if}


        {if $permissions.z8garage}
            <div class="uk-width-1-2@l"><button id="open_garage_z8_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">Garage Z8</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}

        {if $permissions.z7garage}
        <div class="uk-width-1-2@l"><button id="open_garage_z7_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">Garage Z7</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}

        <div class="uk-width-1-1"><h4 class="uk-h4">Entrances</h4></div>

        {if $permissions.entrance_menclova}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_menclova_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_menclova_options"><div class="single_open">Menclova</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.entrance_smrckova}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_smrckova_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_smrckova_options"><div class="single_open">Smrckova</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.z7b1}
            <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z7b1_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_z7b1_options"><div class="single_open">Z7.B1</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.z7b2}
            <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z7b2_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_z7b2_options"><div class="single_open">Z7.B2</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.z8b1}
            <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z8b1_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_z8b1_options"><div class="single_open">Z8.B1</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.z8b2}
            <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z8b2_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_z8b2_options"><div class="single_open">Z8.B2</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.z9b1}
            <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z9b1_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_z9b1_options"><div class="single_open">Z9.B1</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}
        {if $permissions.z9b2}
            <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z9b2_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1" value="open_entrance_z9b2_options"><div class="single_open">Z9.B2</div><div class="camera"><img src="security-camera.svg" width="40" /></div></button></div>
        {/if}

        {if $permissions.z9b2elevator}
        <div class="uk-width-1-3@l"><button name="action" id="unlock_elevator_z9b2"type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="unlock_elevator_z9b2">Z9.B2 Elevator</button></div>
        {/if}


    </div>

</div>


<div id="open_garage_gate_modal" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-margin-auto-vertical uk-padding-medium">
        <div class="uk-modal-header uk-visible@s">
            <h2 class="uk-modal-title" id="open_garage_gate_modal_title">Open Garage/Gate</h2>
        </div>
        <div class="uk-modal-body uk-padding-small">
            <div class="uk-alert-success uk-text-center uk-margin-medium notify_modal" style="display:none;" uk-alert>
                <p></p>
            </div>
            <div class="uk-grid-small uk-text-center uk-grid-row-small" uk-grid>
                <div class="uk-width-1-1"><img src="loading.jpg" style="width: 100%; height: 100%; cursor: pointer;" id="open_garage_gate_modal_camera_picture" /></div>
                <div class="uk-width-1-1"><img src="loading.jpg" style="width: 100%; height: 100%; cursor: pointer;" id="open_garage_gate_modal_camera_picture2" /></div>
                <div class="uk-width-1-2@l"><button name="action" id="open_garage_gate_dummy_button" type="button" class="open_garage_gate_dummy_button uk-button uk-button-large uk-button-primary uk-width-1-1 clickable clickable_modal" value="">SINGLE OPEN</button></div>
                <div class="uk-width-1-2@l"><button name="action" id="open_garage_gate_1min_dummy_button" type="button" class="open_garage_gate_1min_dummy_button uk-button uk-button-large uk-button-danger uk-width-1-1 clickable clickable_modal" value="">OPEN FOR 1 MIN</button></div>
            </div>
        </div>
    </div>
</div>



{include file="footer.tpl"}
