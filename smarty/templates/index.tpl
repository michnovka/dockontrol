{include file="header.tpl"}
<script src="/resources/javascript/base32.min.js" type="text/javascript"></script>
<script src="/resources/javascript/jsTOTP.min.js" type="text/javascript"></script>
<script src="/resources/javascript/index.js" type="text/javascript"></script>

<script type="text/javascript">
    var _GEOLOCATION_ENABLED = {if $user.geolocation_enabled}true{else}false{/if};
</script>


<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>DOCKontrol</span></h2>

    <div class="uk-alert-success uk-text-center uk-margin-medium" style="display:none;" id="action_result" uk-alert>
        <p></p>
    </div>

    <div class="uk-grid-small uk-text-center uk-grid-row-small" uk-grid>

        {if $permissions.gate}
            <div class="uk-width-1-2@l"><button name="action" id="enter" type="button" class="uk-button uk-button-large uk-button-primary uk-width-1-1 clickable{if $user.geolocation_enabled} geolocation-icon{/if}" value="enter">CAR ENTER</button></div>
            <div class="uk-width-1-2@l"><button name="action" id="exit" type="button" class="uk-button uk-button-large uk-button-danger uk-width-1-1 clickable" value="exit">CAR EXIT</button></div>
        {/if}

        <div class="uk-width-1-1"><h4 class="uk-h4">Gates</h4></div>

        {section name=g loop=$gates}
            {if $permissions[$gates[g].permission]}
                <div class="uk-width-1-2@l"><button id="open_{$gates[g].id}_options" type="button" class="garage_gate_modal uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">{$gates[g].name}</div>{if $gates[g].camera1}<div class="camera"><img src="/resources/images/security-camera.svg" width="40" data-camera1="{$gates[g].camera1}" data-camera2="{$gates[g].camera2}"{if $gates[g].allow_1min_open} data-allow1min="1"{/if} /></div>{/if}</button></div>
            {/if}
        {/section}

        <div class="uk-width-1-1"><h4 class="uk-h4">Entrances</h4></div>

        {section name=e loop=$entrances}
            {if $permissions[$entrances[e].permission]}
                <div class="uk-width-1-3@l"><button id="open_{$entrances[e].id}_options" type="button" class="garage_gate_modal entrance uk-button uk-button-large uk-button-default uk-width-1-1"><div class="single_open">{$entrances[e].name}</div>{if $entrances[e].camera1}<div class="camera"><img src="/resources/images/security-camera.svg" width="40" data-camera1="{$entrances[e].camera1}" data-camera2="{$entrances[e].camera2}"{if $entrances[e].allow_1min_open} data-allow1min="1"{/if} /></div>{/if}</button></div>
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
                <div class="uk-width-1-2@l"><button name="action" id="nuki_unlock_{$nuki[n].id}" type="button" class="uk-button uk-button-default uk-button-large uk-button-default uk-width-1-1 clickable nuki nuki-unlock" value="nuki_unlock_{$nuki[n].id}">Unlock {$nuki[n].name}</button></div>
                {if $nuki[n].can_lock}
                    <div class="uk-width-1-2@l"><button name="action" id="nuki_lock_{$nuki[n].id}" type="button" class="uk-button uk-button-default uk-button-large uk-button-default uk-width-1-1 clickable nuki nuki-lock" value="nuki_lock_{$nuki[n].id}">Lock {$nuki[n].name}</button></div>
                {/if}
            {/section}

        {/if}

    </div>

    {if $guest}
        <div>
            <ul class="uk-flex-center" uk-tab>
                <li class="uk-active"><a href="#legend-cs">Česky</a></li>
                <li><a href="#legend-en">English</a></li>
            </ul>


            <ul class="uk-switcher">
                <li>
                    <h4 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Legenda</span></h4>
                    <div class="uk-margin uk-text-left">
                        <p>Byl Vám udělen časově omezený přístup hosta k rezidenčnímu areálu DOCK v Praze. Všechny úkony prostřednictvím této stránky jsou logovány a areál je pod kamerovým monitoringem.</p>
                        <p><strong>Car ENTER / EXIT</strong> - Podržte tlačítko pro otevření vjezdové brány a garáže pro vjezd / výjezd na jeden stisk.</p>
                        <p><strong>Gates and entrances</strong> - Podržte tlačítko pro otevírání vchodů do baráku a pěších branek</p>
                    </div>
                </li>
                <li>
                    <h4 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Legend</span></h4>
                    <div class="uk-margin uk-text-left">
                        <p>You were granted a time-limited guest pass to the DOCK residential zone in Prague. All actions are logged and all entrances are under camera surveillance.</p>
                        <p><strong>Car ENTER / EXIT</strong> - Hold the button down for a while to Open both the big gate and garage door in correct timing, allowing vehicle entry / exit with one click.</p>
                        <p><strong>Gates and entrances</strong> - Hold the button down for a while to open pedestrian entrances</p>
                    </div>
                </li>
            </ul>
        </div>
    {/if}

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
                        <img src="/resources/images/loading.jpg" class="open_garage_gate_modal_camera_picture" id="open_garage_gate_modal_camera_picture" />
                        <div class="paused_container"><img src="/resources/images/pause.svg" width="80" /></div>
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