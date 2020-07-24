{include file="header.tpl" title="NUKI device"}

<div class="uk-container uk-container-medium uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>NUKI device</span></h2>

    {if $error_message}
        <div class="uk-alert-danger uk-text-center uk-margin-medium" uk-alert>
            <p>{$error_message}</p>
        </div>
    {/if}

    {if $success}
        <div class="uk-alert-success uk-text-center uk-margin-medium" uk-alert>
            <p>{$success}</p>
        </div>
    {/if}


    <form class="uk-form-stacked uk-margin-large uk-text-left" method="post">

        <input type="hidden" name="id" value="{$nuki.id}">

        <div class="uk-margin">
            <label class="uk-form-label" for="username">Name</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.name}uk-form-danger{/if}" id="name" type="text" placeholder="NUKI name" value="{$edit_nuki.name}" name="name">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="username">Username</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.username}uk-form-danger{/if}" id="username" type="text" placeholder="Username" value="{$edit_nuki.username}" name="username">
            </div>
        </div>


        <div class="uk-margin">
            <label class="uk-form-label" for="password1">Password1</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.password1}uk-form-danger{/if}" id="password1" type="password" placeholder="Enter to change" value="" name="password1">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="dockontrol_nuki_api_server">Dockontrol NUKI API server URL</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.dockontrol_nuki_api_server}uk-form-danger{/if}" id="dockontrol_nuki_api_server" type="text" placeholder="https://api.dockontrol-nuki.com:19443" value="{$edit_nuki.dockontrol_nuki_api_server}" name="dockontrol_nuki_api_server">
            </div>
        </div>


        <div class="uk-margin">
            <label class="uk-form-label" for="can_lock">Can lock?</label>
            <div class="uk-form-controls">
                <input class="uk-checkbox" id="can_lock" name="can_lock" type="checkbox" value="1"{if $edit_nuki.can_lock} checked{/if}>
            </div>
        </div>

        <div class="uk-margin">
            <div class="uk-form-controls">
                <button type="submit" name="action" value="save" class="uk-button uk-button-primary uk-button-large uk-width-full">Save</button>
            </div>
        </div>
        {if $nuki.id}
            <div class="uk-margin-small">
                <div class="uk-form-controls">
                    <button type="submit" name="action" value="delete" class="uk-button uk-button-danger uk-button-large uk-width-full">DELETE NUKI</button>
                </div>
            </div>

        {/if}
    </form>


</div>

