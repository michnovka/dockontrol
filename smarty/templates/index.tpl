{include file="header.tpl"}

<script type="text/javascript">

    var lastTimeout = null;
    var stopTimer = false;

    function doAction(what, element, isInModal, repeatTimes){
        var actionResultSelector = $('div#action_result');

        if(isInModal)
            actionResultSelector = $(element).closest('.uk-modal').find('.notify_modal');

        if(what !== 'stopTimer'){

            if(!repeatTimes){
                $(element).data('original', $(element).html());
                $(element).data('originalDisabled' ,$(element).prop('disabled'));
                $(element).data('originalId' ,$(element).attr('id'));
                $(element).html('<div uk-spinner></div>').prop('disabled', true);
            }else{
                $(element).html('Close garage').prop('disabled', false).attr('id', 'stopTimer');
            }

            $.post(window.location.self, { action : what, repeat_times: repeatTimes }, function(data){
                $(actionResultSelector).find('p').text(data.message);

                var doNotHideActionResult = false;

                if(data.status === 'ok'){
                    //$(actionResultSelector).removeClass('uk-alert-danger').addClass('uk-alert-success').show(200);

                    window.navigator.vibrate(100);

                    if(stopTimer){
                        data.repeat_times = 0;
                        stopTimer = false;
                    }

                    if(data.repeat_times && data.repeat_times > 0){
                        doNotHideActionResult = true;
                        lastTimeout = setTimeout(function(){ console.log('repeating'); doAction(what, element, isInModal, data.repeat_times); }, data.repeat_miliseconds);
                    }

                }else{
                    $(actionResultSelector).removeClass('uk-alert-success').addClass('uk-alert-danger').show(200);
                }

                if(!doNotHideActionResult){

                    setTimeout(function(){ $(actionResultSelector).hide(200);}, 3000);

                    setTimeout(function(){
                        $(element).html($(element).data('original')).prop('disabled', $(element).data('originalDisabled')).attr('id', $(element).data('originalId')).removeClass('success');
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

    $(document).ready(function(){

        {if $user.button_press_type == 'hold'}

            var timeOut = null;

            var touchMoved = false;

            var clicksWithoutAction = 0;

            $('button.clickable').on('mousedown touchstart', function(e) {
                $(this).addClass('active');

                touchMoved = false;

                var this_object = this;
                var this_id = $(this).attr('id');
                console.log('clicked: '+this_id);

                if(this_id == 'stopTimer')
                    stopTimer = true;

                var isInModal = $(this).hasClass('clickable_modal');

                timeOut = setTimeout(function(){
                    if(touchMoved != true || isInModal) {
                        clicksWithoutAction = 0;
                        console.log('held: ' + this_id);
                        doAction(this_id, this_object, isInModal, 0);
                    }
                }, 200);

            }).bind('mouseup mouseleave touchend', function() {
                var this_id = $(this).attr('id');
                console.log('held end: '+this_id);
                $(this).removeClass('active');
                clearInterval(timeOut);
            }).bind('touchmove', function () {
                var this_id = $(this).attr('id');
                console.log('touch moved: '+this_id);
                $(this).removeClass('active');
                touchMoved = true;
            }).bind('click', function () {
                if(clicksWithoutAction++ > 3){
                    window.alert('You have to press and hold the button, not just click.');
                }
                console.log('clicked (without action: ' + clicksWithoutAction + ')');
            });

        {else}

            $('button.clickable').click(function(event){
                var this_id = $(this).attr('id');
                console.log('clicked: '+this_id);

                if(this_id == 'stopTimer')
                    stopTimer = true;

                var isInModal = $(this).hasClass('clickable_modal');

                doAction(this_id, this, isInModal, 0);
            });

        {/if}

        open_garage_gate_modal = $('div#open_garage_gate_modal');

        $('button.garage_gate_modal').click(function() {

            var button_id = $(this).attr('id');

            var new_button_id = button_id.replace(/_options/g,'');
            var camera_id = new_button_id.replace(/open_/g,'');

            $(open_garage_gate_modal).find('button.open_garage_gate_dummy_button').attr('id', new_button_id);
            $(open_garage_gate_modal).find('button.open_garage_gate_1min_dummy_button').attr('id', new_button_id+'_1min');
            $(open_garage_gate_modal).find('h2#open_garage_gate_modal_title').text('Open ' + $(this).text());
            $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture').attr('src', 'loading.jpg');
            $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture').attr('src', 'camera.php?camera='+camera_id+'&now'+Date.now());

            $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture2').attr('src', 'loading.jpg');
            $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture2').attr('src', 'camera.php?camera='+camera_id+'_in&now'+Date.now());

            var modal = UIkit.modal(open_garage_gate_modal);
            modal.show();

        });

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
            <div class="uk-width-1-2@l"><button id="open_gate_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1">Gate</button></div>
        {/if}
        {if $permissions.z9garage}
            <div class="uk-width-1-2@l"><button id="open_garage_z9_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1">Garage Z9</button></div>
        {/if}

        {if $permissions.z8garage}
            <div class="uk-width-1-2@l"><button id="open_garage_z8_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1">Garage Z8</button></div>
        {/if}

        {if $permissions.z7garage}
        <div class="uk-width-1-2@l"><button id="open_garage_z7_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1">Garage Z7</button></div>
        {/if}

        <div class="uk-width-1-1"><h4 class="uk-h4">Entrances</h4></div>

        {if $permissions.entrance_menclova}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_menclova" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_menclova">Menclova</button></div>
        {/if}
        {if $permissions.entrance_smrckova}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_smrckova" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_smrckova">Smrckova</button></div>
        {/if}
        {if $permissions.z7b1}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z7b1" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_z7b1">Z7.B1 Entrance</button></div>
        {/if}
        {if $permissions.z7b2}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z7b2" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_z7b2">Z7.B2 Entrance</button></div>
        {/if}
        {if $permissions.z8b1}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z8b1" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_z8b1">Z8.B1 Entrance</button></div>
        {/if}
        {if $permissions.z8b2}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z8b2" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_z8b2">Z8.B2 Entrance</button></div>
        {/if}
        {if $permissions.z9b1}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z9b1" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_z9b1">Z9.B1 Entrance</button></div>
        {/if}
        {if $permissions.z9b2}
        <div class="uk-width-1-3@l"><button name="action" id="open_entrance_z9b2" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="open_entrance_z9b2">Z9.B2 Entrance</button></div>
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
