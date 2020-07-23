{include file="header.tpl"}

<script type="text/javascript">

    var lastTimeout = null;
    var stopTimer = false;

    function doAction(what, element, isInModal, reSetUpHooks){
        var actionResultSelector = $('div#action_result');

        if(isInModal)
            actionResultSelector = $(element).closest('.uk-modal').find('.notify_modal');

        $(element).data('original', $(element).html());
        $(element).data('originalDisabled' ,$(element).prop('disabled'));
        $(element).data('originalId' ,$(element).attr('id'));
        $(element).html('<div uk-spinner></div>').addClass('spinner').prop('disabled', true);

        what = what.replace(/_options/g,'');

        $.post(window.location.self, { action : what }, function(data){
            $(actionResultSelector).find('p').text(data.message);

            var doNotHideActionResult = false;

            if(data.status === 'relogin'){
                window.location.reload();
            }else if(data.status === 'ok'){
                //$(actionResultSelector).removeClass('uk-alert-danger').addClass('uk-alert-success').show(200);
                var canVibrate = "vibrate" in navigator;

                if(canVibrate)
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

    }

    var open_garage_gate_modal;


    var timeOut = null;

    var touchMoved = false;

    var clicksWithoutAction = 0;

    var pictureInterval = null;

    var picture_element;

    function startPictureInterval(){
        clearInterval(pictureInterval);

        pictureInterval = window.setInterval(function () {

            console.log('interval');

            if(isCurrentWindowHidden || !picture_element.is(':visible')) {
                console.log('clearinterval');

                clearInterval(pictureInterval);
                picture_element.parent().find('.paused_container').show();
            }else
                picture_element.attr('src', picture_element.attr('src')+'1');
        }, 4000);
    }


    function setUpHooksCamera(element){

        $(element).click(function() {

            var button_element = $(this).parent();
            var button_id = button_element.attr('id');

            var new_button_id = button_id.replace(/_options/g,'');

            var img_element = $(this).find('img');

            var camera1_id = img_element.data('camera1');
            var camera2_id = img_element.data('camera2');

            var allow_1min_open = img_element.data('allow1min');

            $(open_garage_gate_modal).find('button.open_garage_gate_dummy_button').attr('id', new_button_id);

            var button_open_1min_element = $(open_garage_gate_modal).find('button.open_garage_gate_1min_dummy_button');

            $(open_garage_gate_modal).find('h2#open_garage_gate_modal_title').text('Open ' + $(this).parent().find('div.single_open').text());

            picture_element.attr('src', 'loading.jpg').attr('src', 'camera.php?camera='+camera1_id+(!!camera2_id ? '&camera2='+camera2_id : '')+'&now'+Date.now());
            picture_element.parent().find('.paused_container').hide();

            startPictureInterval();

            if(!!allow_1min_open){
                button_open_1min_element.show().attr('id', new_button_id+'_1min');
            }else{
                button_open_1min_element.hide();
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
                    doAction(this_id, this_object, isInModal, reSetUpHooks);
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

        open_garage_gate_modal = $('div#open_garage_gate_modal');
        picture_element = $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture');

        setUpHooks('button.clickable,button.garage_gate_modal div.single_open');
        setUpHooksCamera('button.garage_gate_modal div.camera');

        $('div.picture_container').click(function () {
            var imageElement = $(this).find('img.open_garage_gate_modal_camera_picture');

            var pausedContainerElement = $(this).find('.paused_container');

            if(pausedContainerElement.is(':hidden')){

                pausedContainerElement.show();

                clearInterval(pictureInterval);

            } else {

                imageElement.attr('src', imageElement.attr('src')+'1');

                pausedContainerElement.hide();

                startPictureInterval();
            }
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

    var hidden, visibilityChange;
    if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
        visibilityChange = "visibilitychange";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
    }

    var isCurrentWindowHidden = false;

    // If the page is hidden, pause the video;
    // if the page is shown, play the video
    function handleVisibilityChange() {
        isCurrentWindowHidden = !!document[hidden];
    }

    // Handle page visibility change
    document.addEventListener(visibilityChange, handleVisibilityChange, false);

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

        {section name=g loop=$gates}
            {if $permissions[$gates[g].permission]}
                <div class="uk-width-1-2@l"><button id="open_{$gates[g].id}_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">{$gates[g].name}</div>{if $gates[g].camera1}<div class="camera"><img src="security-camera.svg" width="40" data-camera1="{$gates[g].camera1}" data-camera2="{$gates[g].camera2}"{if $gates[g].allow_1min_open} data-allow1min="1"{/if} /></div>{/if}</button></div>
            {/if}
        {/section}

        <div class="uk-width-1-1"><h4 class="uk-h4">Entrances</h4></div>

        {section name=e loop=$entrances}
            {if $permissions[$entrances[e].permission]}
                <div class="uk-width-1-3@l"><button id="open_{$entrances[e].id}_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">{$entrances[e].name}</div>{if $entrances[e].camera1}<div class="camera"><img src="security-camera.svg" width="40" data-camera1="{$entrances[e].camera1}" data-camera2="{$entrances[e].camera2}"{if $entrances[e].allow_1min_open} data-allow1min="1"{/if} /></div>{/if}</button></div>
            {/if}
        {/section}

        {section name=el loop=$elevators}
            {if $permissions[$elevators[el].permission]}
                <div class="uk-width-1-3@l"><button name="action" id="unlock_{$elevators[el].id}" type="button" class="uk-button uk-button-large uk-button-default uk-width-1-1 clickable" value="unlock_{$elevators[el].id}">{$elevators[el].name}</button></div>
            {/if}
        {/section}


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
                {if $user.has_camera_access}
                <div class="uk-width-1-1 picture_container">
                    <img src="loading.jpg" class="open_garage_gate_modal_camera_picture" id="open_garage_gate_modal_camera_picture" />
                    <div class="paused_container"><img src="pause.svg" width="80" /></div>
                </div>
                {/if}
                <div class="uk-width-1-2@l"><button name="action" id="open_garage_gate_dummy_button" type="button" class="open_garage_gate_dummy_button uk-button uk-button-large uk-button-primary uk-width-1-1 clickable clickable_modal" value="">SINGLE OPEN</button></div>
                <div class="uk-width-1-2@l"><button name="action" id="open_garage_gate_1min_dummy_button" type="button" class="open_garage_gate_1min_dummy_button uk-button uk-button-large uk-button-danger uk-width-1-1 clickable clickable_modal" value="">OPEN FOR 1 MIN</button></div>
            </div>
        </div>
    </div>
</div>



{include file="footer.tpl"}