{if $nuki.id}
    <script type="text/javascript">
        function setPassword2(){

            var nuki_password = window.prompt('Please enter password for this NUKI device:');

            if (!!nuki_password) {
                localStorage.setItem('nuki_{$nuki.id}_password', nuki_password);
                $('div#configure_pin_button').show();
            }
        }

        $(document).ready(function () {

            var nuki_password = localStorage.getItem('nuki_{$nuki.id}_password');

            if (!!nuki_password)
                $('div#configure_pin_button').show();
            else
                $('div#configure_pin_button').hide();

            $('button#change_pin_save').click(function () {

                var this_button = $(this);
                var error = false;
                // check password 2
                var nuki_password = localStorage.getItem('nuki_{$nuki.id}_password');

                var pin_element = $('input#change_pin_pin');
                var password1_element = $('input#change_pin_password1');
                var password2_element = $('input#change_pin_password2');

                password1_element.removeClass('uk-form-danger');
                password2_element.removeClass('uk-form-danger');
                pin_element.removeClass('uk-form-danger');

                var password2 = password2_element.val()

                if(password2 !== nuki_password){
                    password2_element.addClass('uk-form-danger');
                    error = true;
                }

                // check pin legth
                if(pin_element.val().length < 4){
                    pin_element.addClass('uk-form-danger');
                    error = true;
                }

                if(!error){

                    $(this_button).data('original', $(this_button).html());
                    $(this_button).html('<div uk-spinner></div>').addClass('spinner').prop('disabled', true);

                    // submit AJAX
                    $.post(window.location.self, { action : 'change_pin', password1: password1_element.val(), pin: pin_element.val() }, function(data){
                        if(data.status === 'ok'){
                            // close modal

                            setTimeout(function(){
                                $(this_button).prop('disabled',false).removeClass('spinner').html($(this_button).data('original')).removeClass('success').removeClass('error');

                                password1_element.val('');
                                password2_element.val('');
                                pin_element.val('');
                                var modal = UIkit.modal($('#configure_pin_modal'));
                                modal.hide();
                            }, 2000);

                            var success_svg = '<svg id="successAnimation" class="animated" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 70 70">' +
                                '  <path id="successAnimationResult" fill="#D8D8D8" d="M35,60 C21.1928813,60 10,48.8071187 10,35 C10,21.1928813 21.1928813,10 35,10 C48.8071187,10 60,21.1928813 60,35 C60,48.8071187 48.8071187,60 35,60 Z M23.6332378,33.2260427 L22.3667622,34.7739573 L34.1433655,44.40936 L47.776114,27.6305926 L46.223886,26.3694074 L33.8566345,41.59064 L23.6332378,33.2260427 Z"/>' +
                                '  <circle id="successAnimationCircle" cx="35" cy="35" r="24" stroke="#979797" stroke-width="2" stroke-linecap="round" fill="transparent"/>' +
                                '  <polyline id="successAnimationCheck" stroke="#979797" stroke-width="2" points="23 34 34 43 47 27" fill="transparent"/>' +
                                '</svg>';


                            $(this_button).html(success_svg).removeClass('active').addClass('success');

                        }else{
                            if(data.error_password1) {
                                password1_element.addClass('uk-form-danger');
                            }

                            if(data.error_pin){
                                pin_element.addClass('uk-form-danger');
                            }

                            $(this_button).prop('disabled',false).removeClass('spinner').html($(this_button).data('original')).removeClass('success').removeClass('error');
                        }

                    },'json');


                }
            })

        });
    </script>
<div class="uk-container uk-container-medium uk-container-center uk-text-center">

    <h4 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Security</span></h4>

    <div class="uk-margin-small">
        <div class="uk-form-controls">
            <button type="button" name="action" value="set_password2" class="uk-button uk-button-default uk-button-large uk-width-full" onclick="setPassword2();">SET PASSWORD2</button>
        </div>
    </div>

    <div class="uk-margin-small" id="configure_pin_button">
        <div class="uk-form-controls">
            <a class="uk-button uk-button-default uk-button-large uk-width-full" href="#configure_pin_modal" uk-toggle>CONFIGURE PIN</a>
        </div>
    </div>

</div>


    <div id="configure_pin_modal" class="uk-flex-top" uk-modal>
        <div class="uk-modal-dialog uk-margin-auto-vertical uk-padding-medium">
            <button class="uk-modal-close-default" type="button" uk-close></button>

            <div class="uk-modal-header uk-visible@s">
                <h2 class="uk-modal-title" id="open_garage_gate_modal_title">Configure PIN</h2>
            </div>
            <div class="uk-modal-body uk-padding-small">

                <div class="uk-margin uk-width-1-1"><input class="uk-input" id="change_pin_password1" type="password" placeholder="Password1" value="" name="password1"></div>
                <div class="uk-margin uk-width-1-1"><input class="uk-input" id="change_pin_password2" type="password" placeholder="Password2" value="" name="password2"></div>
                <div class="uk-margin uk-width-1-1"><input class="uk-input" id="change_pin_pin" type="tel" placeholder="PIN" value="" name="pin"></div>
                <div class="uk-margin uk-width-1-1"><button name="action" id="change_pin_save" type="button" class="open_garage_gate_dummy_button uk-button uk-button-large uk-button-primary uk-width-1-1 clickable clickable_modal" value="">SAVE</button></div>

            </div>
        </div>
    </div>

{/if}

{include file="footer.tpl"}
