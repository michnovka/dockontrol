{include file="header.tpl"}
<script src="resources/base32.min.js" type="text/javascript"></script>
<script src="resources/jsTOTP.min.js" type="text/javascript"></script>

{literal}
    <script>

        /**
         * creates a new FIDO2 registration
         * @returns {undefined}
         */
        function newregistration(pin, nuki_id, callback) {

            if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                window.alert('Browser not supported.');
                return;
            }

            // get default args
            window.fetch('/webauthn-server.php?fn=getCreateArgs&pin='+pin+'&nuki_id='+nuki_id, {method:'GET',cache:'no-cache'}).then(function(response) {
                return response.json();

                // convert base64 to arraybuffer
            }).then(function(json) {

                // error handling
                if (json.success === false) {
                    throw new Error(json.msg);
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                recursiveBase64StrToArrayBuffer(json);
                return json;

                // create credentials
            }).then(function(createCredentialArgs) {
                console.log(createCredentialArgs);
                return navigator.credentials.create(createCredentialArgs);

                // convert to base64
            }).then(function(cred) {
                return {
                    clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
                };

                // transfer to server
            }).then(JSON.stringify).then(function(AuthenticatorAttestationResponse) {

                return window.fetch('/webauthn-server.php?fn=processCreate', {method:'POST', body: AuthenticatorAttestationResponse, cache:'no-cache'});

                // convert to JSON
            }).then(function(response) {
                return response.json();

                // analyze response
            }).then(function(json) {
                if (json.success) {
                    localStorage.setItem('nuki_'+nuki_id+'_fingerprint', true);
                    callback();
                } else {
                    localStorage.removeItem('nuki_'+nuki_id+'_fingerprint');
                    throw new Error(json.msg);
                }

                // catch errors
            }).catch(function(err) {
                window.alert(err.message || 'unknown error occured');
            });
        }


        /**
         * checks a FIDO2 registration
         * @returns {undefined}
         */
        function checkregistration(nuki_id, callback, callbackFailure) {

            if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                window.alert('Browser not supported.');
                return;
            }

            // get default args
            window.fetch('/webauthn-server.php?fn=getGetArgs', {method:'GET',cache:'no-cache'}).then(function(response) {
                return response.json();

                // convert base64 to arraybuffer
            }).then(function(json) {

                // error handling
                if (json.success === false) {
                    localStorage.removeItem('nuki_' + nuki_id + '_fingerprint');
                    throw new Error(json.msg);
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                recursiveBase64StrToArrayBuffer(json);
                return json;

                // create credentials
            }).then(function(getCredentialArgs) {
                return navigator.credentials.get(getCredentialArgs);

                // convert to base64
            }).then(function(cred) {
                return {
                    id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
                    clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
                    signature : cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null,
                    nuki_id: nuki_id
                };

                // transfer to server
            }).then(JSON.stringify).then(function(AuthenticatorAttestationResponse) {
                return window.fetch('/webauthn-server.php?fn=processGet', {method:'POST', body: AuthenticatorAttestationResponse, cache:'no-cache'});

                // convert to json
            }).then(function(response) {
                return response.json();

                // analyze response
            }).then(function(json) {
                if (json.success) {
                    callback(json.pin);
                } else {
                    localStorage.removeItem('nuki_' + nuki_id + '_fingerprint');
                    callbackFailure();
                    throw new Error(json.msg);
                }

                // catch errors
            }).catch(function(err) {
                localStorage.removeItem('nuki_' + nuki_id + '_fingerprint');
                callbackFailure();
                window.alert(err.message || 'unknown error occured');
            });
        }


        /**
         * convert RFC 1342-like base64 strings to array buffer
         * @param {mixed} obj
         * @returns {undefined}
         */
        function recursiveBase64StrToArrayBuffer(obj) {
            let prefix = '?BINARY?B?';
            let suffix = '?=';
            if (typeof obj === 'object') {
                for (let key in obj) {
                    if (typeof obj[key] === 'string') {
                        let str = obj[key];
                        if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                            str = str.substring(prefix.length, str.length - suffix.length);

                            let binary_string = window.atob(str);
                            let len = binary_string.length;
                            let bytes = new Uint8Array(len);
                            for (var i = 0; i < len; i++)        {
                                bytes[i] = binary_string.charCodeAt(i);
                            }
                            obj[key] = bytes.buffer;
                        }
                    } else {
                        recursiveBase64StrToArrayBuffer(obj[key]);
                    }
                }
            }
        }

        /**
         * Convert a ArrayBuffer to Base64
         * @param {ArrayBuffer} buffer
         * @returns {String}
         */
        function arrayBufferToBase64(buffer) {
            var binary = '';
            var bytes = new Uint8Array(buffer);
            var len = bytes.byteLength;
            for (var i = 0; i < len; i++) {
                binary += String.fromCharCode( bytes[ i ] );
            }
            return window.btoa(binary);
        }

        /**
         * force https on load
         * @returns {undefined}
         */
        window.onload = function() {
            if (location.protocol !== 'https:' && location.host !== 'localhost') {
                location.href = location.href.replace('http', 'https');
            }
        }

    </script>
{/literal}
<script type="text/javascript">

    function hexToString(hex) {
        if (!hex.match(/^[0-9a-fA-F]+$/)) {
            throw new Error('is not a hex string.');
        }
        if (hex.length % 2 !== 0) {
            hex = '0' + hex;
        }
        var bytes = [];
        for (var n = 0; n < hex.length; n += 2) {
            var code = parseInt(hex.substr(n, 2), 16)
            bytes.push(code);
        }
        return bytes;
    }

    function sha256(str) {
        // Get the string as arraybuffer.
        var buffer = new TextEncoder("utf-8").encode(str)
        return crypto.subtle.digest("SHA-256", buffer).then(function(hash) {
            return hex(hash)
        })
    }

    function hex(buffer) {
        var digest = ''
        var view = new DataView(buffer)
        for(var i = 0; i < view.byteLength; i += 4) {
            // We use getUint32 to reduce the number of iterations (notice the `i += 4`)
            var value = view.getUint32(i)
            // toString(16) will transform the integer into the corresponding hex string
            // but will remove any initial "0"
            var stringValue = value.toString(16)
            // One Uint32 element is 4 bytes or 8 hex chars (it would also work with 4
            // chars for Uint16 and 2 chars for Uint8)
            var padding = '00000000'
            var paddedValue = (padding + stringValue).slice(-padding.length)
            digest += paddedValue
        }

        return digest
    }

    function vibrateIfPossible() {
        var canVibrate = "vibrate" in navigator;

        if(canVibrate)
            window.navigator.vibrate(100);
    }


    var success_svg = '<svg id="successAnimation" class="animated" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 70 70">' +
        '  <path id="successAnimationResult" fill="#D8D8D8" d="M35,60 C21.1928813,60 10,48.8071187 10,35 C10,21.1928813 21.1928813,10 35,10 C48.8071187,10 60,21.1928813 60,35 C60,48.8071187 48.8071187,60 35,60 Z M23.6332378,33.2260427 L22.3667622,34.7739573 L34.1433655,44.40936 L47.776114,27.6305926 L46.223886,26.3694074 L33.8566345,41.59064 L23.6332378,33.2260427 Z"/>' +
        '  <circle id="successAnimationCircle" cx="35" cy="35" r="24" stroke="#979797" stroke-width="2" stroke-linecap="round" fill="transparent"/>' +
        '  <polyline id="successAnimationCheck" stroke="#979797" stroke-width="2" points="23 34 34 43 47 27" fill="transparent"/>' +
        '</svg>';

    var error_svg = '<svg  viewBox="0 0 87 87" version="1.1"  width="35" height="35" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">' +
        '<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">' +
        '<g id="Group-2" transform="translate(2.000000, 2.000000)">' +
        '<circle id="Oval-2" stroke="rgba(252, 68, 68, 0.5)" stroke-width="4" cx="41.5" cy="41.5" r="41.5"></circle>' +
        '<circle  class="ui-error-circle" stroke="#F74444" stroke-width="4" cx="41.5" cy="41.5" r="41.5"></circle>' +
        '<path class="ui-error-line1" d="M22.244224,22 L60.4279902,60.1837662" id="Line" stroke="#F74444" stroke-width="3" stroke-linecap="square"></path>' +
        '<path class="ui-error-line2" d="M60.755776,21 L23.244224,59.8443492" id="Line" stroke="#F74444" stroke-width="3" stroke-linecap="square"></path>' +
        '</g>' +
        '</g>' +
        '</svg>';

    var pin_modal = null;
    var pin_modal_pin_value = null;
    var pin_modal_pin_value_display = null;

    function askForPin(callback, nuki_id, callingButtonElement) {
        // get modal

        if (localStorage.getItem('nuki_' + nuki_id + '_fingerprint')) {
            vibrateIfPossible();

            makeElementSpinning(callingButtonElement);

            // we should try to use fingerprint
            checkregistration(nuki_id, callback, function(){

                setTimeout(function(){
                    restoreSpinningElement(callingButtonElement, false)
                }, 2000);

                $(callingButtonElement).html(error_svg).removeClass('active').addClass('error');

            });
        } else {

            // reset PIN code
            pin_modal_pin_value_display.text('');
            pin_modal_pin_value.val('');

            var modal = UIkit.modal(pin_modal);

            // add event on submit click
            pin_modal.find('button#pin_submit').unbind('click').bind('click', function () {

                var pin = pin_modal.find('input#pin_value').val();

                if (pin_modal.find('input#pin_use_fingerprint').is(':checked')) {

                    // check PIN
                    $.post(window.location.self, { action: 'check_pin', pin: pin, nuki_id: nuki_id }, function (data) {
                        if (data.status === 'ok') {
                            // register new user
                            newregistration(pin, nuki_id, function () {
                                callback(pin);
                                modal.hide();
                            });

                        } else {
                            alert('Incorrect PIN');
                            modal.hide();
                        }

                    }, 'json');

                } else {
                    callback(pin);
                    modal.hide();
                }
            });

            modal.show();
        }
    }

    var lastTimeout = null;
    var stopTimer = false;

    function makeElementSpinning(element) {
        if(!$(element).data('originalDataSaved')) {
            $(element).data('original', $(element).html());
            $(element).data('originalDisabled', $(element).prop('disabled'));
            $(element).data('originalId', $(element).attr('id'));
            $(element).html('<div uk-spinner></div>').addClass('spinner').prop('disabled', true);
            $(element).data('originalDataSaved', true);
        }
    }

    function restoreSpinningElement(element, reSetUpHooks) {
        if($(element).data('originalDataSaved')) {
            $(element).removeClass('spinner').html($(element).data('original')).prop('disabled', $(element).data('originalDisabled')).attr('id', $(element).data('originalId')).removeClass('success').removeClass('error').data('originalDataSaved', false);
        }

        if(reSetUpHooks) {
            setUpHooks($(element).find('div.single_open'));
            setUpHooksCamera($(element).find('div.camera'));
        }
    }

    function doAction(what, element, isInModal, reSetUpHooks, pin, totp, nonce){

        if($(element).hasClass('nuki')){

            console.log('nuki');
            var nuki_id = what.replace(/^nuki_(un)?lock_/g, '');

            if(!pin){
                // check if we need pin
                var nuki_pin_required = localStorage.getItem('nuki_' + nuki_id + '_pin_required');

                if(nuki_pin_required){
                    askForPin(function(pinCode){
                        doAction(what, element, isInModal, reSetUpHooks, pinCode);
                    }, nuki_id, element);
                    return;
                }else{
                    // go on with no PIN
                }

            }

            if(!totp) {

                var nuki_password = localStorage.getItem('nuki_' + nuki_id + '_password');

                if (!nuki_password) {

                    nuki_password = window.prompt('Please enter password for this NUKI device:');
                    if (!!nuki_password)
                        localStorage.setItem('nuki_' + nuki_id + '_password', nuki_password);
                    else
                        return;
                }

                if (!nuki_password)
                    return;

                sha256(nuki_password).then(function (digest) {
                    var secret = base32.encode(hexToString(digest.substr(0, 20)));

                    var nonce = Date.now();

                    sha256(nonce).then(function (digest2) {
                        var secret = base32.encode(hexToString(digest.substr(0, 20))) + base32.encode(hexToString(digest2.substr(0, 10)));

                        var totp = new jsOTP.totp();
                        var timeCode = totp.getOtp(secret);

                        doAction(what, element, isInModal, reSetUpHooks, pin, timeCode, nonce);
                    });
                });

                return;
            }

        }

        var actionResultSelector = $('div#action_result');

        if(isInModal)
            actionResultSelector = $(element).closest('.uk-modal').find('.notify_modal');

        makeElementSpinning(element);
        what = what.replace(/_options/g,'');


        $.post(window.location.self, { action : what, totp: totp, pin: pin, totp_nonce: nonce }, function(data){
            $(actionResultSelector).find('p').text(data.message);

            var doNotHideActionResult = false;

            if(data.status === 'relogin'){
                window.location.reload();
            }else if(data.status === 'ok'){
                //$(actionResultSelector).removeClass('uk-alert-danger').addClass('uk-alert-success').show(200);
                vibrateIfPossible();
            }else{
                if(data.status === 'pin_required'){
                    var nuki_id = what.replace(/^nuki_(un)?lock_/g, '');
                    localStorage.setItem('nuki_' + nuki_id + '_pin_required', true);
                }

                $(actionResultSelector).removeClass('uk-alert-success').addClass('uk-alert-danger').show(200);
            }

            if(!doNotHideActionResult){

                setTimeout(function(){ $(actionResultSelector).hide(200);}, 3000);

                setTimeout(function(){
                    restoreSpinningElement(element, reSetUpHooks);
                }, 2000);


                if(data.status === 'ok') {
                    $(element).html(success_svg).removeClass('active').addClass('success');
                }else{
                    $(element).html(error_svg).removeClass('active').addClass('error');
                }
            }
        }, 'json');

    }

    var open_garage_gate_modal;


    var timeOut = null;

    var touchMoved = false;

    var clicksWithoutAction = 0;

    var pictureInterval = null;

    var picture_element;
    var picture_element_loaded = false;

    function startPictureInterval(){
        clearInterval(pictureInterval);

        pictureInterval = window.setInterval(function () {

            console.log('interval');

            if(isCurrentWindowHidden || !picture_element.is(':visible')) {
                console.log('clearinterval');

                clearInterval(pictureInterval);
                picture_element.parent().find('.paused_container').show();
            }else {
                if(picture_element_loaded) {
                    picture_element_loaded = false;
                    picture_element.attr('src', picture_element.attr('src') + '1');
                }
            }
        }, 500);
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

            // set also originalId data because a JS timeout can be set to update it later to non-up-to-date value
            $(open_garage_gate_modal).find('button.open_garage_gate_dummy_button').attr('id', new_button_id).data('originalId', new_button_id);

            var button_open_1min_element = $(open_garage_gate_modal).find('button.open_garage_gate_1min_dummy_button');

            $(open_garage_gate_modal).find('h2#open_garage_gate_modal_title').text('Open ' + $(this).parent().find('div.single_open').text());

            picture_element.attr('src', 'loading.jpg').attr('src', 'camera.php?camera='+camera1_id+(!!camera2_id ? '&camera2='+camera2_id : '')+'&now'+Date.now());
            picture_element.parent().find('.paused_container').hide();

            startPictureInterval();

            if(!!allow_1min_open){
                button_open_1min_element.show().attr('id', new_button_id+'_1min').data('originalId', new_button_id+'_1min');
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

            var timeOutTime = 0;

            if('ontouchstart' in window){
                timeOutTime = 250;
            }

            timeOut = setTimeout(function(){
                if(touchMoved !== true || isInModal) {
                    clicksWithoutAction = 0;
                    console.log('held: ' + this_id);
                    doAction(this_id, this_object, isInModal, reSetUpHooks);
                }
            }, timeOutTime);

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

    var PINMaskTimeout = null;

    $(document).ready(function(){

        pin_modal = $('div#modal-pin');
        pin_modal_pin_value_display = pin_modal.find('h1#pin_value_display');
        pin_modal_pin_value = pin_modal.find('input#pin_value');

        open_garage_gate_modal = $('div#open_garage_gate_modal');
        picture_element = $(open_garage_gate_modal).find('img#open_garage_gate_modal_camera_picture');
        picture_element.on('load', function () {
            picture_element_loaded = true;
            console.log('loaded');
        })

        pin_modal.find('button.pin_digit').click(function () {
            clearInterval(PINMaskTimeout);
            PINMaskTimeout = null;

            // add to PIN value and display
            var digit = $(this).text();
            var current_text = pin_modal_pin_value_display.text();

            pin_modal_pin_value_display.text("*".repeat(current_text.length) + digit);
            pin_modal_pin_value.val(pin_modal_pin_value.val() + digit);

            PINMaskTimeout = setTimeout(function(){
                pin_modal_pin_value_display.text("*".repeat(pin_modal_pin_value_display.text().length));
            }, 1000);

        });

        pin_modal.find('button#pin_delete').click(function () {
            // add to PIN value and display
            pin_modal_pin_value_display.text(pin_modal_pin_value_display.text().slice(0,-1));
            pin_modal_pin_value.val(pin_modal_pin_value.val().slice(0,-1));
        });

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


        {if $nuki}
            <div class="uk-width-1-1"><h4 class="uk-h4">NUKI</h4></div>

            {section name=n loop=$nuki}
                <div class="uk-width-1-2@l"><button name="action" id="nuki_unlock_{$nuki[n].id}" type="button" class="uk-button uk-button-primary uk-button-large uk-button-default uk-width-1-1 clickable nuki" value="nuki_unlock_{$nuki[n].id}">Unlock {$nuki[n].name}</button></div>
                {if $nuki[n].can_lock}
                    <div class="uk-width-1-2@l"><button name="action" id="nuki_lock_{$nuki[n].id}" type="button" class="uk-button uk-button-danger uk-button-large uk-button-default uk-width-1-1 clickable nuki" value="nuki_lock_{$nuki[n].id}">Lock {$nuki[n].name}</button></div>
                {/if}
            {/section}

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


<div id="modal-pin" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h1 class="uk-text-center" id="pin_value_display" style="min-height: 3.5rem;"></h1>
        <input type="hidden" id="pin_value" />
        <div class="uk-grid-small uk-text-center uk-grid-row-small" uk-grid>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">1</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">2</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">3</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">4</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">5</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">6</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">7</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">8</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">9</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-danger uk-width-1-1" id="pin_delete">DEL</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-default uk-width-1-1 pin_digit">0</button></div>
            <div class="uk-width-1-3"><button type="button" class="uk-button uk-button-primary uk-width-1-1" id="pin_submit">OK</button></div>
        </div>
        <div class="uk-margin">
            <div class="uk-form-controls no-select">
                <input class="uk-checkbox no-select" id="pin_use_fingerprint" name="pin_use_fingerprint" type="checkbox" value="1">
                <label class="uk-form-label no-select" for="pin_use_fingerprint">Use fingerprint from now on</label>
            </div>
        </div>
    </div>
</div>



{include file="footer.tpl"}